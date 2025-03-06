<?php

use App\Data\AhProductData;
use App\Models\Product;
use App\Models\Store;
use App\Services\Crawlers\AhCrawler;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;

test('fetch all products', function () {
    $this->mock(AhCrawler::class, function (MockInterface $mock) {
        $mock->shouldReceive('fetchAllProducts')
            ->andReturn(collect([AhProductData::from([
                'webshopId' => 4177,
                'title' => 'Ah Broccoli',
                'descriptionHighlights' => 'Broccoli heeft een knapperige, frisse iets pittigere smaak dan bloemkool.',
                'priceBeforeBonus' => 1.45
            ])]));

        $mock->shouldReceive('getStore')
            ->andReturn(Store::factory(['slug' => 'ah'])->create());
    });

    Queue::fake();

    $this->artisan('crawl:categories')->assertExitCode(0);

    $storeProduct = Product::orderBy('id', 'desc')
        ->first()
        ->stores()
        ->withPivot('raw_identifier')
        ->first()
        ->pivot;

    expect($storeProduct->raw_identifier)->toBe('4177');
    expect($storeProduct->original_price)->toBe(1.45);
});
