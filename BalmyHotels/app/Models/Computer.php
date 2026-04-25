<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Computer extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'ip_address', 'location',
        'assigned_user', 'specs', 'notes',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function securitySnapshots()
    {
        return $this->hasMany(ComputerSecuritySnapshot::class);
    }

    public function latestSnapshot()
    {
        return $this->hasOne(ComputerSecuritySnapshot::class)->latestOfMany('reported_at');
    }
}
