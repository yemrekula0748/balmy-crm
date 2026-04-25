<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditType extends Model
{
    protected $fillable = ['name', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function audits()
    {
        return $this->hasMany(Audit::class);
    }
}
