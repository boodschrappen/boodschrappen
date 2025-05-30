<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    protected $fillable = [
        "amount",
        "checked",
        "custom",
        "user_id",
        "product_store_id",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storeProduct(): BelongsTo
    {
        return $this->belongsTo(ProductStore::class, "product_store_id");
    }

    public static function boot(): void
    {
        parent::boot();

        self::creating(
            fn($model) => $model->fill(["user_id" => auth()->user()?->id])
        );

        self::addGlobalScope(
            "user",
            fn($model) => $model->where("user_id", auth()->user()?->id)
        );
    }
}
