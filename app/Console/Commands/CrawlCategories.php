<?php

namespace App\Console\Commands;

use App\Jobs\FetchProduct;
use App\Models\ProductStore;
use App\Services\Crawlers\AhCrawler;
use App\Services\Crawlers\Crawler;
use App\Services\Crawlers\VomarCrawler;
use Illuminate\Console\Command;
use Illuminate\Queue\Queue;

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
        // VomarCrawler::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach ($this->crawlers as $crawler) {
            $this->crawl(app($crawler));
        }
    }

    protected function crawl(Crawler $crawler)
    {
        $products = $crawler->fetchAllProducts();
        $store = $crawler->getStore();

        // Find existing products. These will already be attached to the store.
        $existingIds = $store->storeProducts()->pluck('raw_identifier');

        // Insert new products. Updating products is done in a separate process.
        $newProducts = $products
            // Find out which products need to be added.
            ->where(fn($product) => ! $existingIds->contains(
                fn($existingId) => array_intersect(
                    $existingId,
                    $product->toStoreProduct()->raw_identifier
                )
            ))
            ->mapWithKeys(function ($product) {
                $savedProduct = $product->toProduct();
                $savedProduct->save();

                return [$savedProduct->id => $product->toStoreProduct()->toArray()];
            });

        $store->products()->attach($newProducts);

        // TODO: (Soft) Delete outdated products
        // ProductStore::query()
        //     ->where('store_id', $store->id)
        //     ->whereIn('product_id', )
        //     ->delete();

        // Push new products to the queue to fetch full product details.
        foreach ($newProducts as $newProduct) {
            FetchProduct::dispatch(new ProductStore($newProduct));
        }
    }
}
