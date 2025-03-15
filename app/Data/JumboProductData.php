<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class JumboProductData extends Data implements ProductData
{
    public function __construct(
        public string                $id,
        public string                $title,
        public Optional|null|array   $price = null,
        public Optional|null|array   $quantityOptions = null,
        public Optional|null|bool    $available = null,
        public Optional|null|string  $productType = null,
        public Optional|null|array   $crossSellSKUList = null,
        public Optional|null|bool    $nixProduct = null,
        public Optional|null|array   $imageInfo = null,
        public Optional|null|string  $unavailabilityReason = null,
        public Optional|null|array   $badgesToDisplay = null,
        public Optional|null|bool    $sample = null,
        public Optional|null|array   $availability = null,
        public Optional|null|array   $allergens = null,
        public Optional|null|array   $surcharges = null,
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

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::from([
            'id' => $storeProduct->raw_identifier,
            'title' => $storeProduct->product->name,
            'price' => $storeProduct->original_price,
        ]);
    }
}
