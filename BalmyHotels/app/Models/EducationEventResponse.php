<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationEventResponse extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ATTENDING = 'attending';
    public const STATUS_DECLINED = 'declined';

    protected $fillable = [
        'education_event_id',
        'user_id',
        'status',
        'note',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(EducationEvent::class, 'education_event_id');
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ATTENDING => 'Katilacak',
            self::STATUS_DECLINED => 'Katilamayacak',
            default => 'Cevap bekliyor',
        };
    }
}
