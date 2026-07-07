<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServicePlannerStop extends Model
{
    protected $fillable = [
        'service_planner_plan_id',
        'row_number',
        'passenger_name',
        'phone',
        'district',
        'address',
        'notes',
        'latitude',
        'longitude',
        'distance_to_start_km',
        'geocode_status',
        'geocode_provider',
        'geocode_message',
    ];

    protected $casts = [
        'row_number' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'distance_to_start_km' => 'float',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ServicePlannerPlan::class, 'service_planner_plan_id');
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(ServicePlannerAssignment::class, 'service_planner_stop_id');
    }
}
