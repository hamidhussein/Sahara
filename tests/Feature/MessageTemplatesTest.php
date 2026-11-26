<?php

use App\Enums\DonationStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Filament\Pages\ManageMessageSettings;
use App\Mail\ContactAutoReply;
use App\Mail\DonationReceipt;
use App\Mail\TransferReceiptReceived;
use App\Messaging\DonorMessages;
use App\Messaging\MessageTemplates;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\GivingType;
use App\Models\User;
use App\Settings\MessageSettings;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function tmplDonation(array $overrides = []): Donation
{
    $donation = Donation::create($overrides + [
        'giving_type_id' => GivingType::where('slug', 'zakat')->value('id'),
        'amount' => 12000,
        'donor_name' => 'Sana Ali',
        'donor_phone' => '0300 1234567',
        'donor_email' => 'sana@example.com',
        'payment_method' => PaymentMethod::Easypaisa,
        'status' => DonationStatus::AwaitingConfirmation,
    ]);
    $donation->markPaid();

    return $donation;
}

function superAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::SuperAdmin->value);

    return $user;
}

function editTemplate(string $key, ?string $text): void
{
    $settings = app(MessageSettings::class);
    $settings->{$key} = $text;
    $settings->save();
}

// ── Rendering ─────────────────────────────────────────────────────────────

it('uses the default wording until someone edits it', function () {
    $text = DonorMessages::receiptConfirmation(tmplDonation());

    expect($text)->toContain('Dear Sana Ali,')
        ->toContain('Rs 12,000 as Zakat for Where it is needed most')
        ->toContain('/receipts/SSWF-')
        ->toEndWith('Sab Ka Sahara Welfare Foundation');
});

it('sends the edited WhatsApp wording with the donation filled in', function () {
    editTemplate('whatsapp_receipt', "Assalam-o-Alaikum {name}! JazakAllah for your {type} of {amount}.\nReceipt {reference}: {receipt_link}");
    $donation = tmplDonation();

    expect(DonorMessages::receiptConfirmation($donation))
        ->toStartWith('Assalam-o-Alaikum Sana Ali! JazakAllah for your Zakat of Rs 12,000.')
        ->toContain("Receipt {$donation->reference}: ".url('/receipts/'));

    expect(DonorMessages::whatsappLink($donation))->toContain(rawurlencode('Assalam-o-Alaikum Sana Ali!'));
});

it('falls back to the default when a template is emptied', function () {
    editTemplate('whatsapp_receipt', '');

    expect(MessageTemplates::text('whatsapp_receipt'))->toBe(MessageTemplates::default('whatsapp_receipt'));
});

it('uses the edited email subject and words, and escapes what donors typed', function () {
    editTemplate('receipt_email_subject', 'Receipt {reference} – thank you {name}');
    editTemplate('receipt_email_body', "Dear {name},\n\nYour {type} reached us on {date}.");
    $donation = tmplDonation(['donor_name' => 'Ali <b>Raza</b>']);

    $mail = new DonationReceipt($donation);
    $mail->assertHasSubject("Receipt {$donation->reference} – thank you Ali <b>Raza</b>");
    $mail->assertSeeInHtml('Dear Ali &lt;b&gt;Raza&lt;/b&gt;,', false);
    $mail->assertDontSeeInHtml('<b>Raza</b>', false);
    $mail->assertSeeInHtml('Your Zakat reached us on', false);
    $mail->assertSeeInHtml('Receipt number', false); // the fixed details table stays
});

it('uses the edited “receipt received” and contact reply wording', function () {
    editTemplate('received_email_body', 'Shukriya {name}, we will check {reference} today.');
    editTemplate('contact_reply_subject', 'Re: {subject}');
    $donation = tmplDonation();

    (new TransferReceiptReceived($donation))->assertSeeInHtml("Shukriya Sana Ali, we will check {$donation->reference} today.", false);

    $message = ContactMessage::create(['name' => 'Karim', 'email' => 'k@example.com', 'subject' => 'Wheelchair', 'message' => 'Please call me back.']);
    (new ContactAutoReply($message))->assertHasSubject('Re: Wheelchair');
});

it('explains template problems in plain words', function () {
    expect(MessageTemplates::problems('whatsapp_receipt', 'Thanks {name}! {receipt_link}'))
        ->toBe(['Keep {reference} in this message.']);

    expect(MessageTemplates::problems('receipt_email_subject', 'Thanks {nmae} – {reference}'))
        ->toHaveCount(1)
        ->and(MessageTemplates::problems('receipt_email_subject', 'Thanks {nmae} – {reference}')[0])
        ->toStartWith('{nmae} is not a placeholder here.');

    expect(MessageTemplates::problems('contact_reply_body', 'Thanks {name}, about {subject}.'))->toBe([]);
});

// ── The admin page ────────────────────────────────────────────────────────

it('shows the current wording and saves edits', function () {
    Livewire::actingAs(superAdmin())->test(ManageMessageSettings::class)
        ->assertFormSet(['whatsapp_receipt' => MessageTemplates::default('whatsapp_receipt')])
        ->assertSee('Preview with sample details')
        ->fillForm(['receipt_email_subject' => 'Your receipt {reference}'])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(MessageSettings::class);
    expect($settings->receipt_email_subject)->toBe('Your receipt {reference}')
        // unchanged messages stay "default", so later improvements still reach them
        ->and($settings->whatsapp_receipt)->toBeNull();
});

it('refuses to save a message without its receipt link or with a wrong placeholder', function () {
    Livewire::actingAs(superAdmin())->test(ManageMessageSettings::class)
        ->fillForm([
            'whatsapp_receipt' => 'Thank you {name}, receipt {reference}.',
            'contact_reply_body' => 'Hello {donor}',
        ])
        ->call('save')
        ->assertHasFormErrors(['whatsapp_receipt', 'contact_reply_body']);

    expect(app(MessageSettings::class)->whatsapp_receipt)->toBeNull();
});

it('resets a message to the default wording', function () {
    editTemplate('received_email_subject', 'Custom subject');

    Livewire::actingAs(superAdmin())->test(ManageMessageSettings::class)
        ->assertFormSet(['received_email_subject' => 'Custom subject'])
        ->callAction(TestAction::make('reset_received_email_subject')->schemaComponent('received_email_subject'))
        ->assertFormSet(['received_email_subject' => MessageTemplates::default('received_email_subject')]);
});

it('is for Super Admin only', function (Role $role) {
    $user = User::factory()->create();
    $user->assignRole($role->value);

    $this->actingAs($user)->get('/admin/manage-message-settings')->assertForbidden();
})->with([Role::Finance, Role::Editor, Role::Services]);
