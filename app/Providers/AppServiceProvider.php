<?php

namespace App\Providers;

use App\Enums\Role;
use App\Settings\ImpactSettings;
use App\Settings\SiteSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Super Admin can do everything (plan 1.2, roles).
        Gate::before(fn ($user) => $user->hasRole(Role::SuperAdmin->value) ? true : null);

        // Contact details and impact numbers for the public layout, edited in Admin → Settings.
        View::composer(['components.site.*', 'components.layouts.*', 'pages.*'], function ($view) {
            $view->with('site', app(SiteSettings::class));
        });
        View::composer('pages.home', function ($view) {
            $view->with('impact', app(ImpactSettings::class));
        });
    }
}
