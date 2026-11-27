<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * XML sitemap and robots.txt (plan 1.4, SEO and sharing).
 */
class SeoController extends Controller
{
    /** Cleared whenever content that appears in the sitemap changes (see AppServiceProvider). */
    public const SITEMAP_CACHE_KEY = 'seo.sitemap';

    public function sitemap(): Response
    {
        $xml = Cache::remember(self::SITEMAP_CACHE_KEY, now()->addHours(6), fn () => $this->buildSitemap()->render());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    protected function buildSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();
        $add = fn (string $url, ?\DateTimeInterface $modified = null, float $priority = 0.6, string $frequency = Url::CHANGE_FREQUENCY_MONTHLY) => $sitemap->add(
            Url::create($url)
                ->setPriority($priority)
                ->setChangeFrequency($frequency)
                ->setLastModificationDate($modified ?? now()->startOfDay())
        );

        // Fixed pages.
        $add(route('home'), null, 1.0, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('donate'), null, 0.9);
        foreach (['about', 'leadership', 'transparency', 'programs.index', 'get-help', 'get-involved', 'partner', 'contact', 'donate.zakat-calculator'] as $name) {
            $add(route($name), null, 0.7);
        }
        $add(route('projects.index'), null, 0.8, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('news.index'), null, 0.8, Url::CHANGE_FREQUENCY_WEEKLY);
        $add(route('gallery'), null, 0.5);

        Program::published()->get(['slug', 'updated_at'])
            ->each(fn ($p) => $add(route('programs.show', $p), $p->updated_at, 0.8));

        Project::published()->whereHas('program', fn ($q) => $q->published())->get(['slug', 'updated_at'])
            ->each(fn ($p) => $add(route('projects.show', $p), $p->updated_at, 0.7));

        Post::published()->latest('published_at')->get(['slug', 'updated_at'])
            ->each(fn ($p) => $add(route('news.show', $p), $p->updated_at, 0.6, Url::CHANGE_FREQUENCY_YEARLY));

        GalleryAlbum::published()->with('media')->get()->filter->hasContent()
            ->each(fn ($a) => $add(route('gallery.show', $a), $a->updated_at, 0.4));

        Page::where('is_published', true)->get(['slug', 'updated_at'])
            ->each(fn ($p) => $add(url('/'.$p->slug), $p->updated_at, 0.3, Url::CHANGE_FREQUENCY_YEARLY));

        return $sitemap;
    }

    /**
     * Only the live site invites search engines; staging and local copies ask them to stay away.
     */
    public function robots(): Response
    {
        $lines = config('app.env') === 'production'
            ? [
                'User-agent: *',
                'Disallow: /admin',
                'Disallow: /finance/',
                'Disallow: /payments/',
                'Disallow: /donate/thank-you/',
                'Disallow: /donate/payment-failed/',
                'Disallow: /receipts/',
                'Disallow: /livewire/',
                '',
                'Sitemap: '.route('sitemap'),
            ]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
