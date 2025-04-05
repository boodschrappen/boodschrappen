<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Console\Command;

class MergeProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge products based on gtins. This can be greatly improved, but I don\'t care.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);

        $original = Product::pluck('gtins', 'id');

        $this->line("Mapping {$original->count()} products");

        $bar = $this->output->createProgressBar($original->count());
        $bar->start();

        $reduced = $original->reduce(function ($result, $gtins, $id) use ($bar) {
            $bar->advance();

            $gtins = is_array($gtins) ? $gtins : json_decode($gtins, true);

            foreach ($result['reduced'] as $destinationId => $item) {
                if (array_intersect($item, $gtins)) {
                    $result['reduced'][$destinationId] = array_unique(array_merge($item, $gtins));
                    $result['move'][$id] = $destinationId;
                    return $result;
                }
            }

            if (count($gtins) > 0) {
                $result['reduced'][$id] = $gtins;
            }

            return $result;
        }, ['reduced' => [], 'move' => []]);

        $bar->finish();

        $this->newLine(2);
        $this->line('Updating gtins for ' . count($reduced['reduced']) . ' products');
        $this->withProgressBar($reduced['reduced'], function ($gtins, $_, $id) {
            Product::withoutSyncingToSearch(fn() => Product::whereId($id)->update(['gtins' => $gtins]));
        });

        $this->newLine(2);
        $this->line('Merging ' . count($reduced['move']) . ' products');
        $this->withProgressBar($reduced['move'], function ($destination, $_, $id) {
            Product::withoutSyncingToSearch(fn() => ProductStore::where('product_id', $id)->update(['product_id' => $destination]));
        });

        Product::whereIn('id', array_keys($reduced['move']))->delete();

        Product::query()->searchable();

        $this->newLine(2);
        $this->line('It took ' . (microtime(true) - $startTime) . ' seconds to complete.');
    }
}
