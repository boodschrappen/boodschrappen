<?php

namespace App\Jobs;

use App\Models\ProductStore;
use App\Services\Crawlers\Crawler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class FetchProduct implements ShouldQueue
{
    use Queueable;

    private Crawler $crawler;

    /**
     * Create a new job instance.
     */
    public function __construct(private ProductStore $storeProduct)
    {
        $crawlerClassName = Str::of($storeProduct->store->slug)->title()->prepend('App\Services\Crawlers\\')->append('Crawler')->toString();

        $this->crawler = app($crawlerClassName);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productData = $this->crawler->fetchProduct($this->storeProduct->raw_identifier);
        $mergedProductData = $this->crawler->formatProduct(array_merge(
            is_string($this->storeProduct->raw) ? json_decode($this->storeProduct->raw, true) : $this->storeProduct->raw,
            (array) $productData
        ));

        // Persist transformations
        $this->storeProduct->product->fill($mergedProductData->toProduct()->toArray())->save();
        $this->storeProduct->fill($mergedProductData->toStoreProduct()->toArray())->save();
    }
}
