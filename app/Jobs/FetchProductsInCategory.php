<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use App\Services\Crawlers\Crawler;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class FetchProductsInCategory implements ShouldQueue
{
    use Batchable, Queueable;

    private Crawler $crawler;

    private Store $store;

    /**
     * Create a new job instance.
     */
    public function __construct(private mixed $category)
    {
        $this->crawler = Context::get('crawler');
        $this->store = $this->crawler->getStore();
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $newProducts = $this->fetchNewProducts();

        $this->store->products()->attach($newProducts);

        // Push new products to the queue to fetch full product details.
        ProductStore::query()
            ->whereIn('product_id', $newProducts->keys())
            ->where('store_id', $this->store->id)
            ->chunk(1000, function ($storeProducts) {
                $this->batch()->add($storeProducts->map(
                    fn($newStoreProduct) => new FetchProduct($newStoreProduct)
                ));
            });


        Log::debug("New products for {$this->store->name}: " . json_encode($newProducts->keys()->toArray()));
    }

    private function fetchNewProducts()
    {
        // Find existing products. These will already be attached to the store.
        $existingIds = $this->store->storeProducts()->pluck('raw_identifier');

        // Insert new products. Updating products is done in a separate process.
        return $this->crawler
            ->fetchProductsByCategory($this->category)
            // Find out which products need to be added.
            ->where(fn($product) => ! $existingIds->contains($product->toStoreProduct()->raw_identifier))
            ->mapWithKeys(function ($product) {
                $savedProduct = $product->toProduct();

                // Prevent filling up the queue with individual product indexes prematurely.
                Product::withoutSyncingToSearch(fn() => $savedProduct->save());

                return [$savedProduct->id => $product->toStoreProduct()->getAttributes()];
            });
    }
}
