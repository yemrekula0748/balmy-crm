<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksShiftType extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'code', 'start_time', 'end_time', 'planned_minutes',
        'break_minutes', 'late_grace_minutes', 'early_leave_grace_minutes',
        'crosses_midnight', 'color', 'is_active',
    ];

    protected $casts = [
        'crosses_midnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function assignments() { return $this->hasMany(PdksShiftAssignment::class, 'shift_type_id'); }
}
