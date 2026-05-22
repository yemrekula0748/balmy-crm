<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationCourse extends Model
{
    public const LANGUAGES = [
        'tr' => 'Turkce',
        'en' => 'Ingilizce',
        'ru' => 'Rusca',
    ];

    protected $fillable = [
        'trainer_id',
        'title',
        'description',
        'language',
        'video_path',
        'video_original_name',
        'duration_seconds',
        'quiz_min_correct',
        'is_active',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'quiz_min_correct' => 'integer',
        'is_active' => 'boolean',
    ];

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EducationAssignment::class);
    }

    public function quizQuestions(): HasMany
    {
        return $this->hasMany(EducationQuizQuestion::class)->orderBy('sort_order');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(EducationQuizAttempt::class);
    }

    public function getLanguageLabelAttribute(): string
    {
        return self::LANGUAGES[$this->language] ?? strtoupper((string) $this->language);
    }

    public function getHasQuizAttribute(): bool
    {
        if ($this->relationLoaded('quizQuestions')) {
            return $this->quizQuestions->isNotEmpty();
        }

        return $this->quizQuestions()->exists();
    }
}
