<?php

namespace App\Contracts;

use App\Data\Nutrients\AllergensData;
use App\Data\Nutrients\NutrientsData;
use App\Data\Discounts\DiscountData;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

interface ProductData
{
    public static function fromModel(ProductStore $storeProduct): Data;

    public function toProduct(): Product;

    public function toStoreProduct(): ProductStore;

    public function nutrients(): NutrientsData|null;

    public function ingredients(): string|null;

    public function allergens(): AllergensData|null;

    /**
     * @return Collection<Discount>
     */
    public function discount(): DiscountData|null;
}
