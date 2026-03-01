<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fault extends Model
{
    protected $fillable = [
        'branch_id', 'reported_by', 'assigned_department_id',
        'fault_type_id', 'fault_location_id', 'fault_area_id',
        'title', 'description', 'image_path', 'status',
        'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    const PRIORITIES = [
        'low'      => 'Düşük',
        'medium'   => 'Orta',
        'high'     => 'Yüksek',
        'critical' => 'Kritik',
    ];

    const STATUSES = [
        'open'        => 'Açık',
        'in_progress' => 'İşlemde',
        'resolved'    => 'Çözüldü',
        'closed'      => 'Kapalı',
    ];

    const PRIORITY_COLORS = [
        'low'      => 'success',
        'medium'   => 'warning',
        'high'     => 'danger',
        'critical' => 'dark',
    ];

    const STATUS_COLORS = [
        'open'        => 'danger',
        'in_progress' => 'warning',
        'resolved'    => 'success',
        'closed'      => 'secondary',
    ];

    public function branch()      { return $this->belongsTo(Branch::class); }
    public function reporter()    { return $this->belongsTo(User::class, 'reported_by'); }
    public function department()  { return $this->belongsTo(Department::class, 'assigned_department_id'); }
    public function faultType()   { return $this->belongsTo(FaultType::class); }
    public function faultLocation() { return $this->belongsTo(FaultLocation::class); }
    public function faultArea()   { return $this->belongsTo(FaultArea::class); }
    public function updates()     { return $this->hasMany(FaultUpdate::class)->orderBy('created_at', 'desc'); }

    public function resolutionTimeHours(): ?int
    {
        if ($this->resolved_at) {
            return (int) $this->created_at->diffInHours($this->resolved_at);
        }
        return null;
    }
}
