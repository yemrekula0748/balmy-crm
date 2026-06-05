<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksOvertimeRequest extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'department_id', 'overtime_date', 'start_time',
        'end_time', 'duration_minutes', 'status', 'reason', 'manager_note',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
