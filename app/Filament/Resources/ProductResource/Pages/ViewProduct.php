<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\ShoppingListItem;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected ?string $heading = " ";

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make("add_to_list")
                ->label("Voeg toe aan je lijstje")
                ->badge(
                    fn($record) => ShoppingListItem::firstWhere(
                        "product_store_id",
                        $record->productStores()->first()->id
                    )?->amount
                )
                ->badgeColor("info")
                ->icon("heroicon-o-shopping-cart")
                ->action(function (Product $record) {
                    // TODO: Put in reuable method.
                    $existingListItem = ShoppingListItem::firstWhere(
                        "product_store_id",
                        $record->productStores()->first()->id
                    );

                    if ($existingListItem) {
                        $existingListItem->amount += 1;
                        $existingListItem->save();
                    } else {
                        ShoppingListItem::create([
                            "amount" => 1,
                            // TODO: Select the cheapest option. Maybe using a scope?
                            "product_store_id" => $record
                                ->productStores()
                                ->first()->id,
                            "description" => $record->name,
                        ]);
                    }

                    Notification::make("list_item_added")
                        ->title("$record->name is toegevoegd aan je lijstje")
                        ->send();
                }),
        ];
    }
}
