<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuttleTripBranchMovement extends Model
{
    public const DEFAULT_PERIOD = 'day';

    public const PERIODS = [
        'day' => 'İlk Uğrama',
        'evening' => 'İkinci Uğrama',
    ];

    public const TYPES = [
        'arrival' => 'Gelen',
        'departure' => 'Giden',
    ];

    protected $fillable = [
        'shuttle_trip_id',
        'branch_id',
        'movement_period',
        'movement_type',
        'headcount',
        'movement_time',
    ];

    protected $casts = [
        'headcount' => 'integer',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(ShuttleTrip::class, 'shuttle_trip_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->movement_type] ?? $this->movement_type;
    }

    public function getPeriodLabelAttribute(): string
    {
        $period = $this->movement_period ?: self::DEFAULT_PERIOD;

        return self::PERIODS[$period] ?? $period;
    }
}
