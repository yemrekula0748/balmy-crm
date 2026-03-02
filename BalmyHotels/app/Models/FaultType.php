<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaultType extends Model
{
    protected $fillable = ['branch_id', 'name', 'completion_hours', 'is_active'];

    public function branch() { return $this->belongsTo(Branch::class); }

    /** Hangi departmanların bu arıza türünü kullanabileceği (boşsa hepsi) */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_fault_type');
    }

    /**
     * Verilen departman bu arıza türüne erişebilir mi?
     * Pivot kayıt yoksa tüm departmanlara açık kabul edilir.
     */
    public function allowedForDepartment(int $departmentId): bool
    {
        $allowed = $this->departments;
        if ($allowed->isEmpty()) return true;
        return $allowed->contains('id', $departmentId);
    }
}
