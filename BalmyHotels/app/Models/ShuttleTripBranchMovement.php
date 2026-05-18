<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuttleTripBranchMovement extends Model
{
    public const TYPES = [
        'arrival' => 'Gelen',
        'departure' => 'Giden',
    ];

    protected $fillable = [
        'shuttle_trip_id',
        'branch_id',
        'movement_type',
        'headcount',
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
}
