<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'user_id',
        'start_km', 'end_km',
        'gps_km', 'avg_speed', 'min_speed', 'max_speed',
        'start_km_photo', 'end_km_photo',
        'destination', 'notes',
        'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function locations()
    {
        return $this->hasMany(VehicleTripLocation::class)->orderBy('recorded_at');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Toplam km (eğer tamamlandıysa) */
    public function totalKm(): ?int
    {
        if ($this->end_km && $this->start_km) {
            return $this->end_km - $this->start_km;
        }
        return null;
    }
}
