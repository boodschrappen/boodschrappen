<?php

namespace App\Jobs;

use App\Models\ProductStore;
use App\Services\Crawlers\Crawler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchProduct implements ShouldQueue
{
    use Queueable;

    private Crawler $crawler;

    /**
     * Create a new job instance.
     */
    public function __construct(string $crawler, private ProductStore $product)
    {
        $this->crawler = app($crawler);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug($this->crawler::class);
    }
}
