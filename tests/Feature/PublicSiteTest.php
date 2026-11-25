<?php

use App\Settings\SiteSettings;
use Database\Seeders\ContentSeeder;

beforeEach(fn () => $this->seed(ContentSeeder::class));

it('renders every page in the main menu', function (string $uri) {
    $this->get($uri)->assertOk()->assertSee('Sab Ka Sahara');
})->with([
    '/', '/about', '/about/leadership', '/about/transparency', '/programs', '/programs/clean-water',
    '/projects', '/get-help', '/get-involved', '/news', '/gallery', '/contact', '/donate',
    '/privacy', '/refund-policy', '/terms',
]);

it('leaves the Donate button out of the phone bar on the Donate pages', function () {
    $bar = fn (string $html) => str($html)->after('aria-label="Quick actions"')->before('</nav>')->value();

    expect($bar($this->get('/')->getContent()))->toContain(route('donate'))
        ->and($bar($this->get('/donate')->getContent()))->not->toContain('>Donate')
        ->and($bar($this->get('/donate')->getContent()))->toContain('Call us');
});

it('returns 404 for an unknown program', function () {
    $this->get('/programs/not-a-program')->assertNotFound();
});

it('shows the plan’s confirmed contact details and registration number', function () {
    $this->get('/')
        ->assertSee('+92 5811 453014')
        ->assertSee('sswogilgit@gmail.com')
        ->assertSee('J-6 (16)/203/2012')
        ->assertSee('Support for everyone who has none.');
});

it('falls back to the office phone and hides WhatsApp until those numbers are confirmed', function () {
    $this->get('/')
        ->assertSee('Call us: +92 5811 453014')
        ->assertDontSee('WhatsApp:')
        ->assertDontSee('Ambulance &amp; Emergency:', false);
});

it('shows the ambulance and WhatsApp numbers once they are set in Settings', function () {
    $settings = app(SiteSettings::class);
    $settings->ambulance_phone = '+92 300 0000000';
    $settings->whatsapp_number = '+92 311 1111111';
    $settings->save();

    $this->get('/')
        ->assertSee('Ambulance &amp; Emergency: +92 300 0000000', false)
        ->assertSee('tel:+923000000000', false)
        ->assertSee('https://wa.me/923111111111', false);
});
