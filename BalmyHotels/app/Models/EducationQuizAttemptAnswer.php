<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationQuizAttemptAnswer extends Model
{
    protected $fillable = [
        'education_quiz_attempt_id',
        'education_quiz_question_id',
        'education_quiz_option_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(EducationQuizAttempt::class, 'education_quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(EducationQuizQuestion::class, 'education_quiz_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(EducationQuizOption::class, 'education_quiz_option_id');
    }
}
