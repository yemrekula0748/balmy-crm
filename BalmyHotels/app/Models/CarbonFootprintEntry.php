<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarbonFootprintEntry extends Model
{
    protected $fillable = [
        'report_id', 'scope', 'category', 'sub_category', 'source_description',
        'quantity', 'unit', 'emission_factor', 'ef_source', 'co2_kg',
        'is_renewable', 'notes',
    ];

    protected $casts = [
        'scope'           => 'integer',
        'quantity'        => 'float',
        'emission_factor' => 'float',
        'co2_kg'          => 'float',
        'is_renewable'    => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CarbonFootprintReport::class, 'report_id');
    }

    /** co2_kg hesapla */
    public function calcCo2(): float
    {
        return round($this->quantity * $this->emission_factor, 3);
    }

    public function getScopeLabelAttribute(): string
    {
        return match ($this->scope) {
            1 => 'Scope 1',
            2 => 'Scope 2',
            3 => 'Scope 3',
            default => '-',
        };
    }

    public function getScopeBadgeClassAttribute(): string
    {
        return match ($this->scope) {
            1 => 'badge bg-danger',
            2 => 'badge bg-warning text-dark',
            3 => 'badge bg-info text-dark',
            default => 'badge bg-secondary',
        };
    }
}
