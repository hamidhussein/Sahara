<?php

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Filament\Pages\ManageDonationSettings;
use App\Filament\Resources\Donations\Pages\ViewDonation;
use App\Livewire\DonateForm;
use App\Mail\DonationReceipt;
use App\Mail\TransferReceiptReceived;
use App\Models\Donation;
use App\Models\User;
use App\Payments\PaymentDetails;
use App\Payments\SampleDetails;
use App\Settings\DonationSettings;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SamplePaymentDetailsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
    $this->seed(DatabaseSeeder::class);
    Mail::fake();
});

function walletForm(string $method)
{
    return Livewire::withQueryParams(['type' => 'sadaqah', 'amount' => '2500'])->test(DonateForm::class)
        ->set('name', 'Ayesha Khan')->set('phone', '0312 7654321')->set('email', 'ayesha@example.com')
        ->call('next')
        ->set('method', $method);
}

function liveSite(): void
{
    config(['app.env' => 'production']);
}

function realWallets(): DonationSettings
{
    $s = app(DonationSettings::class);
    $s->jazzcash_title = 'Sab Ka Sahara Welfare Foundation';
    $s->jazzcash_number = '0301 2345678';
    $s->jazzcash_qr = 'payment-qr/jazzcash.png';
    $s->easypaisa_title = 'Sab Ka Sahara Welfare Foundation';
    $s->easypaisa_number = '0346 2345678';
    $s->easypaisa_qr = null;
    $s->raast_id = null;
    $s->bank_qr = null;
    $s->save();
    Storage::disk('public')->put('payment-qr/jazzcash.png', 'png');

    return $s;
}

// ── Donating with a wallet ────────────────────────────────────────────────

it('shows the JazzCash number, QR code and steps, and takes the donation with a receipt', function () {
    walletForm('jazzcash')
        ->assertSee('Please send to')
        ->assertSee(SampleDetails::JAZZCASH_NUMBER)
        ->assertSee('QR code to pay with JazzCash', false)
        ->assertSee('Send Rs 2,500 to the number above')
        ->set('receipt', UploadedFile::fake()->image('jazzcash-confirmation.png'))
        ->call('donate')
        ->assertHasNoErrors()
        ->assertRedirectContains('/donate/thank-you/');

    $donation = Donation::sole();
    expect($donation->payment_method)->toBe(PaymentMethod::JazzCash)
        ->and($donation->status)->toBe(DonationStatus::AwaitingConfirmation)
        ->and($donation->getFirstMedia('transfer_receipt')->disk)->toBe('local');

    Mail::assertQueued(TransferReceiptReceived::class);
});

it('needs a receipt for Easypaisa too', function () {
    walletForm('easypaisa')->assertSee(SampleDetails::EASYPAISA_NUMBER)
        ->call('donate')
        ->assertHasErrors(['receipt' => 'required']);

    expect(Donation::count())->toBe(0);
});

it('lets Finance confirm a wallet payment with wallet wording', function () {
    walletForm('easypaisa')->set('receipt', UploadedFile::fake()->image('r.png'))->call('donate');
    $finance = User::factory()->create();
    $finance->assignRole(Role::Finance->value);

    Livewire::actingAs($finance)->test(ViewDonation::class, ['record' => Donation::sole()->getKey()])
        ->assertActionVisible('viewTransferReceipt')
        ->mountAction('confirmTransfer')
        ->callMountedAction();

    expect(Donation::sole()->status)->toBe(DonationStatus::Paid);
    Mail::assertQueued(DonationReceipt::class);
});

it('labels sample details clearly on test copies', function () {
    walletForm('bank_transfer')
        ->assertSee('Sample details for testing. Do not send money to these.')
        ->assertSee(SampleDetails::RAAST_ID)
        ->assertSee('2010-0081-002350-01-8');
});

// ── The live site never shows samples ─────────────────────────────────────

it('hides sample wallets and sample QR codes on the live site', function () {
    liveSite();

    expect(PaymentMethod::available())->toBe([PaymentMethod::BankTransfer]);

    $bank = PaymentDetails::for(PaymentMethod::BankTransfer);
    expect($bank->isSample)->toBeFalse()
        ->and($bank->qrUrl)->toBeNull()
        ->and($bank->rows)->not->toHaveKey('Raast ID')
        ->and($bank->rows['Account number'])->toBe('2010-0081-002350-01-8');

    $this->get('/donate')->assertDontSee(SampleDetails::JAZZCASH_NUMBER)->assertDontSee('Sample details');
});

it('shows real wallet details and the provider QR code on the live site', function () {
    liveSite();
    realWallets();

    expect(PaymentMethod::available())->toBe([PaymentMethod::JazzCash, PaymentMethod::Easypaisa, PaymentMethod::BankTransfer]);

    $jazz = PaymentDetails::for(PaymentMethod::JazzCash);
    expect($jazz->isSample)->toBeFalse()
        ->and($jazz->rows['JazzCash number'])->toBe('0301 2345678')
        ->and($jazz->qrUrl)->toEndWith('payment-qr/jazzcash.png');

    expect(PaymentDetails::for(PaymentMethod::Easypaisa)->qrUrl)->toBeNull();
});

it('hides a wallet when its number is removed', function () {
    $s = app(DonationSettings::class);
    $s->easypaisa_number = null;
    $s->save();

    expect(PaymentMethod::available())->not->toContain(PaymentMethod::Easypaisa);
});

it('never seeds samples on the live site', function () {
    $s = app(DonationSettings::class);
    $s->jazzcash_number = null;
    $s->save();

    liveSite();
    $this->seed(SamplePaymentDetailsSeeder::class);

    expect(app(DonationSettings::class)->jazzcash_number)->toBeNull();
});

it('lets Super Admin enter real wallet details and upload a QR code', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SuperAdmin->value);

    Livewire::actingAs($admin)->test(ManageDonationSettings::class)
        ->assertSee('Some details below are samples for testing.')
        ->fillForm([
            'jazzcash_title' => 'Sab Ka Sahara Welfare Foundation',
            'jazzcash_number' => '0301 2345678',
            'jazzcash_qr' => [UploadedFile::fake()->image('jazzcash-qr.png', 400, 400)],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $s = app(DonationSettings::class);
    expect($s->jazzcash_number)->toBe('0301 2345678')
        ->and($s->jazzcash_qr)->toStartWith('payment-qr/');
    Storage::disk('public')->assertExists($s->jazzcash_qr);
});
