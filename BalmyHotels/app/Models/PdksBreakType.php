<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdksBreakType extends Model
{
    protected $fillable = ['name', 'code', 'max_minutes', 'is_paid', 'color', 'sort_order', 'is_active'];

    protected $casts = ['is_paid' => 'boolean', 'is_active' => 'boolean'];

    public function records() { return $this->hasMany(PdksBreakRecord::class, 'break_type_id'); }
}
