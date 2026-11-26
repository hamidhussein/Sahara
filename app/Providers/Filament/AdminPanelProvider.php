<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            // Profile page: where staff set up two-factor login and change their password.
            ->profile(isSimple: false)
            // Two-factor login for admins (plan 1.2). Required on the live site, so
            // every admin sets it up at their next sign-in before doing anything else.
            ->multiFactorAuthentication(
                [
                    AppAuthentication::make()->recoverable()->brandName('SSWF Admin'),
                    EmailAuthentication::make(),
                ],
                isRequired: fn () => (bool) config('sswf.admin_two_factor_required'),
            )
            ->passwordReset()
            ->brandName('SSWF Admin')
            ->favicon(asset('favicon.svg'))
            ->colors([
                'primary' => Color::hex('#1e6b3a'),
                'warning' => Color::hex('#e8a317'),
                'info' => Color::hex('#2a7fb8'),
            ])
            ->font('Inter')
            // Menu groups from plan 1.3; each group shows only what the user's role allows.
            ->navigationGroups([
                NavigationGroup::make('Content'),
                NavigationGroup::make('Donations'),
                NavigationGroup::make('Transparency'),
                NavigationGroup::make('Settings'),
                NavigationGroup::make('Users'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
