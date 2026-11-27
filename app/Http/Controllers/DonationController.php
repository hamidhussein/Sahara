<?php

namespace App\Http\Controllers;

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Livewire\DonateForm;
use App\Mail\DonationReceipt;
use App\Models\Donation;
use App\Payments\GatewayManager;
use App\Payments\Gateways\FakeGateway;
use App\Payments\InvalidCallbackException;
use App\Services\DonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DonationController extends Controller
{
    public function create(): View
    {
        return view('donate.create');
    }

    /** Signed link, so only the donor who just gave sees their name and amount. */
    public function thankYou(Donation $donation): View
    {
        $donation->load(['givingType', 'cause']);

        return view('donate.thank-you', ['donation' => $donation]);
    }

    public function failed(Donation $donation): View
    {
        return view('donate.failed', ['donation' => $donation]);
    }

    /**
     * Provider callback. The only place an online donation becomes paid,
     * and only after its signature checks out (plan 1.2).
     */
    public function callback(Request $request, string $method, GatewayManager $gateways, DonationService $service): RedirectResponse
    {
        $paymentMethod = PaymentMethod::tryFrom($method);
        abort_unless($paymentMethod?->isOnline() && $paymentMethod->isAvailable(), 404);

        try {
            $result = $gateways->for($paymentMethod)->verifyCallback($request);
        } catch (InvalidCallbackException $e) {
            Log::warning('Rejected payment callback', ['method' => $method, 'reason' => $e->getMessage(), 'ip' => $request->ip()]);
            abort(403, 'This payment confirmation could not be verified.');
        }

        $donation = $service->applyGatewayResult($result);

        return $donation->status === DonationStatus::Paid
            ? redirect()->to(DonateForm::thankYouUrl($donation))
            : redirect()->to(URL::signedRoute('donate.failed', $donation->reference, now()->addDays(7)));
    }

    /** The fake provider's checkout page (local and testing only). */
    public function fakeCheckout(Donation $donation): View
    {
        abort_if(app()->isProduction(), 404);
        abort_unless($donation->status === DonationStatus::Pending, 404);

        $fields = function (string $outcome) use ($donation): array {
            $data = [
                'reference' => $donation->reference,
                'amount' => (string) $donation->amount,
                'outcome' => $outcome,
                'transaction_id' => $outcome === 'approved' ? 'FAKE-'.strtoupper(substr(sha1($donation->reference), 0, 10)) : '',
            ];

            return $data + ['signature' => FakeGateway::sign($data)];
        };

        return view('donate.fake-checkout', [
            'donation' => $donation,
            'approve' => $fields('approved'),
            'decline' => $fields('declined'),
        ]);
    }

    /**
     * The donor's official PDF receipt, opened from the signed link sent by
     * WhatsApp (and later SMS). Only confirmed donations have a receipt.
     */
    public function receipt(Donation $donation): Response
    {
        abort_unless($donation->status === DonationStatus::Paid, 404);

        return DonationReceipt::pdf($donation)->stream("SSWF-receipt-{$donation->reference}.pdf");
    }

    /** Private bank transfer receipt, for Finance and Super Admin only. */
    public function transferReceipt(Donation $donation): StreamedResponse
    {
        Gate::authorize('view', $donation);

        $media = $donation->getFirstMedia('transfer_receipt') ?? abort(404);

        return response()->streamDownload(
            fn () => fpassthru($media->stream()),
            $media->file_name,
            ['Content-Type' => $media->mime_type],
            'inline',
        );
    }
}
