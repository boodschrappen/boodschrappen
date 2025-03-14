<?php

use App\Jobs\FetchProduct;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery\MockInterface;

test('fetch all products', function (string $slug) {
    // Arrange

    $crawler = Str::of($slug)->title()->prepend('App\Services\Crawlers\\')->append('Crawler')->toString();
    $dataModel = Str::of($slug)->title()->prepend('App\Data\\')->append('ProductData')->toString();

    Queue::fake();

    $store = Store::factory(['slug' => $slug])->create();

    $productData = $dataModel::fromModel(ProductStore::factory([
        'store_id' => $store->id,
    ])->make());

    $this->mock($crawler, function (MockInterface $mock) use ($store, $productData) {
        $mock->shouldReceive('fetchAllProducts')
            ->andReturn(collect([$productData]));

        $mock->shouldReceive('getStore')
            ->andReturn($store);
    });

    // Act

    $this->artisan('crawl:categories', ['storeSlug' => $slug])->assertExitCode(0);

    // Assert

    $storeProduct = Product::orderBy('id', 'desc')
        ->first()
        ->stores()
        ->withPivot('raw_identifier')
        ->first()
        ->pivot;

    expect($storeProduct->raw_identifier)->toBe(str($productData->toStoreProduct()->raw_identifier)->toString());
    expect((int) $storeProduct->original_price)->toBe((int) $productData->toStoreProduct()->original_price);

    Queue::assertPushed(FetchProduct::class);
})
    ->with(['ah', 'vomar']);
