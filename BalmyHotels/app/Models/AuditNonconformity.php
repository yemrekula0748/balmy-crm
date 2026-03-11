<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditNonconformity extends Model
{
    protected $fillable = [
        'audit_id', 'branch_id', 'department_id',
        'description', 'photo_path',
        'status', 'resolved_at', 'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    const STATUSES = [
        'open'     => 'Açık',
        'resolved' => 'Çözüldü',
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
