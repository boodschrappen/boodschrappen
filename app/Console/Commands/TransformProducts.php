<?php

namespace App\Console\Commands;

use App\Models\ProductStore;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TransformProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transform-products
                            {storeSlug : A store slug of which products will be re-transformed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "(re-)transform one or more products";

    protected array $stores = ["ah", "dekamarkt", "dirk", "jumbo", "vomar"];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($slug = $this->argument("storeSlug")) {
            return $this->transform($slug);
        }

        foreach ($this->stores as $slug) {
            $this->transform($slug);
        }
    }

    public function transform(string $slug)
    {
        $dataClass = Str::of($slug)
            ->title()
            ->prepend("App\Data\\")
            ->append("ProductData")
            ->toString();

        // Fetch raw values from database
        ProductStore::query()
            ->whereRelation("store", "slug", $slug)
            ->with("product")
            ->chunk(1000, function ($storeProducts) use ($dataClass) {
                $this->line(
                    "Transforming " . $storeProducts->count() . " products."
                );

                $storeProducts->each(function ($storeProduct) use ($dataClass) {
                    $rawData = $dataClass::from($storeProduct->raw);

                    // Apply transformations
                    $newProduct = $rawData->toProduct();
                    $newStoreProduct = $rawData->toStoreProduct();

                    // Persist transformations
                    $storeProduct->product
                        ->fill($newProduct->toArray())
                        ->save();
                    $storeProduct->fill($newStoreProduct->toArray())->save();
                });
            });
    }
}
