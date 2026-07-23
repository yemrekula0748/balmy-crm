<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShuttleTripBranchCompletion extends Model
{
    protected $fillable = [
        'shuttle_trip_id',
        'branch_id',
        'completed_at',
        'completed_by',
        'completed_with_missing_data',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'completed_with_missing_data' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(ShuttleTrip::class, 'shuttle_trip_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
