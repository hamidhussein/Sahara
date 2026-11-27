<?php

use App\Enums\PostCategory;
use App\Http\Controllers\SeoController;
use App\Livewire\DonateForm;
use App\Models\Donation;
use App\Models\GivingType;
use App\Models\Post;
use App\Models\Program;
use App\Settings\SiteSettings;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Cache::forget(SeoController::SITEMAP_CACHE_KEY);
});

function asLiveSite(): void
{
    config(['app.env' => 'production']);
}

// ── robots and noindex ────────────────────────────────────────────────────

it('keeps staging and local copies out of search engines', function () {
    $this->get('/robots.txt')->assertOk()->assertSeeText("User-agent: *\nDisallow: /");
    $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

it('lets search engines into the live site, except private and payment pages', function () {
    asLiveSite();

    $this->get('/robots.txt')->assertOk()
        ->assertSee('Disallow: /admin')
        ->assertSee('Disallow: /donate/thank-you/')
        ->assertSee('Sitemap: '.route('sitemap'))
        ->assertDontSee("Disallow: /\n");

    $this->get('/')->assertDontSee('noindex', false);
    $this->get('/this-page-is-missing')->assertNotFound()->assertSee('noindex', false);
});

it('marks a donor’s thank-you page noindex even on the live site', function () {
    asLiveSite();
    $donation = Donation::create([
        'giving_type_id' => GivingType::first()->id, 'amount' => 1000, 'donor_name' => 'A',
        'donor_phone' => '03001234567', 'payment_method' => 'bank_transfer', 'status' => 'awaiting_confirmation',
    ]);

    $this->get(DonateForm::thankYouUrl($donation))->assertOk()->assertSee('noindex', false);
});

// ── Share tags ────────────────────────────────────────────────────────────

it('gives every page a canonical URL, description and share image', function () {
    $this->get('/about/transparency')
        ->assertSee('<link rel="canonical" href="'.route('transparency').'">', false)
        ->assertSee('<meta property="og:url" content="'.route('transparency').'">', false)
        ->assertSee('<meta property="og:image" content="'.asset('images/hero-danyor-water.jpg').'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

it('uses the Settings description where a page has none of its own', function () {
    $settings = app(SiteSettings::class);
    $settings->seo_description = 'Clean water, health camps and food for families in Gilgit-Baltistan.';
    $settings->save();

    $this->get('/')->assertSee('<meta name="description" content="Clean water, health camps and food for families in Gilgit-Baltistan.">', false);
});

it('describes news as articles, with escaped structured data', function () {
    $post = Post::create([
        'title' => 'Plant opened </script><script>alert(1)</script>',
        'slug' => 'plant-opened', 'category' => PostCategory::Water,
        'body' => '<p>The Minapin plant is open.</p>', 'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/news/plant-opened')->assertOk()
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('"@type":"NewsArticle"', false)
        ->assertSee('article:published_time', false);

    expect(substr_count($response->getContent(), '<script>alert(1)</script>'))->toBe(0);
});

it('tells search engines who SSWF is on the home page', function () {
    $this->get('/')->assertSee('"@type":"NGO"', false)
        ->assertSee('"addressLocality":"Gilgit"', false)
        ->assertSee('"sameAs":["https://www.facebook.com/sswfgilgit/"]', false);
});

// ── Sitemap ───────────────────────────────────────────────────────────────

it('lists published content in the sitemap and leaves out drafts, empty albums and private pages', function () {
    Program::where('slug', 'qurbani')->update(['is_published' => false]);

    $xml = $this->get('/sitemap.xml')->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->getContent();

    expect($xml)
        ->toContain('<loc>'.route('programs.show', 'clean-water').'</loc>')
        ->toContain('<loc>'.route('projects.show', 'miachar-water-plant').'</loc>')
        ->toContain('<loc>'.route('gallery.show', 'clean-water-plants').'</loc>')
        ->toContain('<loc>'.route('privacy').'</loc>')
        ->toContain('<loc>'.route('transparency').'</loc>')
        ->not->toContain(route('programs.show', 'qurbani'))
        ->not->toContain('phekar-youth-smoking-naswar-seminar') // draft
        ->not->toContain(route('gallery.show', 'muharram-medical-camps')) // no photos yet
        ->toContain('<loc>'.route('get-help').'</loc>')
        ->toContain('<loc>'.route('get-involved').'</loc>')
        ->not->toContain('/admin');
});

it('refreshes the sitemap when news is published', function () {
    $this->get('/sitemap.xml')->assertDontSee('phekar-youth-smoking-naswar-seminar');

    Post::where('slug', 'phekar-youth-smoking-naswar-seminar')->update(['published_at' => now()->subHour()]);
    // A query-builder update skips model events, so publish through the model as editors do.
    Post::where('slug', 'phekar-youth-smoking-naswar-seminar')->first()->touch();

    $this->get('/sitemap.xml')->assertSee(route('news.show', 'phekar-youth-smoking-naswar-seminar'));
});
