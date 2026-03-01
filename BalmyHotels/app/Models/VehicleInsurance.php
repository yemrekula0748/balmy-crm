<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id','type','company','policy_no',
        'start_date','end_date','cost','notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Bitiş tarihi yaklaşıyor mu? (30 gün içinde) */
    public function isExpiringSoon(): bool
    {
        return $this->end_date >= now() && $this->end_date <= now()->addDays(30);
    }

    /** Süresi geçmiş mi? */
    public function isExpired(): bool
    {
        return $this->end_date < now();
    }
}
