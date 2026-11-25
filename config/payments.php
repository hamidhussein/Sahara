<?php

/*
 * Payment methods for the Donate page (plan 1.2, Donation module).
 *
 * Bank transfer / Raast works from day one. Each online method stays hidden
 * until its merchant account is approved, its gateway driver is written
 * against the provider's sandbox, and it is switched on here. Gateway keys
 * live only in the server's .env, never in Git (plan 1.4).
 */

return [

    'methods' => [
        'bank_transfer' => [
            'enabled' => env('PAYMENTS_BANK_TRANSFER_ENABLED', true),
        ],
        'jazzcash' => [
            'enabled' => env('PAYMENTS_JAZZCASH_ENABLED', false),
            'driver' => env('PAYMENTS_JAZZCASH_DRIVER', 'jazzcash'),
        ],
        'easypaisa' => [
            'enabled' => env('PAYMENTS_EASYPAISA_ENABLED', false),
            'driver' => env('PAYMENTS_EASYPAISA_DRIVER', 'easypaisa'),
        ],
        'card' => [
            'enabled' => env('PAYMENTS_CARD_ENABLED', false),
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
