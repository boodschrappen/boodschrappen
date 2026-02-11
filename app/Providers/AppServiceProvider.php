<?php

namespace App\Providers;

use App\Services\Crawlers\AhCrawler;
use App\Services\Crawlers\DekamarktCrawler;
use App\Services\Crawlers\DirkCrawler;
use App\Services\Crawlers\JumboCrawler;
use App\Services\Crawlers\VomarCrawler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerCrawlers();

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected function registerCrawlers(): void
    {
        $crawlers = [
            AhCrawler::class,
            DekamarktCrawler::class,
            DirkCrawler::class,
            JumboCrawler::class,
            VomarCrawler::class
        ];

        foreach ($crawlers as $crawler) {
            $this->app->singleton($crawler, $crawler);
        }
    }
}
