<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksAttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id', 'user_id', 'branch_id', 'department_id', 'shift_assignment_id',
        'work_date', 'check_in_at', 'check_out_at', 'check_in_latitude',
        'check_in_longitude', 'check_in_accuracy', 'check_out_latitude',
        'check_out_longitude', 'check_out_accuracy', 'check_in_wifi_ssid',
        'check_in_wifi_bssid', 'check_out_wifi_ssid', 'check_out_wifi_bssid',
        'check_in_mock_detected', 'check_out_mock_detected', 'verification_status',
        'verification_issues', 'worked_minutes', 'break_minutes', 'late_minutes',
        'early_leave_minutes', 'status', 'source', 'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_mock_detected' => 'boolean',
        'check_out_mock_detected' => 'boolean',
        'verification_issues' => 'array',
    ];

    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function shiftAssignment() { return $this->belongsTo(PdksShiftAssignment::class, 'shift_assignment_id'); }
    public function breaks() { return $this->hasMany(PdksBreakRecord::class, 'attendance_record_id'); }

    public function getWorkedTimeLabelAttribute(): string
    {
        $minutes = (int) $this->worked_minutes;

        return intdiv($minutes, 60) . ' sa. ' . ($minutes % 60) . ' dk.';
    }
}
