<?php

namespace App\Providers\Filament;

use App\Filament\Pages\FrontPage;
use App\Filament\Pages\PulseDashboard;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Kainiklas\FilamentScout\FilamentScoutPlugin;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id("app")
            ->spa()
            ->homeUrl(fn() => FrontPage::getUrl())
            ->login()
            ->registration()
            ->topNavigation()
            ->userMenuItems([
                "profile" => MenuItem::make()
                    ->label(fn() => auth()->user()->name)
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon("heroicon-m-user-circle"),
            ])
            ->favicon("/images/logo.svg")
            ->brandLogo("/images/logo.svg")
            ->brandLogoHeight("2.5rem")
            ->brandName("Boodschrappen")
            ->discoverResources(
                in: app_path("Filament/Resources"),
                for: "App\\Filament\\Resources"
            )
            ->discoverPages(
                in: app_path("Filament/Pages"),
                for: "App\\Filament\\Pages"
            )
            ->pages([FrontPage::class, PulseDashboard::class])
            ->discoverWidgets(
                in: app_path("Filament/Widgets"),
                for: "App\\Filament\\Widgets"
            )
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
                fn() => new HtmlString(
                    Blade::render(
                        '@guest <x-filament::button :href="filament()->getLoginUrl()" tag="a">Inloggen</x-filament::button> @endguest'
                    )
                )
            )
            ->font("sans-serif", provider: LocalFontProvider::class)
            ->plugins([
                FilamentScoutPlugin::make()->useMeilisearch(),
                FilamentEditProfilePlugin::make()
                    ->slug("profiel")
                    ->shouldRegisterNavigation(false),
            ]);
    }

    public function boot()
    {
        FilamentColor::register([
            "primary" => "#4ecdc4",
        ]);

        FilamentAsset::register([
            Css::make("custom", Vite::asset("resources/css/app.css", "build")),
        ]);
    }
}
