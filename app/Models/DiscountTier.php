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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logUnguarded()
            ->logExcept(["discount_id"])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
