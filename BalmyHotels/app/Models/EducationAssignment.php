<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationAssignment extends Model
{
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'education_course_id',
        'user_id',
        'assigned_by',
        'language',
        'assigned_week_start',
        'due_at',
        'watched_seconds',
        'max_watched_seconds',
        'duration_seconds',
        'progress_percent',
        'status',
        'started_at',
        'completed_at',
        'last_watched_at',
    ];

    protected $casts = [
        'assigned_week_start' => 'date',
        'due_at' => 'datetime',
        'watched_seconds' => 'integer',
        'max_watched_seconds' => 'integer',
        'duration_seconds' => 'integer',
        'progress_percent' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'education_course_id');
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'Tamamlandi',
            self::STATUS_IN_PROGRESS => 'Devam ediyor',
            default => 'Baslamadi',
        };
    }
}
