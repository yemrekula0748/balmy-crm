<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $fillable = [
        'branch_id', 'department_id', 'audit_type_id',
        'audited_by', 'notes', 'status',
    ];

    const STATUSES = [
        'open'   => 'Açık',
        'closed' => 'Kapalı',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function auditType()
    {
        return $this->belongsTo(AuditType::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function nonconformities()
    {
        return $this->hasMany(AuditNonconformity::class);
    }
}
