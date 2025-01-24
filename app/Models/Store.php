<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    protected $guarded = ['id'];

    public function products(): BelongsToMany
    {
        return $this->hasManyThrough(Product::class, ProductStore::class)
            ->withPivot('current_price', 'reduced_price');
    }

    public function discounts(): BelongsToMany
    {
        return $this->hasManyThrough(Discount::class, ProductStore::class)
            ->withPivot('current_price', 'reduced_price');
    }
}
