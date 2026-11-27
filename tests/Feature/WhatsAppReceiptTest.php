<?php

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Filament\Resources\Donations\Pages\ViewDonation;
use App\Messaging\DonorMessages;
use App\Models\Donation;
use App\Models\GivingType;
use App\Models\User;
use App\Support\PhoneNumber;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Mail::fake();
});

function waDonation(array $overrides = []): Donation
{
    return Donation::create($overrides + [
        'giving_type_id' => GivingType::where('slug', 'sadaqah')->value('id'),
        'amount' => 5000,
        'donor_name' => 'Ali Raza',
        'donor_phone' => '0300 1234567',
        'payment_method' => PaymentMethod::JazzCash,
        'status' => DonationStatus::AwaitingConfirmation,
    ]);
}

function waFinance(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::Finance->value);

    return $user;
}

it('turns the numbers donors type into WhatsApp format', function (string $typed, ?string $expected) {
    expect(PhoneNumber::international($typed))->toBe($expected);
})->with([
    ['0300 1234567', '923001234567'],
    ['+92 300-1234567', '923001234567'],
    ['0092 300 1234567', '923001234567'],
    ['923001234567', '923001234567'],
    ['+44 7700 900123', '447700900123'],
    ['12345', null],
    ['', null],
]);

it('writes a confirmation with the receipt number and a secure receipt link', function () {
    $donation = waDonation();
    $donation->markPaid();

    $text = DonorMessages::receiptConfirmation($donation);

    expect($text)
        ->toContain('Dear Ali Raza,')
        ->toContain('Rs 5,000 as Sadaqah for Where it is needed most')
        ->toContain('Receipt number: '.$donation->reference)
        ->toContain('/receipts/'.$donation->reference.'?signature=');

    expect(DonorMessages::whatsappLink($donation))
        ->toStartWith('https://wa.me/923001234567?text=')
        ->toContain(rawurlencode('Dear Ali Raza,'));
});

it('opens the PDF receipt from the signed link only, and only once paid', function () {
    $donation = waDonation();
    $url = DonorMessages::receiptUrl($donation);

    $this->get($url)->assertNotFound(); // not confirmed yet

    $donation->markPaid();
    $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($response->getContent(), 0, 4))->toBe('%PDF');

    $this->get('/receipts/'.$donation->reference)->assertForbidden();
    $this->get($url.'x')->assertForbidden();
});

it('offers WhatsApp right after Finance confirms a payment', function () {
    $donation = waDonation(['donor_email' => null]);

    Livewire::actingAs(waFinance())->test(ViewDonation::class, ['record' => $donation->getKey()])
        ->callAction('confirmTransfer')
        ->assertNotified('Payment confirmed');

    expect($donation->refresh()->status)->toBe(DonationStatus::Paid);
});

it('shows Send receipt on WhatsApp for paid donations with a usable phone number', function () {
    $paid = waDonation();
    $paid->markPaid();

    Livewire::actingAs(waFinance())->test(ViewDonation::class, ['record' => $paid->getKey()])
        ->assertActionVisible('sendWhatsApp')
        ->assertActionHasUrl('sendWhatsApp', DonorMessages::whatsappLink($paid));

    $pending = waDonation(['donor_phone' => '0311 7654321']);
    Livewire::actingAs(waFinance())->test(ViewDonation::class, ['record' => $pending->getKey()])
        ->assertActionHidden('sendWhatsApp');

    $badPhone = waDonation(['donor_phone' => '123']);
    $badPhone->markPaid();
    Livewire::actingAs(waFinance())->test(ViewDonation::class, ['record' => $badPhone->getKey()])
        ->assertActionHidden('sendWhatsApp');
});

it('keeps receipt links out of search engines', function () {
    config(['app.env' => 'production']);
    $this->get('/robots.txt')->assertSee('Disallow: /receipts/');
});
