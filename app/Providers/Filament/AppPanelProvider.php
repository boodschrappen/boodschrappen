<?php

namespace App\Providers\Filament;

use App\Filament\Pages\PulseDashboard;
use App\Filament\Resources\ProductResource;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->spa()
            ->homeUrl(fn() => ProductResource::getUrl())
            ->login()
            ->topNavigation()
            ->favicon('/images/logo.svg')
            ->brandLogo('/images/logo.svg')
            ->brandLogoHeight('2.5rem')
            ->brandName('Boodschrappen')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                PulseDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn() => new HtmlString(Blade::render('<x-filament::button :href="filament()->getLoginUrl()" tag="a">Inloggen</x-filament::button>'))
            );
    }

    public function boot()
    {
        FilamentColor::register([
            'primary' => '#4ecdc4',
            'success' => Color::Emerald,
            'warning' => Color::Orange,
            'danger' => Color::Rose,
            'gray' => Color::Gray,
            'info' => Color::Blue,
        ]);
    }
}
