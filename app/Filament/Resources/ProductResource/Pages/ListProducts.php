<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Kainiklas\FilamentScout\Traits\InteractsWithScout;

class ListProducts extends ListRecords
{
    use InteractsWithScout;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        if (filled($search = $this->getTableSearch())) {
            $keys = static::$resource::getModel()::search(
                $search,
                function ($meiliSearch, string $search, array $options) {
                    $options['attributesToHighlight'] = ['*'];
                    $options['highlightPreTag'] = '<strong>';
                    $options['highlightPostTag'] = '</strong>';
                    $options['limit'] = 1000;

                    return $meiliSearch->search($search, $options);
                }
            )
                ->keys()
                ->join(',');

            $query->join(
                // Since we know for certain the keys are only ints and came from a trusted source, we can perform this dangerous action.
                // Normally this could result in an SQL injection.
                DB::raw('unnest(array[' . $keys . ']) with ordinality as l(id, idx)'),
                'products.id',
                '=',
                'l.id'
            )
                ->orderBy('l.idx');
        }

        return $query;
    }
}
