<?php

namespace App\Filament\Tables\Actions;

use Filament\Actions\Action;
use App\Filament\Actions\Traits\AddToList;

class AddToListAction extends Action
{
    use AddToList;

    public function shouldClearRecordAfter(): bool
    {
        return true;
    }
}
