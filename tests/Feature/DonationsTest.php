<?php

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Filament\Resources\Donations\Pages\ListDonations;
use App\Filament\Resources\Donations\Pages\ViewDonation;
use App\Livewire\DonateForm;
use App\Livewire\ZakatCalculator;
use App\Mail\DonationReceipt;
use App\Mail\TransferReceiptReceived;
use App\Models\Cause;
use App\Models\Donation;
use App\Models\GivingType;
use App\Models\User;
use App\Payments\Gateways\FakeGateway;
use App\Settings\DonationSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Mail::fake();
    Storage::fake('local');
});

function staff(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

/** Fill steps 1–3 of the Donate form. */
function filledDonateForm(string $type = 'sadaqah', ?string $cause = null, int $amount = 5000)
{
    $form = Livewire::test(DonateForm::class)->call('chooseType', $type)->call('next');
    if ($cause) {
        $form->call('chooseCause', $cause);
    }

    return $form->call('next')
        ->call('setAmount', $amount)
        ->set('name', 'Ali Raza')
        ->set('phone', '0300 1234567')
        ->set('email', 'ali@example.com')
        ->call('next');
}

function enableFakeJazzCash(): void
{
    config(['payments.methods.jazzcash.enabled' => true, 'payments.methods.jazzcash.driver' => 'fake']);
}

function fakeCallback(Donation $donation, string $outcome = 'approved', ?int $amount = null): array
{
    $data = [
        'reference' => $donation->reference,
        'amount' => (string) ($amount ?? $donation->amount),
        'outcome' => $outcome,
        'transaction_id' => 'T-123',
    ];

    return $data + ['signature' => FakeGateway::sign($data)];
}

// ── The Donate page ────────────────────────────────────────────────────────

it('shows all nine types of giving and only bank transfer until gateways are approved', function () {
    $this->get('/donate')->assertOk()->assertSee('Zakat')->assertSee('Khums (Sehm-e-Sadat / Sehm-e-Imam)')
        ->assertSee('General donation or membership fee');

    expect(PaymentMethod::available())->toBe([PaymentMethod::BankTransfer]);
});

it('preselects type, cause and amount from the link', function () {
    Livewire::withQueryParams(['type' => 'zakat', 'cause' => 'ramadan-package', 'amount' => '10000'])
        ->test(DonateForm::class)
        ->assertSet('typeSlug', 'zakat')
        ->assertSet('causeSlug', 'ramadan-package')
        ->assertSet('amountChoice', '10000')
        ->assertSet('step', 3);
});

it('maps a program link to its cause', function () {
    Livewire::withQueryParams(['cause' => 'ambulance-service'])->test(DonateForm::class)->assertSet('causeSlug', 'ambulance');
});

it('explains what is missing at each step', function () {
    Livewire::test(DonateForm::class)
        ->call('next')->assertHasErrors(['typeSlug' => 'required'])
        ->call('chooseType', 'sadaqah')->call('next')->call('next')
        ->call('setAmount', 50)->set('phone', '12')
        ->call('next')
        ->assertHasErrors(['amount', 'name', 'phone'])
        ->assertSee('Choose an amount between Rs 100 and Rs 10,000,000.');
});

it('computes Fitrana from the number of people once the amount is announced', function () {
    $settings = app(DonationSettings::class);
    $settings->fitrana_amount = 600;
    $settings->save();

    Livewire::test(DonateForm::class)->call('chooseType', 'fitrana')->set('fitranaPeople', 5)
        ->assertSet('amount', 3000);
});

// ── Bank transfer ──────────────────────────────────────────────────────────

it('records a bank transfer with a private receipt and sends it to Finance to confirm', function () {
    filledDonateForm('sadaqah', 'winter-package', 5000)
        ->set('method', 'bank_transfer')
        ->set('receipt', UploadedFile::fake()->image('receipt.jpg'))
        ->call('donate')
        ->assertHasNoErrors()
        ->assertRedirectContains('/donate/thank-you/SSWF-');

    $donation = Donation::sole();
    expect($donation->status)->toBe(DonationStatus::AwaitingConfirmation)
        ->and($donation->amount)->toBe(5000)
        ->and($donation->cause->slug)->toBe('winter-package')
        ->and($donation->reference)->toMatch('/^SSWF-\d{4}-000001$/')
        ->and($donation->needs_eligibility_review)->toBeFalse();

    $media = $donation->getFirstMedia('transfer_receipt');
    expect($media->disk)->toBe('local');
    Storage::disk('local')->assertExists($media->getPathRelativeToRoot());

    Mail::assertQueued(TransferReceiptReceived::class, fn ($mail) => $mail->hasTo('ali@example.com'));
    Mail::assertNotQueued(DonationReceipt::class);
});

it('needs a receipt for a bank transfer', function () {
    filledDonateForm()->set('method', 'bank_transfer')->call('donate')
        ->assertHasErrors(['receipt' => 'required']);

    expect(Donation::count())->toBe(0);
});

it('flags Zakat for a cause Finance has not marked eligible, and not once it is', function () {
    filledDonateForm('zakat', 'clean-water')->set('method', 'bank_transfer')
        ->set('receipt', UploadedFile::fake()->image('r.jpg'))
        ->assertSee('We will contact you before using your gift for Clean Water.')
        ->call('donate');

    expect(Donation::sole()->needs_eligibility_review)->toBeTrue();

    Cause::where('slug', 'ramadan-package')->sole()->eligibleGivingTypes()->attach(GivingType::where('slug', 'zakat')->sole());

    filledDonateForm('zakat', 'ramadan-package')->set('method', 'bank_transfer')
        ->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');

    expect(Donation::latest('id')->first()->needs_eligibility_review)->toBeFalse();
});

it('shows the thank-you page only through its signed link', function () {
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');
    $donation = Donation::sole();

    $this->get('/donate/thank-you/'.$donation->reference)->assertForbidden();
    $this->get(DonateForm::thankYouUrl($donation))->assertOk()
        ->assertSee('Thank you, Ali Raza!')
        ->assertSee('Our team will confirm your donation within 24 hours');
});

it('lets Finance confirm a transfer, which emails the receipt', function () {
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');
    $donation = Donation::sole();
    $finance = staff(Role::Finance);

    Livewire::actingAs($finance)->test(ViewDonation::class, ['record' => $donation->getKey()])
        ->callAction('confirmTransfer')
        ->assertHasNoErrors();

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Paid)
        ->and($donation->confirmed_by)->toBe($finance->id)
        ->and($donation->receipt_sent_at)->not->toBeNull();
    Mail::assertQueued(DonationReceipt::class, fn ($mail) => $mail->hasTo('ali@example.com'));
});

