<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;
use Filament\Support\Enums\Size;
use Dotswan\FilamentLaravelPulse\Widgets\PulseCache;
use Dotswan\FilamentLaravelPulse\Widgets\PulseExceptions;
use Dotswan\FilamentLaravelPulse\Widgets\PulseQueues;
use Dotswan\FilamentLaravelPulse\Widgets\PulseServers;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowOutGoingRequests;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowQueries;
use Dotswan\FilamentLaravelPulse\Widgets\PulseSlowRequests;
use Dotswan\FilamentLaravelPulse\Widgets\PulseUsage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class PulseDashboard extends Dashboard
{
    use HasFiltersAction;

    protected static ?string $title = "Overzicht";

    protected static string $routePath = "/metrics";

    protected static ?int $navigationSort = 100;

    protected static string | \BackedEnum | null $navigationIcon = "heroicon-o-presentation-chart-line";

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin ?? false;
    }

    public function getColumns(): int|array
    {
        return 12;
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make("1h")->action(
                    fn() => $this->redirect(
                        route("filament.app.pages.pulse-dashboard")
                    )
                ),
                Action::make("24h")->action(
                    fn() => $this->redirect(
                        route("filament.app.pages.pulse-dashboard", [
                            "period" => "24_hours",
                        ])
                    )
                ),
                Action::make("7d")->action(
                    fn() => $this->redirect(
                        route("filament.app.pages.pulse-dashboard", [
                            "period" => "7_days",
                        ])
                    )
                ),
            ])
                ->label(__("Filter"))
                ->icon("heroicon-m-funnel")
                ->size(Size::Small)
                ->color("gray")
                ->button(),
        ];
    }

    public function getWidgets(): array
    {
        return [
            PulseServers::class,
            PulseCache::class,
            PulseExceptions::class,
            PulseUsage::class,
            PulseQueues::class,
            PulseSlowQueries::class,
            PulseSlowRequests::class,
            PulseSlowOutGoingRequests::class,
        ];
    }
}
