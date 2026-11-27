<?php

namespace App\Providers;

use App\Enums\Role;
use App\Http\Controllers\SeoController;
use App\Models\Donation;
use App\Models\GalleryAlbum;
use App\Models\Page;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Settings\SiteSettings;
use App\Support\ActivityRecorder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin can do everything (plan 1.2, roles), except create or delete
        // donation records, which DonationPolicy refuses for everyone.
        Gate::before(function ($user, string $ability, array $arguments) {
            $target = $arguments[0] ?? null;
            $isDonation = $target instanceof Donation || $target === Donation::class;

            if ($isDonation && ! in_array($ability, ['viewAny', 'view', 'update'], true)) {
                return null;
            }

            return $user->hasRole(Role::SuperAdmin->value) ? true : null;
        });

        // Sign-ins, role changes and settings changes for Admin → Users → Activity log.
        ActivityRecorder::register();

        // Rebuild the sitemap whenever something in it changes.
        foreach ([Program::class, Project::class, Post::class, GalleryAlbum::class, Page::class, Media::class] as $model) {
            $model::saved(fn () => Cache::forget(SeoController::SITEMAP_CACHE_KEY));
            $model::deleted(fn () => Cache::forget(SeoController::SITEMAP_CACHE_KEY));
        }

        // Contact details and impact numbers for the public layout, edited in Admin → Settings.
        View::composer(['components.site.*', 'components.layouts.*', 'pages.*', 'donate.*', 'livewire.*', 'about.*', 'contact', 'errors.*'], function ($view) {
            $view->with('site', app(SiteSettings::class));
        });
    }
}