it('lets Finance reject a transfer with a reason', function () {
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');
    $donation = Donation::sole();

    Livewire::actingAs(staff(Role::Finance))->test(ViewDonation::class, ['record' => $donation->getKey()])
        ->callAction('rejectTransfer', data: ['reason' => 'Not in the bank statement'])
        ->assertHasNoErrors();

    expect($donation->refresh()->status)->toBe(DonationStatus::Failed)
        ->and($donation->finance_notes)->toContain('Not in the bank statement');
    Mail::assertNotQueued(DonationReceipt::class);
});

it('shows transfer receipts only to Finance and Super Admin', function () {
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');
    $url = route('donations.transfer-receipt', Donation::sole());

    $this->get($url)->assertRedirect();
    $this->actingAs(staff(Role::Editor))->get($url)->assertForbidden();
    $this->actingAs(staff(Role::Finance))->get($url)->assertOk();
    $this->actingAs(staff(Role::SuperAdmin))->get($url)->assertOk();
});

// ── Online payments (fake provider) ────────────────────────────────────────

it('marks an online donation paid only after a correctly signed callback', function () {
    enableFakeJazzCash();

    filledDonateForm('khairat', null, 2500)->set('method', 'jazzcash')->call('donate')
        ->assertRedirectContains('/payments/fake/SSWF-');

    $donation = Donation::sole();
    expect($donation->status)->toBe(DonationStatus::Pending);

    $this->get(route('payments.fake.checkout', $donation->reference))->assertOk()->assertSee('Pay Rs 2,500');

    $this->post(route('payments.callback', 'jazzcash'), fakeCallback($donation))
        ->assertRedirectContains('/donate/thank-you/'.$donation->reference);

    expect($donation->refresh()->status)->toBe(DonationStatus::Paid)
        ->and($donation->gateway_transaction_id)->toBe('T-123');
    Mail::assertQueued(DonationReceipt::class, 1);

    // Providers may call back twice: still one receipt.
    $this->post(route('payments.callback', 'jazzcash'), fakeCallback($donation));
    Mail::assertQueued(DonationReceipt::class, 1);
});

