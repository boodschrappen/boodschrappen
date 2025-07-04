<?php

namespace App\Tables\Columns;

use Filament\Tables\Columns\Column;

class WhereToBuyColumn extends Column
{
    protected string $view = "tables.columns.where-to-buy-column";

    protected bool $showPrice = true;

    public function showPrice($showPrice = true): self
    {
        $this->showPrice = $this->evaluate($showPrice, [
            "state" => $this->getState(),
            "record" => $this->getRecord(),
        ]);

        return $this;
    }
}
