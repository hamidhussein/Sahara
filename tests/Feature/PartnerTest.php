<?php

use App\Livewire\ContactForm;
use Database\Seeders\DatabaseSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

it('explains the partnership models and areas of work', function () {
    $this->get('/partner-with-us')->assertOk()
        ->assertSee('Complete project management and implementation')
        ->assertSee('Funded projects with local implementation')
        ->assertSee('Local coordination and project facilitation')
        ->assertSee('Why partner with Sahara?')
        ->assertSee(route('programs.show', 'clean-water'))
        ->assertSee(route('contact', ['subject' => 'Partnership enquiry']), false)
        ->assertSee('mailto:info@subkasahara.org', false);
});

it('links Partner With Us from the footer and lists it in the sitemap', function () {
    $this->get('/')->assertSee(route('partner'));
    $this->get('/sitemap.xml')->assertSee(route('partner'));
});

it('opens the contact form with the subject from the link', function () {
    Livewire::withQueryParams(['subject' => 'Partnership enquiry'])
        ->test(ContactForm::class)
        ->assertSet('subject', 'Partnership enquiry');

    Livewire::withQueryParams(['subject' => ['not', 'text']])
        ->test(ContactForm::class)
        ->assertSet('subject', '');
});
