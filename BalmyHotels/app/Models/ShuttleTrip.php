<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuttleTrip extends Model
{
    protected $fillable = [
        'shuttle_vehicle_id',
        'route_id',
        'branch_id',
        'shift',
        'trip_date',
        'arrival_time',
        'arrival_count',
        'departure_time',
        'departure_count',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'trip_date'       => 'date',
        'arrival_count'   => 'integer',
        'departure_count' => 'integer',
    ];

    public const SHIFTS = [
        'A Shifti',
        'B Shifti',
        'C Shifti',
        'Ara Shift 12-9',
        'İdari',
        'Lojman',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(ShuttleVehicle::class, 'shuttle_vehicle_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(ShuttleRoute::class, 'route_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Doluluk oranı (geliş) % */
    public function getArrivalOccupancyAttribute(): ?float
    {
        $capacity = $this->vehicle->capacity ?? 0;
        if ($capacity <= 0) return null;
        return round(($this->arrival_count / $capacity) * 100, 1);
    }

    /** Doluluk oranı (dönüş) % */
    public function getDepartureOccupancyAttribute(): ?float
    {
        $capacity = $this->vehicle->capacity ?? 0;
        if ($capacity <= 0) return null;
        return round(($this->departure_count / $capacity) * 100, 1);
    }

    public function scopeForBranch($q, $branchId)
    {
        return $q->where('branch_id', $branchId);
    }

    public function scopeForDate($q, $date)
    {
        return $q->where('trip_date', $date);
    }

    public function scopeForPeriod($q, $from, $to)
    {
        return $q->whereBetween('trip_date', [$from, $to]);
    }
}
