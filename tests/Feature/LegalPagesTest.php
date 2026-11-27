<?php

use App\Enums\Role;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('publishes the plan’s legal drafts with confirmed contact details and no unconfirmed time limits', function () {
    $this->get('/privacy')->assertOk()
        ->assertSee('Your phone number is never shown publicly.')
        ->assertSee('mailto:info@subkasahara.org', false)
        ->assertSee('Last updated');

    $this->get('/refund-policy')->assertOk()
        ->assertSee('Once a donation has been spent on a project or distributed, it cannot be refunded.')
        ->assertDontSee('[7 days]')->assertDontSee('7 days')->assertDontSee('14 working days');

    $this->get('/terms')->assertOk()->assertSee('These terms are governed by the laws of Pakistan.');
});

it('shows a friendly 404 with ways forward', function () {
    $this->get('/this-does-not-exist')->assertNotFound()
        ->assertSee('This page has wandered off.')
        ->assertSee(route('donate'), false)
        ->assertSee(route('contact'), false)
        ->assertSee('Call us: +92 5811 453014');
});

it('lets Editors change the text but not the address of a legal page', function () {
    $editor = User::factory()->create();
    $editor->assignRole(Role::Editor->value);
    $page = Page::where('slug', 'refund-policy')->sole();

    Livewire::actingAs($editor)->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormFieldIsDisabled('slug')
        ->fillForm(['body' => '<p>Contact us within 7 days of your donation.</p>'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh()->slug)->toBe('refund-policy');
    $this->get('/refund-policy')->assertSee('Contact us within 7 days of your donation.');

    expect(Gate::forUser($editor)->allows('delete', $page))->toBeFalse()
        ->and(Gate::forUser($editor)->allows('create', Page::class))->toBeFalse();
});
