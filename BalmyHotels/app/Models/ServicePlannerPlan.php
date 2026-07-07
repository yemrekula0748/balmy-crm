<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlannerPlan extends Model
{
    protected $fillable = [
        'branch_id',
        'created_by',
        'name',
        'plan_date',
        'start_location_name',
        'start_address',
        'start_latitude',
        'start_longitude',
        'status',
        'route_generated_at',
        'geocoding_provider',
        'planning_notes',
        'meta',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'route_generated_at' => 'datetime',
        'meta' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(ServicePlannerVehicle::class)->orderBy('vehicle_order');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(ServicePlannerStop::class)->orderBy('row_number');
    }

    public function assignments(): HasMany
    {
        return $this->hasManyThrough(
            ServicePlannerAssignment::class,
            ServicePlannerVehicle::class,
            'service_planner_plan_id',
            'service_planner_vehicle_id'
        );
    }

    public function getTotalSeatCapacityAttribute(): int
    {
        return (int) $this->vehicles->sum('seat_capacity');
    }

    public function getImportedStopCountAttribute(): int
    {
        return (int) $this->stops->count();
    }
}
