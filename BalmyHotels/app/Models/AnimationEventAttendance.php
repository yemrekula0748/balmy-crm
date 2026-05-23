<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimationEventAttendance extends Model
{
    protected $fillable = [
        'animation_event_date_id',
        'animation_event_participant_id',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function eventDate()
    {
        return $this->belongsTo(AnimationEventDate::class, 'animation_event_date_id');
    }

    public function participant()
    {
        return $this->belongsTo(AnimationEventParticipant::class, 'animation_event_participant_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
