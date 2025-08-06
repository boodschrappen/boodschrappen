<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DiscountTier extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ["id"];

    protected $fillable = [
        "description",
        "amount",
        "unit",
        "size",
        "discount_id",
        "created_at",
        "updated_at",
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(["discount_id"])
            ->dontSubmitEmptyLogs();
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
