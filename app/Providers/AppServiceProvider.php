<?php

namespace App\Providers;

use App\Services\Crawlers\AhCrawler;
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
        $crawlers = [AhCrawler::class, VomarCrawler::class];

        foreach ($crawlers as $crawler) {
            $this->app->singleton($crawler, $crawler);
        }
    }
}
