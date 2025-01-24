<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Discount extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['start', 'end'];
    }

    public function product(): HasOneThrough
    {
        return $this->hasOneThrough(Product::class, ProductStore::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(DiscountTier::class);
    }

    public function store(): HasOneThrough
    {
        return $this->hasOneThrough(Store::class, ProductStore::class);
    }
}
