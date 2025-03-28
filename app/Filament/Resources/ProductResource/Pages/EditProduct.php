<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Jobs\FetchProduct;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('fetch')
                ->label('Product heropvragen')
                ->action(fn(Product $record) => $record->productStores->each(
                    function (ProductStore $productStore) {
                        FetchProduct::dispatch($productStore);

                        Notification::make()
                            ->title('Product wordt binnenkort opgehaald')
                            ->body('Dit kan even duren.')
                            ->success()
                            ->send();
                    }
                )),
            Actions\DeleteAction::make(),
        ];
    }
}
