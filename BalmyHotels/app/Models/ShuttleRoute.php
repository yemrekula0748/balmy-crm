<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuttleRoute extends Model
{
    protected $fillable = ['branch_id', 'name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class)->withDefault([
            'name' => 'Ortak',
        ]);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(ShuttleTrip::class, 'route_id');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(
            ShuttleVehicle::class,
            'shuttle_route_vehicle',
            'shuttle_route_id',
            'shuttle_vehicle_id'
        )->withTimestamps();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
