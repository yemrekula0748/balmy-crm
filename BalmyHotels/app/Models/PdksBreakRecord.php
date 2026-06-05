<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksBreakRecord extends Model
{
    protected $fillable = [
        'attendance_record_id', 'employee_id', 'break_type_id', 'started_at',
        'ended_at', 'duration_minutes', 'start_latitude', 'start_longitude',
        'end_latitude', 'end_longitude', 'mock_detected', 'status', 'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'mock_detected' => 'boolean',
    ];

    public function attendanceRecord() { return $this->belongsTo(PdksAttendanceRecord::class, 'attendance_record_id'); }
    public function employee() { return $this->belongsTo(PdksEmployee::class, 'employee_id'); }
    public function breakType() { return $this->belongsTo(PdksBreakType::class, 'break_type_id'); }
}
