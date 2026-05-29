<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\LatestDemoRequests;
use App\Filament\Widgets\LlmSpendOverview;
use App\Filament\Widgets\MatchActivityChart;
use App\Filament\Widgets\MatchQuality;
use App\Filament\Widgets\PlatformOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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
            // Panel id stays "admin" so generated route names (eg
            // filament.admin.resources.users.index) and the Filament cache
            // keys don't shift across the rename. Only the public URL
            // changes — /admin → /manage — to match the operator brand.
            ->id('admin')
            ->path('manage')
            ->login()
            ->brandName('PrComet · manage')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                PlatformOverview::class,
                LlmSpendOverview::class,
                MatchQuality::class,
                MatchActivityChart::class,
                LatestDemoRequests::class,
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
            ])
            // Inject the site analytics tag into the panel's <head>.
            // Renders the same <x-tracking /> component the public
            // surfaces use, so env-based skipping (local/testing) is
            // shared across the whole app.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('<x-tracking />'),
            );
    }
}
