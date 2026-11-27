<?php

namespace App\Services;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Mail\DonationReceipt;
use App\Mail\TransferReceiptReceived;
use App\Mail\TransferReceiptToCheck;
use App\Settings\DonationSettings;
use App\Settings\SiteSettings;
use App\Models\Cause;
use App\Models\Donation;
use App\Models\GivingType;
use App\Models\User;
use App\Payments\GatewayResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class DonationService
{
    /**
     * Record a new donation attempt (plan 1.2: record every attempt).
     *
     * @param  array{giving_type_id:int, cause_id:?int, amount:int, donor_name:string, donor_phone:string, donor_email:?string, is_anonymous:bool, payment_method:PaymentMethod, ip_address:?string}  $data
     */
    public function start(array $data, ?UploadedFile $transferReceipt = null): Donation
    {
        $method = $data['payment_method'];

        if (! $method->isAvailable()) {
            throw new InvalidArgumentException("{$method->getLabel()} is not available.");
        }

        // Bank, JazzCash and Easypaisa in manual mode: Finance checks the receipt.
        if ($method->needsReceipt() && ! $transferReceipt) {
            throw new InvalidArgumentException("{$method->getLabel()} needs a payment receipt.");
        }

        $type = GivingType::active()->findOrFail($data['giving_type_id']);
        $cause = $data['cause_id'] ? Cause::active()->findOrFail($data['cause_id']) : null;

        return DB::transaction(function () use ($data, $type, $cause, $method, $transferReceipt) {
            $donation = Donation::create([
                ...$data,
                'cause_id' => $cause?->id,
                'status' => $method->needsReceipt()
                    ? DonationStatus::AwaitingConfirmation
                    : DonationStatus::Pending,
                // Zakat/Khums for a cause Finance hasn't marked eligible: contact the donor first.
                'needs_eligibility_review' => ! $type->acceptsCause($cause),
            ]);

            if ($transferReceipt) {
                $donation->addMedia($transferReceipt)
                    ->usingFileName($donation->reference.'.'.$transferReceipt->guessExtension())
                    ->toMediaCollection('transfer_receipt');

                if ($donation->donor_email) {
                    Mail::to($donation->donor_email)->queue(new TransferReceiptReceived($donation));
                }

                // Tell Finance straight away, so the donor's receipt isn't left waiting.
                Mail::to($this->financeRecipients())->queue(new TransferReceiptToCheck($donation));
            }

            return $donation;
        });
    }

    /**
     * Apply a verified gateway callback. Idempotent: providers may call more than once.
     */
    public function applyGatewayResult(GatewayResult $result): Donation
    {
        $donation = Donation::where('reference', $result->reference)->firstOrFail();

        if ($donation->status === DonationStatus::Paid) {
            return $donation;
        }

        if ($result->successful && $result->amount !== $donation->amount) {
            // Never accept a payment for a different amount than the donor chose.
            $donation->forceFill([
                'status' => DonationStatus::Failed,
                'gateway_payload' => $result->payload,
                'finance_notes' => trim($donation->finance_notes."\nGateway reported Rs {$result->amount}, expected {$donation->formattedAmount()}."),
            ])->save();

            return $donation;
        }

        if (! $result->successful) {
            $donation->forceFill(['status' => DonationStatus::Failed, 'gateway_payload' => $result->payload])->save();

            return $donation;
        }

        $donation->markPaid(transactionId: $result->transactionId, payload: $result->payload);
        $this->sendReceipt($donation);

        return $donation;
    }

    /**
     * Who hears about new receipts: the address set in Admin → Settings →
     * Donations, else every Finance user, else the site email.
     *
     * @return list<string>
     */
    public function financeRecipients(): array
    {
        $configured = app(DonationSettings::class)->finance_email;
        if (filled($configured)) {
            return [$configured];
        }

        $finance = User::whereHas('roles', fn ($query) => $query->where('name', Role::Finance->value))
            ->pluck('email')->filter()->unique()->values()->all();

        return $finance ?: [app(SiteSettings::class)->email];
    }

    /** Finance confirms a bank, JazzCash or Easypaisa payment after finding it in the account statement. */
    public function confirmTransfer(Donation $donation, User $by): void
    {
        if ($donation->status !== DonationStatus::AwaitingConfirmation) {
            throw new InvalidArgumentException('Only transfers waiting for confirmation can be confirmed.');
        }

        $donation->markPaid(confirmedBy: $by);
        $this->sendReceipt($donation);
    }

    public function rejectTransfer(Donation $donation, User $by, string $reason): void
    {
        $donation->forceFill([
            'status' => DonationStatus::Failed,
            'confirmed_by' => $by->id,
            'finance_notes' => trim($donation->finance_notes."\nRejected: ".$reason),
        ])->save();
    }

    public function sendReceipt(Donation $donation): bool
    {
        if ($donation->status !== DonationStatus::Paid || ! $donation->donor_email) {
            return false;
        }

        Mail::to($donation->donor_email)->queue(new DonationReceipt($donation));
        $donation->forceFill(['receipt_sent_at' => now()])->save();

        return true;
    }
}
