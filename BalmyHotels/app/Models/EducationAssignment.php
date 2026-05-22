<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(EducationQuizAttempt::class, 'education_assignment_id');
    }

    public function latestQuizAttempt(): HasOne
    {
        return $this->hasOne(EducationQuizAttempt::class, 'education_assignment_id')->latestOfMany();
    }

    public function passedQuizAttempt(): HasOne
    {
        return $this->hasOne(EducationQuizAttempt::class, 'education_assignment_id')
            ->where('passed', true)
            ->latestOfMany();
    }

    public function passedQuizAttempts(): HasMany
    {
        return $this->hasMany(EducationQuizAttempt::class, 'education_assignment_id')
            ->where('passed', true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'Tamamlandi',
            self::STATUS_IN_PROGRESS => 'Devam ediyor',
            default => 'Baslamadi',
        };
    }

    public function getQuizPassedAttribute(): bool
    {
        if ($this->relationLoaded('passedQuizAttempt')) {
            return (bool) $this->passedQuizAttempt;
        }

        return $this->passedQuizAttempt()->exists();
    }

    public function getVideoCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED || (float) $this->progress_percent >= 95;
    }

    public function getTrainingApprovedAttribute(): bool
    {
        if (! $this->course || ! $this->course->has_quiz) {
            return $this->video_completed;
        }

        return $this->video_completed && $this->quiz_passed;
    }
}
