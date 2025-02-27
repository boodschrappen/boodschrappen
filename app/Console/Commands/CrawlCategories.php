<?php

namespace App\Console\Commands;

use App\Services\Crawlers\AhCrawler;
use App\Services\Crawlers\Crawler;
use App\Services\Crawlers\VomarCrawler;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Console\Command;

class CrawlCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected array $crawlers = [
        AhCrawler::class,
        VomarCrawler::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach ($this->crawlers as $crawler) {
            $crawler = app($crawler);

            $products = $crawler->fetchAllProducts();

            // Existing products
            // Find existing products. These will already be attached to the store.
            $existingProducts = $crawler->getStore()->products()->pluck('gtins');

            // Find out which products need to be added.
            $newProducts = $crawler->getStore()->products()->create(
                $products
                    ->where(fn($product) => ! $existingProducts->contains(
                        fn($existingProduct) => array_intersect($existingProduct->gtins, $product->gtins)
                    ))
                    ->only('gtins', 'name', 'summary', 'description')
            );

            // Push new products to the queue.
        }
    }
}
