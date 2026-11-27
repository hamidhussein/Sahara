<?php

use App\Enums\ContentLanguage;
use App\Enums\PostCategory;
use App\Enums\ProjectStatus;
use App\Livewire\ProjectBrowser;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use Database\Seeders\ContentSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(ContentSeeder::class));

it('seeds the plan’s programs, projects and partners without duplicating on a second run', function () {
    $this->seed(ContentSeeder::class);

    expect(Program::count())->toBe(8)
        ->and(Project::count())->toBe(11)
        ->and(Partner::count())->toBe(8);
});

it('lists programs and shows a program with its projects', function () {
    $this->get('/programs')->assertOk()->assertSee('Clean Water')->assertSee('Service at Religious Events');

    $this->get('/programs/clean-water')->assertOk()
        ->assertSee('Safe drinking water for every home.')
        ->assertSee('Miachar Water Filtration Plant')
        ->assertSee(route('donate', ['cause' => 'clean-water']), false);
});

it('hides unpublished programs and their projects', function () {
    Program::where('slug', 'clean-water')->update(['is_published' => false]);

    $this->get('/programs/clean-water')->assertNotFound();
    $this->get('/projects/miachar-water-plant')->assertNotFound();
    $this->get('/programs')->assertDontSee('Safe drinking water for every home.');
});

it('shows only confirmed key facts on a project page', function () {
    $this->get('/projects/miachar-water-plant')->assertOk()
        ->assertSee('Miachar, Nagar')
        ->assertSee('Gift of Mr Hadi Shahzad')
        ->assertDontSee('Cost')
        ->assertDontSee('People served');
});

it('filters projects by program, district and status', function () {
    Livewire::test(ProjectBrowser::class)
        ->assertSee('Free Eye Camp 2026')->assertSee('Miachar Water Filtration Plant')
        ->set('program', 'health')
        ->assertSee('Free Eye Camp 2026')->assertDontSee('Miachar Water Filtration Plant')
        ->set('program', '')->set('district', 'Gilgit')
        ->assertSee('Pani Har Ghar Tak')->assertDontSee('Free Eye Camp 2026')
        ->set('status', ProjectStatus::Planned->value)
        ->assertSee('No projects match these filters yet.');
});

it('keeps draft and future news off the site', function () {
    $draft = Post::where('slug', 'phakar-youth-smoking-naswar-seminar')->first();
    $this->get('/news')->assertDontSee($draft->title);
    $this->get('/news/'.$draft->slug)->assertNotFound();

    $draft->update(['published_at' => now()->addDay()]);
    $this->get('/news/'.$draft->slug)->assertNotFound();

    $draft->update(['published_at' => now()->subHour()]);
    $this->get('/news')->assertSee($draft->title);
    $this->get('/news/'.$draft->slug)->assertOk()->assertSee('Drug-Free Phakar campaign');
    $this->get('/')->assertSee($draft->title);
});

it('filters news by category and language', function () {
    Post::create(['title' => 'Water news', 'category' => PostCategory::Water, 'body' => '<p>x</p>', 'published_at' => now()->subDay()]);
    Post::create(['title' => 'صحت کی خبر', 'category' => PostCategory::Health, 'language' => ContentLanguage::Urdu, 'body' => '<p>x</p>', 'published_at' => now()->subDay()]);

    $this->get('/news?category=water')->assertSee('Water news')->assertDontSee('صحت کی خبر');
    $this->get('/news?language=ur')->assertSee('صحت کی خبر')->assertDontSee('Water news');
});

it('shows Urdu posts right-to-left and gives Urdu titles a working web address', function () {
    $post = Post::create(['title' => 'صحت کی خبر', 'category' => PostCategory::Health, 'language' => ContentLanguage::Urdu, 'body' => '<p>متن</p>', 'published_at' => now()->subDay()]);

    // Urdu titles are spelled out in Latin letters for the web address.
    expect($post->slug)->toBe('sht-ky-khbr');

    $this->get('/news/'.$post->slug)->assertOk()
        ->assertSee('lang="ur" dir="rtl"', false)
        ->assertSee('noto-nastaliq-urdu', false);

    // English pages do not load the Urdu fonts.
    $this->get('/programs')->assertDontSee('noto-nastaliq-urdu', false);
});

it('strips scripts from editor-written content', function () {
    Post::create(['title' => 'Safe', 'slug' => 'safe', 'category' => PostCategory::Announcements, 'body' => '<p>Hello</p><script>alert(1)</script><img src=x onerror="alert(2)">', 'published_at' => now()->subDay()]);

    $this->get('/news/safe')->assertOk()
        ->assertSee('Hello')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('onerror', false);
});

it('keeps slugs unique', function () {
    $program = Program::first();
    $a = Project::create(['program_id' => $program->id, 'name' => 'New plant', 'summary' => 's']);
    $b = Project::create(['program_id' => $program->id, 'name' => 'New plant', 'summary' => 's']);

    expect($a->slug)->toBe('new-plant')->and($b->slug)->toBe('new-plant-2');
});

it('shows completed, ongoing and upcoming projects from the November 2026 update', function () {
    Livewire::test(ProjectBrowser::class)
        ->set('status', ProjectStatus::Completed->value)
        ->assertSee('Four Water Filtration Plants: Bar, Budalas, Chaprote and Minapin')
        ->assertSee('1,200 ration packages')->assertSee('600 blankets given')
        ->set('status', ProjectStatus::Ongoing->value)
        ->assertSee('Free Medical Camp, Phakar')->assertSee('30 students supported')
        ->set('status', ProjectStatus::Planned->value)
        ->assertSee('Winter Support Programme 2026–27')->assertSee('Four More Water Filtration Plants')
        ->assertDontSee('Ration Distribution');

    $this->get('/projects/water-plants-2027')->assertOk()
        ->assertSee('Jutal, Hakucher, Dadimal Paeen and one more village')
        ->assertSee('Starts')->assertSee('1 April 2027')->assertDontSee('Started');
});
