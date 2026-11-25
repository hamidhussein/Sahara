<?php

namespace App\Payments;

use App\Models\Donation;
use Illuminate\Http\Request;

/**
 * An online payment provider (JazzCash, Easypaisa, a card gateway).
 *
 * A donation is only marked paid from verifyCallback(), i.e. after the
 * provider's signed confirmation, never from the browser alone (plan 1.2).
 */
interface PaymentGateway
{
    /** Where to send the donor to pay. */
    public function checkoutUrl(Donation $donation): string;

    /**
     * Check the provider's callback signature and report the outcome.
     *
     * @throws InvalidCallbackException when the signature or data don't check out
     */
    public function verifyCallback(Request $request): GatewayResult;
}
