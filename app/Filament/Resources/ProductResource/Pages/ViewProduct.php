<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Actions\AddToListAction;
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
        return [AddToListAction::make()];
    }
}
