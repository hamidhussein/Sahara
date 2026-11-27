<?php

namespace App\Payments\Gateways;

use App\Models\Donation;
use App\Payments\GatewayResult;
use App\Payments\InvalidCallbackException;
use App\Payments\PaymentGateway;
use Illuminate\Http\Request;
use LogicException;

/**
 * Simulates a provider's hosted checkout so the online flow can be tried
 * locally and tested: the donor approves or declines on a fake page, which
 * posts an HMAC-signed callback just like a real provider would.
 */
class FakeGateway implements PaymentGateway
{
    public function __construct()
    {
        if (app()->isProduction()) {
            throw new LogicException('The fake payment gateway cannot run in production.');
        }
    }

    public function checkoutUrl(Donation $donation): string
    {
        return route('payments.fake.checkout', $donation->reference);
    }

    public function verifyCallback(Request $request): GatewayResult
    {
        $data = $request->only(['reference', 'amount', 'outcome', 'transaction_id']);

        if (! hash_equals(static::sign($data), (string) $request->input('signature'))) {
            throw new InvalidCallbackException('Signature does not match.');
        }

        return new GatewayResult(
            reference: (string) $data['reference'],
            successful: ($data['outcome'] ?? null) === 'approved',
            amount: (int) $data['amount'],
            transactionId: $data['transaction_id'] ?? null,
            payload: $data,
        );
    }

    /** @param array<string, mixed> $data */
    public static function sign(array $data): string
    {
        ksort($data);

        return hash_hmac('sha256', http_build_query($data), (string) config('payments.fake.secret'));
    }
}
