<?php

namespace App\Console\Commands;

use App\Jobs\FetchProductsInCategory;
use App\Models\Product;
use App\Services\Crawlers\AhCrawler;
use App\Services\Crawlers\Crawler;
use App\Services\Crawlers\DekamarktCrawler;
use App\Services\Crawlers\DirkCrawler;
use App\Services\Crawlers\JumboCrawler;
use App\Services\Crawlers\VomarCrawler;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrawlCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:categories {storeSlug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawl products in categories of the given stores.';

    protected array $crawlers = [
        AhCrawler::class,
        DekamarktCrawler::class,
        DirkCrawler::class,
        JumboCrawler::class,
        VomarCrawler::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($slug = $this->argument('storeSlug')) {
            $crawler = Str::of($slug)->title()->prepend('App\Services\Crawlers\\')->append('Crawler')->toString();
            return $this->crawl(app($crawler));
        }

        foreach ($this->crawlers as $crawler) {
            $this->crawl(app($crawler));
        }
    }

    protected function crawl(Crawler $crawler): void
    {
        Context::add('crawler', $crawler);

        $store = $crawler->getStore();

        $this->info("Waiting for store $store->name.");

        try {
            $categories = $crawler->fetchCategories();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            $this->error("Initializing jobs for store $store->name failed.");

            return;
        }

        Bus::batch(
            $categories->map(
                fn(mixed $category) => new FetchProductsInCategory($category)
            )
        )->finally(function (Batch $batch) {
            Product::query()->searchable();

            // TODO: (Soft) Delete outdated products
            // ProductStore::query()
            //     ->where('store_id', $store->id)
            //     ->whereIn('product_id', )
            //     ->delete();
        })->name($store->name)->dispatch();

        $this->info("Crawling for store $store->name has started.");
    }
}
