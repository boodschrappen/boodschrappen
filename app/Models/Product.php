<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'gtins' => 'array',
    ];

    public function discounts(): HasManyThrough
    {
        return $this->hasManyThrough(Discount::class, ProductStore::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'product_stores')
            ->withPivot('original_price', 'reduced_price');
    }

    public function productStores(): HasMany
    {
        return $this->hasMany(ProductStore::class);
    }
}
