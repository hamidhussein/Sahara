<?php

use App\Enums\Role;
use App\Filament\Pages\ManageDonationSettings;
use App\Livewire\DonateForm;
use App\Mail\TransferReceiptToCheck;
use App\Models\Donation;
use App\Models\User;
use App\Settings\DonationSettings;
use Database\Seeders\DatabaseSeeder;
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

function financeUser(string $email, Role $role = Role::Finance): User
{
    $user = User::factory()->create(['email' => $email]);
    $user->assignRole($role->value);

    return $user;
}

function donateWithReceipt(string $method = 'jazzcash', string $type = 'sadaqah'): void
{
    Livewire::withQueryParams(['type' => $type, 'amount' => '2500'])->test(DonateForm::class)
        ->set('name', 'Ayesha Khan')->set('phone', '0312 7654321')->set('email', 'ayesha@example.com')
        ->call('next')
        ->set('method', $method)
        ->set('receipt', UploadedFile::fake()->image('confirmation.png'))
        ->call('donate')
        ->assertHasNoErrors();
}

it('emails every Finance user when a donor uploads a receipt', function () {
    financeUser('finance1@example.org');
    financeUser('finance2@example.org');
    financeUser('editor@example.org', Role::Editor);

    donateWithReceipt('jazzcash');

    Mail::assertQueued(TransferReceiptToCheck::class, fn ($mail) => $mail->hasTo('finance1@example.org')
        && $mail->hasTo('finance2@example.org')
        && ! $mail->hasTo('editor@example.org')
        && $mail->donation->is(Donation::sole()));
    Mail::assertQueuedCount(2); // the Finance alert and the donor's "receipt received" email
});

it('sends the alert to the address set in Donation settings instead', function () {
    financeUser('finance1@example.org');
    $settings = app(DonationSettings::class);
    $settings->finance_email = 'accounts@subkasahara.org';
    $settings->save();

    donateWithReceipt('easypaisa');

    Mail::assertQueued(TransferReceiptToCheck::class, fn ($mail) => $mail->hasTo('accounts@subkasahara.org') && ! $mail->hasTo('finance1@example.org'));
});

it('falls back to the site email when there is no Finance user', function () {
    donateWithReceipt('jazzcash');

    Mail::assertQueued(TransferReceiptToCheck::class, fn ($mail) => $mail->hasTo('info@subkasahara.org'));
});

it('tells Finance what to check and links to the admin, without attaching the receipt', function () {
    donateWithReceipt('jazzcash');
    $donation = Donation::sole();
    $mail = new TransferReceiptToCheck($donation);

    $mail->assertHasSubject("Receipt to check: Rs 2,500 by JazzCash ({$donation->reference})");
    $mail->assertHasReplyTo('ayesha@example.com');
    $mail->assertSeeInHtml('Ayesha Khan')
        ->assertSeeInHtml('0312 7654321')
        ->assertSeeInHtml($donation->reference)
        ->assertSeeInHtml('/admin/donations/'.$donation->getKey())
        ->assertDontSeeInHtml('Check eligibility first');
    expect($mail->attachments)->toBeEmpty()->and($mail->rawAttachments)->toBeEmpty()->and($mail->diskAttachments)->toBeEmpty();
});

it('warns Finance when Zakat needs an eligibility check', function () {
    donateWithReceipt('jazzcash');
    $donation = Donation::sole();
    $donation->forceFill(['needs_eligibility_review' => true])->save();

    (new TransferReceiptToCheck($donation))->assertSeeInHtml('Check eligibility first');
});

it('lets Super Admin set where receipt alerts go', function () {
    $admin = financeUser('admin@example.org', Role::SuperAdmin);

    Livewire::actingAs($admin)->test(ManageDonationSettings::class)
        ->fillForm(['finance_email' => 'not-an-email'])
        ->call('save')
        ->assertHasFormErrors(['finance_email'])
        ->fillForm(['finance_email' => 'accounts@subkasahara.org'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(DonationSettings::class)->finance_email)->toBe('accounts@subkasahara.org');
});
