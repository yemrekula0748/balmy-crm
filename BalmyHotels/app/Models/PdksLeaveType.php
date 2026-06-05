<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksLeaveType extends Model
{
    protected $fillable = ['name', 'code', 'default_days', 'is_paid', 'requires_approval', 'color', 'is_active'];

    protected $casts = [
        'default_days' => 'decimal:2',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requests() { return $this->hasMany(PdksLeaveRequest::class, 'leave_type_id'); }
}
