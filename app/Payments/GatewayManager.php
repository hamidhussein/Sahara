<?php

namespace App\Payments;

use App\Enums\PaymentMethod;
use App\Payments\Gateways\FakeGateway;
use LogicException;

/**
 * Resolves the gateway driver configured for a payment method.
 * Real drivers (jazzcash, easypaisa, card) are added here once each merchant
 * account is approved and its sandbox credentials are available.
 */
class GatewayManager
{
    public function for(PaymentMethod $method): PaymentGateway
    {
        if (! $method->isOnline()) {
            throw new LogicException('Bank transfers are confirmed by Finance, not by a gateway.');
        }

        $driver = config("payments.methods.{$method->value}.driver");

        return match ($driver) {
            'fake' => app(FakeGateway::class),
            default => throw new LogicException(
                "No gateway driver for {$method->getLabel()} yet. Write the [{$driver}] driver against the provider's sandbox before enabling it."
            ),
        };
    }
}
