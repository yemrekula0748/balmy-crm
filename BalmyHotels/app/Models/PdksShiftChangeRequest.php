<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksShiftChangeRequest extends Model
{
    protected $fillable = [
        'employee_id', 'shift_assignment_id', 'requested_shift_type_id',
        'requested_shift_date', 'status', 'reason', 'manager_note', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'requested_shift_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function shiftAssignment() { return $this->belongsTo(PdksShiftAssignment::class, 'shift_assignment_id'); }
    public function requestedShiftType() { return $this->belongsTo(PdksShiftType::class, 'requested_shift_type_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
