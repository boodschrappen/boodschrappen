<?php

namespace App\Services;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Database\Eloquent\Collection;

class ProductMerger
{
    private Collection $productInOtherStores;

    private const array WEIGHTS = ["ah", "jumbo", "vomar", "dekamarkt", "dirk"];
    private ProductStore $storeProduct;
    private Product $product;

    public function __construct(private ProductData $productData)
    {
        $this->storeProduct = $this->productData->toStoreProduct();
        $this->product = $this->productData->toProduct();
    }

    public function merge(): Product
    {
        $this->productInOtherStores = ProductStore::query()
            ->whereProductId($this->storeProduct->product_id)
            ->with("product", "store")
            ->get()
            ->groupBy("store.slug");

        $mergedProduct = new Product([
            "name" => $this->getWeightedProperty("name"),
            "description" => $this->getWeightedProperty("description"),
            "gtins" => $this->getWeightedProperty("gtins"),
            "nutrients" => $this->getWeightedProperty("nutrients"),
            "allergens" => $this->getWeightedProperty("allergens"),
        ]);

        return $mergedProduct;
    }

    protected function getWeightedProperty(string $property)
    {
        if ($this->productInOtherStores->count() === 1) {
            return $this->product->{$property};
        }

        foreach (self::WEIGHTS as $weight) {
            if ($storeProduct = $this->productInOtherStores->get($weight)) {
                return $storeProduct->product->{$property};
            }
        }
    }
}
