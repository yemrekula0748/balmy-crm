<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaultLocation extends Model
{
    protected $fillable = ['branch_id', 'name', 'is_active'];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function areas()  { return $this->hasMany(FaultArea::class)->where('is_active', true)->orderBy('name'); }
}
