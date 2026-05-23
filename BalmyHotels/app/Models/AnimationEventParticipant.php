<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimationEventParticipant extends Model
{
    protected $fillable = [
        'animation_event_id',
        'name',
    ];

    public function event()
    {
        return $this->belongsTo(AnimationEvent::class, 'animation_event_id');
    }

    public function attendances()
    {
        return $this->hasMany(AnimationEventAttendance::class, 'animation_event_participant_id');
    }
}
