<?php

namespace App\Filament\Resources\Donations;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Messaging\DonorMessages;
use App\Models\Donation;
use App\Services\DonationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Finance actions on a donation, shared by the table and the donation page.
 */
class DonationActions
{
    /** @return list<Action> */
    public static function all(): array
    {
        return [
            static::viewReceipt(),
            static::confirmTransfer(),
            static::rejectTransfer(),
            static::sendWhatsApp(),
            static::resendReceipt(),
            static::markRefunded(),
            static::notes(),
        ];
    }

    public static function viewReceipt(): Action
    {
        return Action::make('viewTransferReceipt')
            ->label('View payment receipt')
            ->icon(Heroicon::OutlinedPaperClip)
            ->color('gray')
            ->url(fn (Donation $record) => route('donations.transfer-receipt', $record), shouldOpenInNewTab: true)
            ->visible(fn (Donation $record) => $record->hasMedia('transfer_receipt'));
    }

    public static function confirmTransfer(): Action
    {
        return Action::make('confirmTransfer')
            ->label('Confirm payment')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(fn (Donation $record) => "Confirm {$record->formattedAmount()} from {$record->donor_name}?")
            ->modalDescription(fn (Donation $record) => match ($record->payment_method) {
                PaymentMethod::JazzCash => 'Only confirm after you have found this payment in the JazzCash account history.',
                PaymentMethod::Easypaisa => 'Only confirm after you have found this payment in the Easypaisa account history.',
                default => 'Only confirm after you have found this transfer in the bank statement.',
            }.' The donor will be emailed their receipt.')
            ->modalSubmitActionLabel('Confirm payment')
            ->visible(fn (Donation $record) => $record->status === DonationStatus::AwaitingConfirmation)
            ->authorize(fn (Donation $record) => auth()->user()->can('update', $record))
            ->action(function (Donation $record) {
                app(DonationService::class)->confirmTransfer($record, auth()->user());
                $whatsapp = DonorMessages::whatsappLink($record);

                Notification::make()->success()->title('Payment confirmed')
                    ->body($record->donor_email
                        ? "Receipt emailed to {$record->donor_email}.".($whatsapp ? ' You can also send it on WhatsApp.' : '')
                        : ($whatsapp ? 'No email address: send the receipt on WhatsApp.' : 'No email address or usable phone number, so call the donor.'))
                    ->actions($whatsapp ? [
                        Action::make('whatsapp')->label('Send receipt on WhatsApp')->button()->color('success')
                            ->url($whatsapp, shouldOpenInNewTab: true),
                    ] : [])
                    ->persistent()
                    ->send();
            });
    }

    /**
     * Opens WhatsApp on the donor's chat with the confirmation and a secure link
     * to their PDF receipt, ready to send. (Plain WhatsApp links cannot attach
     * files; the WhatsApp Business API, planned later, will send it directly.)
     */
    public static function sendWhatsApp(): Action
    {
        return Action::make('sendWhatsApp')
            ->label('Send receipt on WhatsApp')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->color('success')
            ->url(fn (Donation $record) => DonorMessages::whatsappLink($record), shouldOpenInNewTab: true)
            ->visible(fn (Donation $record) => $record->status === DonationStatus::Paid && DonorMessages::whatsappLink($record) !== null)
            ->authorize(fn (Donation $record) => auth()->user()->can('update', $record));
    }

    public static function rejectTransfer(): Action
    {
        return Action::make('rejectTransfer')
            ->label('Reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->modalHeading('Reject this payment?')
            ->modalDescription('Use this when the payment cannot be found in the bank or wallet statement, or the receipt is not valid. Contact the donor before rejecting.')
            ->schema([
                Textarea::make('reason')->label('Reason')->required()->rows(3),
            ])
            ->modalSubmitActionLabel('Reject payment')
            ->visible(fn (Donation $record) => $record->status === DonationStatus::AwaitingConfirmation)
            ->authorize(fn (Donation $record) => auth()->user()->can('update', $record))
            ->action(function (Donation $record, array $data) {
                app(DonationService::class)->rejectTransfer($record, auth()->user(), $data['reason']);
                Notification::make()->title('Payment rejected')->send();
            });
    }

    public static function resendReceipt(): Action
    {
        return Action::make('resendReceipt')
            ->label('Resend receipt by email')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription(fn (Donation $record) => "Email the receipt again to {$record->donor_email}?")
            ->visible(fn (Donation $record) => $record->status === DonationStatus::Paid && filled($record->donor_email))
            ->authorize(fn (Donation $record) => auth()->user()->can('update', $record))
            ->action(function (Donation $record) {
                app(DonationService::class)->sendReceipt($record);
                Notification::make()->success()->title('Receipt sent')->send();
            });
    }

    public static function markRefunded(): Action
    {
        return Action::make('markRefunded')
            ->label('Mark refunded')
            ->icon(Heroicon::OutlinedArrowUturnLeft)
            ->color('gray')
            ->modalHeading('Mark this donation as refunded?')
            ->modalDescription('Record a refund after the money has been returned to the donor (Refund Policy). This does not move any money.')
            ->schema([
                Textarea::make('reason')->label('Refund details')->required()->rows(3)
                    ->placeholder('Refunded to JazzCash 0300… on 12 Dec, donor donated twice by mistake'),
            ])
            ->visible(fn (Donation $record) => $record->status === DonationStatus::Paid)
            ->authorize(fn (Donation $record) => auth()->user()->can('update', $record))
            ->action(function (Donation $record, array $data) {
                $record->forceFill([
                    'status' => DonationStatus::Refunded,
                    'finance_notes' => trim($record->finance_notes."\nRefunded by ".auth()->user()->name.': '.$data['reason']),
                ])->save();
                Notification::make()->title('Marked as refunded')->send();
            });
    }

    public static function notes(): Action
    {
        return Action::make('notes')
            ->label('Notes')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->color('gray')
            ->fillForm(fn (Donation $record) => ['finance_notes' => $record->finance_notes])
            ->schema([
                Textarea::make('finance_notes')->label('Finance notes')->rows(5)
                    ->helperText('Only staff see these, e.g. “donor called, agreed to use Zakat for Ramadan Package”.'),
            ])
            ->authorize(fn (Donation $record) => auth()->user()->can('update', $record))
            ->action(fn (Donation $record, array $data) => $record->forceFill(['finance_notes' => $data['finance_notes']])->save());
    }
}
