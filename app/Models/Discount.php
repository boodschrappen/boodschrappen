<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Discount extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $with = ['tiers'];

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
        return $this->hasMany(DiscountTier::class, 'discount_id');
    }

    public function store(): HasOneThrough
    {
        return $this->hasOneThrough(Store::class, ProductStore::class);
    }
}
