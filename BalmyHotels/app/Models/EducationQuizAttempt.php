<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationQuizAttempt extends Model
{
    protected $fillable = [
        'education_assignment_id',
        'education_course_id',
        'user_id',
        'total_questions',
        'correct_answers',
        'min_correct',
        'passed',
        'submitted_at',
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'min_correct' => 'integer',
        'passed' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(EducationAssignment::class, 'education_assignment_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'education_course_id');
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EducationQuizAttemptAnswer::class, 'education_quiz_attempt_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->passed ? 'Basarili' : 'Tekrar gerekli';
    }
}
