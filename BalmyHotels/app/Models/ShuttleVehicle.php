<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShuttleVehicle extends Model
{
    protected $fillable = ['branch_id', 'name', 'plate', 'type', 'capacity', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity'  => 'integer',
    ];

    public const TYPES = [
        'minibus' => 'Minibüs',
        'midibus' => 'Midibüs',
        'otobus'  => 'Otobüs',
        'diger'   => 'Diğer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(ShuttleTrip::class, 'shuttle_vehicle_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
