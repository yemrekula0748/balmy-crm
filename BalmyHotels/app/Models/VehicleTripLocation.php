<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleTripLocation extends Model
{
    protected $fillable = ['vehicle_trip_id', 'lat', 'lng', 'speed', 'recorded_at'];

    protected $casts = ['recorded_at' => 'datetime'];

    public function trip()
    {
        return $this->belongsTo(VehicleTrip::class, 'vehicle_trip_id');
    }
}
