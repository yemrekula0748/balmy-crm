<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksLeaveRequest extends Model
{
    public const STATUSES = [
        'pending' => 'Onay Bekliyor',
        'approved' => 'Onaylandi',
        'rejected' => 'Reddedildi',
        'cancelled' => 'Iptal',
    ];

    protected $fillable = [
        'employee_id', 'branch_id', 'department_id', 'leave_type_id', 'start_date',
        'end_date', 'total_days', 'status', 'reason', 'manager_note', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function leaveType() { return $this->belongsTo(PdksLeaveType::class, 'leave_type_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