it('rejects a callback with a forged signature', function () {
    enableFakeJazzCash();
    filledDonateForm()->set('method', 'jazzcash')->call('donate');
    $donation = Donation::sole();

    $forged = fakeCallback($donation);
    $forged['signature'] = str_repeat('0', 64);

    $this->post(route('payments.callback', 'jazzcash'), $forged)->assertForbidden();
    expect($donation->refresh()->status)->toBe(DonationStatus::Pending);
});

it('refuses a payment for a different amount', function () {
    enableFakeJazzCash();
    filledDonateForm('sadaqah', null, 5000)->set('method', 'jazzcash')->call('donate');
    $donation = Donation::sole();

    $this->post(route('payments.callback', 'jazzcash'), fakeCallback($donation, amount: 100))
        ->assertRedirectContains('/donate/payment-failed/');

    expect($donation->refresh()->status)->toBe(DonationStatus::Failed);
    Mail::assertNotQueued(DonationReceipt::class);
});

it('shows the payment failed page when the donor declines', function () {
    enableFakeJazzCash();
    filledDonateForm()->set('method', 'jazzcash')->call('donate');
    $donation = Donation::sole();

    $response = $this->post(route('payments.callback', 'jazzcash'), fakeCallback($donation, 'declined'));
    $this->get($response->headers->get('Location'))->assertOk()->assertSee('No money has been taken.');
    expect($donation->refresh()->status)->toBe(DonationStatus::Failed);
});

it('ignores callbacks for payment methods that are switched off', function () {
    $this->post(route('payments.callback', 'easypaisa'), ['reference' => 'x'])->assertNotFound();
});

it('will not let anyone choose a switched-off method', function () {
    filledDonateForm()->set('method', 'card')->call('donate')->assertHasErrors(['method']);
});

// ── Admin access and records ───────────────────────────────────────────────

it('keeps donations away from Editors and Services', function (Role $role) {
    $this->actingAs(staff($role))->get('/admin/donations')->assertForbidden();
    $this->actingAs(staff($role))->get('/admin/causes')->assertForbidden();
})->with([Role::Editor, Role::Services]);

it('never lets anyone, even Super Admin, create or delete donation records', function () {
    $admin = staff(Role::SuperAdmin);
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');

    expect(Gate::forUser($admin)->allows('delete', Donation::sole()))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('create', Donation::class))->toBeFalse()
        ->and(Gate::forUser($admin)->allows('update', Donation::sole()))->toBeTrue();

    $this->actingAs($admin)->get('/admin/donations/create')->assertNotFound();
});

it('lists transfers to confirm for Finance and exports them', function () {
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');

    Livewire::actingAs(staff(Role::Finance))->test(ListDonations::class, ['activeTab' => 'to_confirm'])
        ->assertCanSeeTableRecords(Donation::all())
        ->callAction('export')
        ->assertFileDownloaded('sswf-donations-'.now()->format('Y-m-d').'.csv');
});

it('produces a PDF receipt', function () {
    filledDonateForm()->set('method', 'bank_transfer')->set('receipt', UploadedFile::fake()->image('r.jpg'))->call('donate');
    $donation = Donation::sole();
    $donation->markPaid();

    expect(substr(DonationReceipt::pdf($donation)->output(), 0, 4))->toBe('%PDF');
});

// ── Zakat calculator and home page ─────────────────────────────────────────

it('calculates 2.5% of zakatable wealth above the nisab', function () {
    $settings = app(DonationSettings::class);
    $settings->nisab_amount = 150000;
    $settings->save();

    Livewire::test(ZakatCalculator::class)
        ->set('cash', '100,000')->assertSet('zakat', 0)
        ->set('gold', '300000')->set('debts', '50000')->assertSet('zakat', 8750)
        ->assertSee(route('donate', ['type' => 'zakat', 'amount' => 8750]));
});

it('shows the appeals on the home page, with progress only when a goal is set', function () {
    $this->get('/')->assertSee('Winter Package 2026')->assertSee('The Sahara Complex')
        ->assertSee('A Better Ambulance Service')->assertDontSee('role="progressbar"', false);

    $winter = Cause::where('slug', 'winter-package')->sole();
    $winter->update(['goal_amount' => 100000]);
    Donation::create([
        'giving_type_id' => GivingType::first()->id, 'cause_id' => $winter->id, 'amount' => 25000,
        'donor_name' => 'A', 'donor_phone' => '03001234567', 'payment_method' => PaymentMethod::BankTransfer,
        'status' => DonationStatus::Paid, 'paid_at' => now(),
    ]);

    $this->get('/')->assertSee('aria-valuenow="25"', false)->assertSee('Raised <strong class="text-ink">Rs 25,000</strong> of Rs 100,000', false);
});
