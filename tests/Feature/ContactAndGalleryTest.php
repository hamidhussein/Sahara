<?php

use App\Enums\Role;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Filament\Resources\GalleryAlbums\Pages\CreateGalleryAlbum;
use App\Livewire\ContactForm;
use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\GalleryAlbum;
use App\Models\User;
use App\Settings\SiteSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Mail::fake();
    Storage::fake('public');
});

function cgStaff(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

// ── Contact page ──────────────────────────────────────────────────────────

it('shows the confirmed contact details and hides what is not confirmed', function () {
    $this->get('/contact')->assertOk()
        ->assertSee('G-18, Hussainia Complex, Khazana Road, Gilgit')
        ->assertSee('+92 5811 453014')
        ->assertSee('info@subkasahara.org')
        ->assertSee('24 hours a day, 7 days a week')
        ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=', false)
        ->assertDontSee('Office hours')
        ->assertDontSee('Ambulance</dt>', false);

    $settings = app(SiteSettings::class);
    $settings->office_hours = 'Monday–Saturday, 9:00 am – 5:00 pm';
    $settings->ambulance_phone = '+92 355 0000000';
    $settings->save();

    $this->get('/contact')->assertSee('Office hours')->assertSee('Monday–Saturday, 9:00 am – 5:00 pm')->assertSee('+92 355 0000000');
});

it('saves a message, emails the office and replies to the sender', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'Sana Ali')
        ->set('contact', 'Sana@Example.com')
        ->set('subject', 'Volunteering at the eye camp')
        ->set('message', 'I am a nurse and would like to help at the next eye camp.')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('sent', true)
        ->assertSee('Thank you for writing to us.');

    $message = ContactMessage::sole();
    expect($message->email)->toBe('sana@example.com')->and($message->phone)->toBeNull();

    Mail::assertQueued(ContactMessageReceived::class, fn ($m) => $m->hasTo('info@subkasahara.org') && $m->hasReplyTo('sana@example.com'));
    Mail::assertQueued(ContactAutoReply::class, fn ($m) => $m->hasTo('sana@example.com'));
});

it('accepts a phone number instead of an email, without an auto-reply', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'Karim')->set('contact', '0355 1234567')
        ->set('subject', 'Wheelchair')->set('message', 'My father needs a wheelchair, please call me.')
        ->call('send')->assertHasNoErrors();

    expect(ContactMessage::sole()->phone)->toBe('0355 1234567');
    Mail::assertQueued(ContactMessageReceived::class);
    Mail::assertNotQueued(ContactAutoReply::class);
});

it('explains what is missing or wrong', function () {
    Livewire::test(ContactForm::class)->call('send')
        ->assertHasErrors(['name' => 'required', 'contact' => 'required', 'subject' => 'required', 'message' => 'required']);

    Livewire::test(ContactForm::class)
        ->set('name', 'A')->set('contact', 'not an address')->set('subject', 'Hi')->set('message', 'A real message here.')
        ->call('send')->assertHasErrors(['contact']);

    expect(ContactMessage::count())->toBe(0);
});

it('quietly drops messages from bots that fill the hidden field', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'Bot')->set('contact', 'bot@example.com')->set('subject', 'Offer')
        ->set('message', 'Buy cheap things now from us.')->set('website', 'http://spam.example')
        ->call('send')->assertSet('sent', true);

    expect(ContactMessage::count())->toBe(0);
    Mail::assertNothingQueued();
});

it('lets Editors read and mark messages answered, but not Finance', function () {
    $message = ContactMessage::create(['name' => 'Sana', 'email' => 's@example.com', 'subject' => 'Hello', 'message' => 'A question for you.']);
    $editor = cgStaff(Role::Editor);

    $this->actingAs(cgStaff(Role::Finance))->get('/admin/contact-messages')->assertForbidden();
    $this->actingAs($editor)->get('/admin/contact-messages')->assertOk()->assertSee('Hello');

    Livewire::actingAs($editor)->test(ViewContactMessage::class, ['record' => $message->getKey()])
        ->callAction('markAnswered', data: ['staff_notes' => 'Replied by email.'])
        ->assertHasNoErrors();

    expect($message->refresh()->handled_at)->not->toBeNull()
        ->and($message->handled_by)->toBe($editor->id)
        ->and($message->staff_notes)->toBe('Replied by email.');
});

// ── Gallery ───────────────────────────────────────────────────────────────

it('shows only albums that have photos or videos', function () {
    $this->get('/gallery')->assertOk()
        ->assertSee('Clean Water Plants')->assertSee('Pani Har Ghar Tak – Danyor')
        ->assertDontSee(route('gallery.show', 'qurbani'), false)
        ->assertDontSee(route('gallery.show', 'ramadan-packages'), false);

    $this->get('/gallery/qurbani')->assertNotFound();
    $this->get('/gallery/clean-water-plants')->assertOk()->assertSee('Photo viewer')->assertSee('Clean Water Plants, photo 1');
});

it('shows YouTube videos as thumbnails that load the player only when tapped', function () {
    GalleryAlbum::where('slug', 'qurbani')->update(['videos' => json_encode([
        ['url' => 'https://youtu.be/dQw4w9WgXcQ', 'title' => 'Qurbani 2026'],
        ['url' => 'https://example.com/not-youtube', 'title' => 'Ignored'],
    ])]);

    $this->get('/gallery')->assertSee(route('gallery.show', 'qurbani'), false)->assertSee('i.ytimg.com/vi/dQw4w9WgXcQ', false);
    $this->get('/gallery/qurbani')->assertOk()
        ->assertSee('Play video: Qurbani 2026')
        ->assertDontSee('Ignored')
        ->assertDontSee('<iframe src=', false);
});

it('recognises the common YouTube link formats', function (string $url, ?string $id) {
    expect(GalleryAlbum::youtubeId($url))->toBe($id);
})->with([
    ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://youtu.be/dQw4w9WgXcQ?t=10', 'dQw4w9WgXcQ'],
    ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    ['https://vimeo.com/123', null],
]);

it('lets Editors create an album with photos and refuses non-YouTube video links', function () {
    Livewire::actingAs(cgStaff(Role::Editor))->test(CreateGalleryAlbum::class)
        ->fillForm(['title' => 'Winter Package 2026', 'videos' => [['url' => 'https://vimeo.com/123', 'title' => '']]])
        ->call('create')
        ->assertHasFormErrors();

    Livewire::actingAs(cgStaff(Role::Editor))->test(CreateGalleryAlbum::class)
        ->fillForm([
            'title' => 'Winter Package 2026',
            'photos' => [UploadedFile::fake()->image('blankets.jpg', 1200, 800)],
            'videos' => [],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->get('/gallery/winter-package-2026')->assertOk()->assertSee('Winter Package 2026, photo 1');
});
