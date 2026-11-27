<?php

/*
 * Payment methods for the Donate page (plan 1.2, Donation module).
 *
 * Each method works in one of two modes:
 *  - manual:  the donor sends money to SSWF's account (bank, JazzCash or
 *             Easypaisa, with the provider's QR code), uploads the receipt,
 *             and Finance confirms it. Shown once the account details are
 *             entered in Admin → Settings → Bank & wallets.
 *  - gateway: the donor pays through the provider's checkout and the
 *             donation is confirmed by the provider's signed callback. Use
 *             once the merchant account is approved and the driver is written
 *             against the provider's sandbox. Keys live only in .env (plan 1.4).
 */

return [

    'methods' => [
        'bank_transfer' => [
            'enabled' => env('PAYMENTS_BANK_TRANSFER_ENABLED', true),
            'mode' => 'manual',
        ],
        'jazzcash' => [
            'enabled' => env('PAYMENTS_JAZZCASH_ENABLED', true),
            'mode' => env('PAYMENTS_JAZZCASH_MODE', 'manual'),
            'driver' => env('PAYMENTS_JAZZCASH_DRIVER', 'jazzcash'),
        ],
        'easypaisa' => [
            'enabled' => env('PAYMENTS_EASYPAISA_ENABLED', true),
            'mode' => env('PAYMENTS_EASYPAISA_MODE', 'manual'),
            'driver' => env('PAYMENTS_EASYPAISA_DRIVER', 'easypaisa'),
        ],
        'card' => [
            'enabled' => env('PAYMENTS_CARD_ENABLED', false),
            'mode' => 'gateway', // cards are always paid through a gateway
            'driver' => env('PAYMENTS_CARD_DRIVER', 'card'),
        ],
    ],

    /*
     * The "fake" driver simulates a provider so the whole online flow (redirect,
     * signed callback, thank-you and failed pages) can be tried locally and in
     * tests. It refuses to run in production.
     */
    'fake' => [
        'secret' => env('PAYMENTS_FAKE_SECRET', env('APP_KEY')),
    ],

    'amounts' => [
        'presets' => [1000, 5000, 10000, 50000],
        'minimum' => 100,
        'maximum' => 10_000_000,
    ],

];
