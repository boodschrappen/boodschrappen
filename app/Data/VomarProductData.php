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
        public int              $articleNumber,
        public string           $description,
        public string           $detailedDescription,
        public int|Optional     $departmentNumberWebsite,
        public int|Optional     $mainGroupNumberWebsite,
        public int|Optional     $subGroupNumberWebsite,
        public string|Optional  $eanPrimary,
        public array|Optional   $eanSynonyms,
        public float            $price,
        public string|Optional  $priceDefaultAmount,
        public array|Optional   $images,
        public string|Optional  $brand,
        public int|Optional     $relevancy,
        public bool|Optional    $discountDeal,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => json_encode(array_merge([$this->eanPrimary], Arr::wrap($this->eanSynonyms))),
            'name'        => $this->description,
            'summary'     => '',
            'description' => $this->detailedDescription,
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
        return self::from([
            'articleNumber' => $storeProduct->raw_identifier,
            'description' => $storeProduct->product->name,
            'detailedDescription' => $storeProduct->product->description,
            'price' => $storeProduct->original_price,
        ]);
    }
}
