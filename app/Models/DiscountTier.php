<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountTier extends Model
{
    protected $guarded = ['id'];

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
