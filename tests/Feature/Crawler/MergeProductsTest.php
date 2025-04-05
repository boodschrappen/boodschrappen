<?php

use App\Models\Product;
use App\Models\ProductStore;

test('merge products based on gtin', function () {
    // Arrange
    $numberOfProductsWithIdenticalGtins = 3;
    $numberOfProductsWithoutGtins = 2;

    // Create a product with the same gtin in 3 different stores.
    $productStores = ProductStore::factory($numberOfProductsWithIdenticalGtins)
        ->state([
            'product_id' => Product::factory()->state([
                'gtins' => [fake()->randomNumber(8)]
            ]),
        ])
        ->create();

    $productStores->first()->product->gtins = [...$productStores->first()->product->gtins, fake()->randomNumber(8)];
    $productStores->first()->product->save();

    ProductStore::factory($numberOfProductsWithoutGtins)
        ->state(['product_id' => Product::factory()->state(['gtins' => []])])
        ->create();


    // Act
    $this->artisan('app:merge-products')->assertSuccessful();

    // Assert
    expect(Product::withCount('stores')->first()->stores_count)->toBe($numberOfProductsWithIdenticalGtins);
    expect(Product::count())->toBe(1 + $numberOfProductsWithoutGtins);
});
