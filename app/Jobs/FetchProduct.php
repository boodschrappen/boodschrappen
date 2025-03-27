<?php

namespace App\Jobs;

use App\Models\ProductStore;
use App\Services\Crawlers\Crawler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchProduct implements ShouldQueue
{
    use Queueable;

    private Crawler $crawler;

    /**
     * Create a new job instance.
     */
    public function __construct(string $crawler, private ProductStore $storeProduct)
    {
        $this->crawler = app($crawler);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $productData = $this->crawler->fetchProduct($this->storeProduct->raw_identifier);

        $mergedProductData = $this->crawler->formatProduct(array_merge(
            json_decode($this->storeProduct->raw, true),
            (array) $productData
        ));

        // Persist transformations
        $this->storeProduct->product->fill($mergedProductData->toProduct()->toArray())->save();
        $this->storeProduct->fill($mergedProductData->toStoreProduct()->toArray())->save();
    }
}
