<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'survey_id', 'sort_order', 'type', 'is_required', 'translations',
        'conditional_question_id', 'conditional_answer_value',
    ];

    protected $casts = [
        'translations' => 'array',
        'is_required'  => 'boolean',
    ];

    const TYPES = [
        'text'     => ['label' => 'Kısa Metin',                  'icon' => 'fa-font'],
        'textarea' => ['label' => 'Uzun Metin',                  'icon' => 'fa-align-left'],
        'radio'    => ['label' => 'Tek Seçim (Radio)',            'icon' => 'fa-dot-circle'],
        'checkbox' => ['label' => 'Çok Seçim (Checkbox)',         'icon' => 'fa-check-square'],
        'rating'   => ['label' => 'Yıldız Değerlendirme (1-5)',   'icon' => 'fa-star'],
        'nps'      => ['label' => 'NPS Skoru (0-10)',             'icon' => 'fa-tachometer-alt'],
    ];

    public function survey()              { return $this->belongsTo(Survey::class); }
    public function conditionalQuestion() { return $this->belongsTo(SurveyQuestion::class, 'conditional_question_id'); }
    public function answers()             { return $this->hasMany(SurveyAnswer::class, 'question_id'); }

    public function getText(string $lang = 'tr'): string
    {
        $t = $this->translations ?? [];
        return $t[$lang]['text'] ?? $t['tr']['text'] ?? (count($t) ? array_values($t)[0]['text'] ?? '' : '') ?: '';
    }

    public function getOptions(string $lang = 'tr'): array
    {
        $t = $this->translations ?? [];
        return $t[$lang]['options'] ?? $t['tr']['options'] ?? [];
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['radio', 'checkbox']);
    }
}
