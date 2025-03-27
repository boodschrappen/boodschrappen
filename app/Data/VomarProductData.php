<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class VomarProductData extends Data implements ProductData
{
    public function __construct(
        public int                   $articleNumber,
        public float                 $price,
        public string                $description = '',
        public Optional|null|string  $detailedDescription = '',
        public Optional|null|int     $departmentNumberWebsite = null,
        public Optional|null|int     $mainGroupNumberWebsite = null,
        public Optional|null|int     $subGroupNumberWebsite = null,
        public Optional|null|string  $eanPrimary = null,
        public Optional|null|array   $eanSynonyms = null,
        public Optional|null|string  $priceDefaultAmount = null,
        public Optional|null|array   $images = null,
        public Optional|null|string  $brand = null,
        public Optional|null|int     $relevancy = null,
        public Optional|null|bool    $discountDeal = null,

        // Only available on details page.
        public Optional|null|bool    $nutritions = null,
        public Optional|null|bool    $ingredients = null,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => json_encode(array_merge([$this->eanPrimary], Arr::wrap($this->eanSynonyms))),
            'name'        => $this->detailedDescription,
            'summary'     => '',
            'description' => $this->description,
            'image'       => $this->images ? 'https://d3vricquk1sjgf.cloudfront.net/articles/' . $this->images[0]['imageUrl'] : null
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            'raw_identifier' => $this->articleNumber,
            'reduced_price' => null,
            'original_price' => $this->price,
            'raw' => json_encode($this->toArray()),
        ]);
    }

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::factory()->withoutOptionalValues()->from([
            'articleNumber' => $storeProduct->raw_identifier,
            'description' => $storeProduct->product->name,
            'detailedDescription' => $storeProduct->product->description,
            'price' => $storeProduct->original_price,
        ]);
    }
}
