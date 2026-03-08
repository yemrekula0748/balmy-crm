<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id','plate','brand','model','year','color','type',
        'current_km','license_expiry','chassis_no','engine_no','notes','is_active'
    ];

    protected $casts = ['license_expiry' => 'date', 'is_active' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function operations()
    {
        return $this->hasMany(VehicleOperation::class);
    }

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class)->orderByDesc('maintenance_at');
    }

    public function insurances()
    {
        return $this->hasMany(VehicleInsurance::class)->orderByDesc('end_date');
    }

    /** Aktif sigorta poliçesi */
    public function activeInsurance()
    {
        return $this->hasOne(VehicleInsurance::class)
            ->where('type', 'trafik')
            ->where('end_date', '>=', now())
            ->orderByDesc('end_date');
    }

    /** Aktif kasko poliçesi */
    public function activeCasco()
    {
        return $this->hasOne(VehicleInsurance::class)
            ->where('type', 'kasko')
            ->where('end_date', '>=', now())
            ->orderByDesc('end_date');
    }

    /** Son bakım */
    public function lastMaintenance()
    {
        return $this->hasOne(VehicleMaintenance::class)->orderByDesc('maintenance_at');
    }

    /** Aktif görev */
    public function activeTrip()
    {
        return $this->hasOne(VehicleTrip::class)->where('status', 'active');
    }

    public function trips()
    {
        return $this->hasMany(VehicleTrip::class)->orderByDesc('started_at');
    }

    /** Şubeye göre scope */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
