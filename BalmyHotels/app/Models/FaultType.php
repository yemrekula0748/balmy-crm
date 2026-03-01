<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaultType extends Model
{
    protected $fillable = ['branch_id', 'name', 'completion_hours', 'is_active'];

    public function branch() { return $this->belongsTo(Branch::class); }
}
