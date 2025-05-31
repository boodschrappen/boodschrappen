<?php

namespace App\Filament\Resources\ShoppingListResource\Pages;

use App\Filament\Resources\ShoppingListResource;
use App\Models\Product;
use App\Models\ShoppingListItem;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListShoppingLists extends ListRecords
{
    protected static string $resource = ShoppingListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::Small)
                ->createAnother(false)
                ->using(function (array $data) {
                    $existingListItem = ShoppingListItem::firstWhere(
                        "product_store_id",
                        $data["product_store_id"]
                    );

                    if ($existingListItem) {
                        $existingListItem->amount += (int) $data["amount"];
                        $existingListItem->save();
                    } else {
                        ShoppingListItem::create([
                            "amount" => $data["amount"],
                            // TODO: Select the cheapest option. Maybe using a scope?
                            "product_store_id" => $data["product_store_id"],
                            "description" => Product::whereRelation(
                                "productStores",
                                "id",
                                $data["product_store_id"]
                            )->first()->name,
                        ]);
                    }
                }),
        ];
    }
}
