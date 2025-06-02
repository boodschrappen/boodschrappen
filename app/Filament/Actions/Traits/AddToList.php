<?php

namespace App\Filament\Actions\Traits;

use App\Models\ShoppingListItem;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Illuminate\Database\Eloquent\Model;

trait AddToList
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return "add_to_list";
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label("Voeg toe aan je lijstje");

        $this->successNotificationTitle(
            fn($record) => "$record->name is toegevoegd aan je lijstje"
        );

        $this->badge(
            fn($record) => ShoppingListItem::firstWhere(
                "product_store_id",
                $record->productStores()->first()->id
            )?->amount
        );

        $this->badgeColor("info");

        $this->icon("heroicon-o-shopping-cart");

        $this->successNotificationTitle(
            fn($record) => "$record->name is toegevoegd aan je lijstje"
        );

        $this->authorize("create", ShoppingListItem::class);

        $this->action(function (): void {
            $this->process(function (Model $record) {
                // TODO: Select the cheapest option. Maybe using a scope?
                $storeProductId = $record->productStores()->first()->id;

                $existingListItem = ShoppingListItem::firstWhere(
                    "product_store_id",
                    $storeProductId
                );

                if ($existingListItem) {
                    $existingListItem->amount += 1;
                    $existingListItem->save();
                } else {
                    ShoppingListItem::create([
                        "product_store_id" => $storeProductId,
                        "description" => $record->name,
                    ]);
                }
            });

            $this->success();
        });
    }
}
