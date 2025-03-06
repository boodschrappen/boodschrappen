<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;

class VomarProductData extends Data implements ProductData
{
    public function __construct(
        public int      $articleNumber,
        public string   $description,
        public string   $detailedDescription,
        public int      $departmentNumberWebsite,
        public int      $mainGroupNumberWebsite,
        public int      $subGroupNumberWebsite,
        public string   $eanPrimary,
        public array    $eanSynonyms,
        public float    $price,
        public string   $priceDefaultAmount,
        public array    $images,
        public string   $brand,
        public int      $relevancy,
        public bool     $discountDeal,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => json_encode(array_merge([$this->eanPrimary], $this->eanSynonyms)),
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
}
