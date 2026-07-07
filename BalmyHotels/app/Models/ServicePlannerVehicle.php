<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServicePlannerVehicle extends Model
{
    protected $fillable = [
        'service_planner_plan_id',
        'name',
        'seat_capacity',
        'vehicle_order',
        'color',
    ];

    protected $casts = [
        'seat_capacity' => 'integer',
        'vehicle_order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServicePlannerPlan::class, 'service_planner_plan_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ServicePlannerAssignment::class)->orderBy('stop_order');
    }
}
