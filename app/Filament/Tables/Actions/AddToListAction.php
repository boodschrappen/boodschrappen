<?php

namespace App\Filament\Tables\Actions;

use App\Filament\Actions\Traits\AddToList;
use Filament\Tables\Actions\Action;

class AddToListAction extends Action
{
    use AddToList;

    public function shouldClearRecordAfter(): bool
    {
        return true;
    }
}
