<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSurveyQuestion extends Model
{
    protected $table = 'staff_survey_questions';

    protected $fillable = [
        'survey_id', 'sort_order', 'type',
        'title', 'options', 'required',
        'condition_question_id', 'condition_answer',
    ];

    protected $casts = [
        'title'    => 'array',
        'options'  => 'array',
        'required' => 'boolean',
    ];

    // --- İlişkiler ---
    public function survey()            { return $this->belongsTo(StaffSurvey::class, 'survey_id'); }
    public function conditionQuestion() { return $this->belongsTo(StaffSurveyQuestion::class, 'condition_question_id'); }
    public function answers()           { return $this->hasMany(StaffSurveyAnswer::class, 'question_id'); }

    // --- Yardımcılar ---
    public function getTitle(string $lang = 'tr'): string
    {
        return $this->title[$lang]
            ?? $this->title['tr']
            ?? (count($this->title ?? []) ? array_values($this->title)[0] : '')
            ?: '';
    }

    /** Belirli dil için seçenekleri döner (radio/checkbox) */
    public function getOptions(string $lang = 'tr'): array
    {
        $opts = $this->options ?? [];
        if (isset($opts[$lang])) return $opts[$lang];
        if (isset($opts['tr']))  return $opts['tr'];
        return count($opts) ? (array)(array_values($opts)[0]) : [];
    }

    /** Bu soru koşullu mu? */
    public function hasCondition(): bool
    {
        return !is_null($this->condition_question_id) && !is_null($this->condition_answer);
    }
}
