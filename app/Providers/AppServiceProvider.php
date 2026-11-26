<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Donation;
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

        // Contact details and impact numbers for the public layout, edited in Admin → Settings.
        View::composer(['components.site.*', 'components.layouts.*', 'pages.*', 'donate.*', 'livewire.*', 'about.*', 'contact', 'errors.*'], function ($view) {
            $view->with('site', app(SiteSettings::class));
        });
    }
}
