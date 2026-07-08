<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlannerServiceDefinition extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'seat_capacity',
        'sort_order',
        'color',
        'is_active',
    ];

    protected $casts = [
        'seat_capacity' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
