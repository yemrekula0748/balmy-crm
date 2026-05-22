<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationQuizQuestion extends Model
{
    protected $fillable = [
        'education_course_id',
        'question',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(EducationCourse::class, 'education_course_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(EducationQuizOption::class)->orderBy('sort_order');
    }
}
