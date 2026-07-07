<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlannerAssignment extends Model
{
    protected $fillable = [
        'service_planner_vehicle_id',
        'service_planner_stop_id',
        'stop_order',
        'leg_distance_km',
        'cumulative_distance_km',
        'travel_minutes',
    ];

    protected $casts = [
        'stop_order' => 'integer',
        'leg_distance_km' => 'float',
        'cumulative_distance_km' => 'float',
        'travel_minutes' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(ServicePlannerVehicle::class, 'service_planner_vehicle_id');
    }

    public function stop(): BelongsTo
    {
        return $this->belongsTo(ServicePlannerStop::class, 'service_planner_stop_id');
    }
}
