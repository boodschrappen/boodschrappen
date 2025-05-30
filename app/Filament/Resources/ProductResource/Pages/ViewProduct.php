<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Resources\StoreResource;
use App\Models\ShoppingListItem;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

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
                ->modal()
                ->modalHeading("Hoeveel wil je aan je lijstje toevoegen?")
                ->modalWidth(MaxWidth::Small)
                ->form([
                    Forms\Components\TextInput::make("amount")
                        ->hiddenLabel()
                        ->numeric(),
                    Forms\Components\Select::make("product_store_id")
                        ->label(StoreResource::getModelLabel())
                        ->options(
                            $this->record->stores->mapWithKeys(
                                fn($store) => [
                                    $store->pivot->id => $store->name,
                                ]
                            )
                        ),
                ])
                ->action(function ($record) {
                    ShoppingListItem::create([
                        "product_store_id" => $record->productStores()->first()
                            ->id,
                        "description" => $record->name,
                    ]);
                }),
        ];
    }
}
