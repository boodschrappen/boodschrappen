<?php

namespace App\Console\Commands;

use App\Console\Crawler\AhCrawler;
use Illuminate\Console\Command;

class CrawlProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:product {productId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach ($this->crawlers as $crawler) {
            // Get all products that need an update.

            // Fetch those products.
            $product = (new $crawler)->fetchProduct();

            // Update them in the database.
        }
    }
}
