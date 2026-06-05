<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksShiftAssignment extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'department_id', 'shift_type_id', 'shift_date',
        'status', 'notes', 'assigned_by',
    ];

    protected $casts = ['shift_date' => 'date'];

    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function shiftType() { return $this->belongsTo(PdksShiftType::class, 'shift_type_id'); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by'); }
    public function changeRequests() { return $this->hasMany(PdksShiftChangeRequest::class, 'shift_assignment_id'); }
}
