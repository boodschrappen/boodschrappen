<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;
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

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'image' => $this->image,
            'gtins' => (array) $this->gtins,
            'stores' => (array) $this->stores,
            'discounts' => (array) $this->discounts,
        ];
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with('stores', 'discounts');
    }
}
