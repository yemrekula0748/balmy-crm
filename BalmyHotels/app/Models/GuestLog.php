<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestLog extends Model
{
    protected $fillable = [
        'branch_id', 'department_id', 'host_user_id', 'created_by',
        'visitor_name', 'visitor_phone', 'visitor_id_no', 'visitor_company',
        'purpose', 'purpose_note',
        'check_in_at', 'check_out_at',
        'notes',
    ];

    protected $casts = [
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    const PURPOSES = [
        'meeting'   => 'Toplantı',
        'delivery'  => 'Teslimat',
        'interview' => 'Görüşme / Mülakat',
        'official'  => 'Resmi Ziyaret',
        'other'     => 'Diğer',
    ];

    const PURPOSE_COLORS = [
        'meeting'   => 'primary',
        'delivery'  => 'warning',
        'interview' => 'info',
        'official'  => 'success',
        'other'     => 'secondary',
    ];

    public function branch()      { return $this->belongsTo(Branch::class); }
    public function department()  { return $this->belongsTo(Department::class); }
    public function host()        { return $this->belongsTo(User::class, 'host_user_id'); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by'); }

    public function isInside(): bool
    {
        return is_null($this->check_out_at);
    }

    public function durationMinutes(): ?int
    {
        if (!$this->check_out_at) return null;
        return (int) $this->check_in_at->diffInMinutes($this->check_out_at);
    }
}
