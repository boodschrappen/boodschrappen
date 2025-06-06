<?php

namespace App\Console\Commands;

use App\Jobs\FetchProduct;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use App\Services\Crawlers\AhCrawler;
use App\Services\Crawlers\Crawler;
use App\Services\Crawlers\DekamarktCrawler;
use App\Services\Crawlers\DirkCrawler;
use App\Services\Crawlers\JumboCrawler;
use App\Services\Crawlers\VomarCrawler;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:categories {storeSlug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl products in categories of the given stores.';

    protected array $crawlers = [
        AhCrawler::class,
        DekamarktCrawler::class,
        DirkCrawler::class,
        JumboCrawler::class,
        VomarCrawler::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($slug = $this->argument('storeSlug')) {
            $crawler = Str::of($slug)->title()->prepend('App\Services\Crawlers\\')->append('Crawler')->toString();
            return $this->crawl(app($crawler));
        }

        foreach ($this->crawlers as $crawler) {
            $this->crawl(app($crawler));
        }
    }

    protected function crawl(Crawler $crawler): void
    {
        $store = $crawler->getStore();

        $crawler->fetchCategories()->each(
            fn(mixed $category) => $this->processProducts(
                $crawler,
                $store,
                $crawler->fetchProductsByCategory($category)
            )
        );

        // TODO: (Soft) Delete outdated products
        // ProductStore::query()
        //     ->where('store_id', $store->id)
        //     ->whereIn('product_id', )
        //     ->delete();
    }

    protected function processProducts(Crawler $crawler, Store $store, Collection $products)
    {
        // Find existing products. These will already be attached to the store.
        $existingIds = $store->storeProducts()->pluck('raw_identifier');

        // Insert new products. Updating products is done in a separate process.
        $newProducts = $products
            // Find out which products need to be added.
            ->where(fn($product) => ! $existingIds->contains($product->toStoreProduct()->raw_identifier))
            ->mapWithKeys(function ($product) {
                $savedProduct = $product->toProduct();

                // We don't want to fill up the queue with individual product indexes.
                Product::withoutSyncingToSearch(fn() => $savedProduct->save());

                return [$savedProduct->id => $product->toStoreProduct()->getAttributes()];
            });

        $store->products()->attach($newProducts);

        // Load the actual new store products.
        $newStoreProducts = ProductStore::whereIn('product_id', $newProducts->keys())->get();

        // Push new products to the queue to fetch full product details.
        foreach ($newStoreProducts as $newStoreProduct) {
            FetchProduct::dispatch($newStoreProduct);
        }

        Log::debug('New products: ' . json_encode($newProducts->keys()->toArray()));
    }
}
