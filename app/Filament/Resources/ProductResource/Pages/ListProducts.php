<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
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
            CreateAction::make(),
        ];
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTableQuery($query);

        if (filled($search = $this->getTableSearch())) {
            $keys = static::$resource::getModel()::search(
                $search,
                function ($meiliSearch, string $search, array $options) {
                    $options['limit'] = 1000;

                    return $meiliSearch->search($search, $options);
                }
            )
                ->keys();

            // Check if we have any results. Not performing this check will cause the postgres query to fail.
            if ($keys->count() === 0) {
                // We need to update the query so that no results are returned.
                $query->whereRaw('1 = 0');
            } else {
                $query->join(
                    // Since we know for certain the keys are only ints and came from a trusted source, we can perform this dangerous action.
                    // Normally this could result in an SQL injection.
                    DB::raw('unnest(array[' . $keys->join(',') . ']) with ordinality as l(id, idx)'),
                    'products.id',
                    '=',
                    'l.id'
                )
                    ->orderBy('l.idx');
            }
        }

        return $query;
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate(($this->getTableRecordsPerPage() === 'all') ? $query->count() : $this->getTableRecordsPerPage());
    }
}
