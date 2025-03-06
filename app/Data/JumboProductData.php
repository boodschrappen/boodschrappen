<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;

class JumboProductData extends Data implements ProductData
{
    public function __construct(
        public string   $id,
        public string   $title,
        public array    $quantityOptions,
        public bool     $available,
        public string   $productType,
        public array    $crossSellSKUList,
        public bool     $nixProduct,
        public array    $imageInfo,
        public string   $unavailabilityReason,
        public array    $price,
        public array    $badgesToDisplay,
        public bool     $sample,
        public array    $availability,
        public array    $allergens,
        public array    $surcharges,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => "[]",
            'name'        => $this->title,
            'summary'     => '',
            'description' => '',
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            'raw_identifier' => $this->id,
            'reduced_price' => null,
            'original_price' => $this->price,
            'raw' => json_encode($this->toArray()),
        ]);
    }
}
