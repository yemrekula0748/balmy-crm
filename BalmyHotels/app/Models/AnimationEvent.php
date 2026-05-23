<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimationEvent extends Model
{
    protected $fillable = [
        'branch_id',
        'created_by',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dates()
    {
        return $this->hasMany(AnimationEventDate::class)->orderBy('event_date');
    }

    public function participants()
    {
        return $this->hasMany(AnimationEventParticipant::class)->orderBy('name');
    }
}
