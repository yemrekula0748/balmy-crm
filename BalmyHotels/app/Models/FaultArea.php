<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaultArea extends Model
{
    protected $fillable = ['fault_location_id', 'name', 'is_active'];

    public function location() { return $this->belongsTo(FaultLocation::class, 'fault_location_id'); }
}
