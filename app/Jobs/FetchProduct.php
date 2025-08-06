<?php

namespace App\Jobs;

use App\Contracts\ProductData;
use App\Models\ProductStore;
use App\Services\Crawlers\Crawler;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Support\Facades\Context;
use Spatie\Activitylog\Facades\CauserResolver;

class FetchProduct implements ShouldQueue
{
    use Batchable, Queueable;

    private Crawler $crawler;

    /**
     * Create a new job instance.
     */
    public function __construct(private ProductStore $storeProduct) {}

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
        $this->crawler = app(Context::get('crawler'));

        CauserResolver::setCauser($this->crawler->getStore());

        // Transform store data to a normalized format.
        $productData = $this->crawler->fetchProduct(
            $this->storeProduct->raw_identifier
        );
        $mergedProductData = $this->crawler->formatProduct(
            array_merge(
                is_string($this->storeProduct->raw)
                    ? json_decode($this->storeProduct->raw, true)
                    : $this->storeProduct->raw,
                (array) $productData
            )
        );

        // Persist transformations.
        $this->storeProduct->product
            ->fill($mergedProductData->toProduct()->toArray())
            ->save();
        $this->storeProduct
            ->fill($mergedProductData->toStoreProduct()->toArray())
            ->save();

        // Store any discounts for the product.
        $this->processDiscounts($mergedProductData);
    }

    protected function processDiscounts(ProductData $productData)
    {
        // In practice, a store only assigns a single discount to a product.
        // This can be updated if there are cases in which multiple discounts are available.
        if ($discountData = $productData->discount()) {
            $discountExists = $this->storeProduct
                ->discounts()
                ->where([
                    ["start", $discountData->start],
                    ["end", $discountData->end],
                ])
                ->exists();

            if (!$discountExists) {
                $discount = $this->storeProduct->discounts()->create([
                    "product_store_id" => $this->storeProduct->id,
                    "start" => $discountData->start,
                    "end" => $discountData->end,
                ]);

                $discount
                    ->tiers()
                    ->createMany(
                        $discountData->tiers->map(fn($tier) => $tier->all())
                    );
            }
        } else {
            $this->storeProduct->discounts()->delete();
        }
    }
}
