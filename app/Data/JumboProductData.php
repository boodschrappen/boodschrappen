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
        public string           $id,
        public string           $title,
        public array|Optional   $quantityOptions,
        public bool|Optional    $available,
        public string|Optional  $productType,
        public array|Optional   $crossSellSKUList,
        public bool|Optional    $nixProduct,
        public array|Optional   $imageInfo,
        public string|Optional  $unavailabilityReason,
        public array            $price,
        public array|Optional   $badgesToDisplay,
        public bool|Optional    $sample,
        public array|Optional   $availability,
        public array|Optional   $allergens,
        public array|Optional   $surcharges,
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
