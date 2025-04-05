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

        // Only available on details page.
        public Optional|null|array   $prices = null,
        public Optional|null|array   $promotion = null,
        public Optional|null|array   $ingredientInfo = null,
        public Optional|null|array   $nutritionalInformation = null,
    ) {}

    public function toProduct(): Product
    {
        $image = $this->imageInfo ? $this->imageInfo['primaryView'][0]['url'] : null;
        $result = preg_match('/(\d+)_[^_]+\.png/i', $image, $gtins);

        return new Product([
            'gtins'       => $result > 0 ? [$gtins[1]] : [],
            'name'        => $this->title,
            'summary'     => '',
            'description' => '',
            'image'       => $image,
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            'raw_identifier' => $this->id,
            'reduced_price' => null,
            'original_price' => $this->price,
            'raw' => $this->toArray(),
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
