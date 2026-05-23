<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimationEventDate extends Model
{
    protected $fillable = [
        'animation_event_id',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(AnimationEvent::class, 'animation_event_id');
    }

    public function attendances()
    {
        return $this->hasMany(AnimationEventAttendance::class, 'animation_event_date_id');
    }
}
