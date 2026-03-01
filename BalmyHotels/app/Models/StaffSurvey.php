<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSurvey extends Model
{
    protected $fillable = [
        'branch_id', 'created_by',
        'title', 'description', 'slug', 'languages',
        'is_anonymous', 'show_dept_field', 'show_employee_id_field',
        'show_language_select', 'allow_multiple', 'is_active',
    ];

    protected $attributes = [
        'languages' => '["tr"]',
    ];

    protected $casts = [
        'title'                   => 'array',
        'description'             => 'array',
        'languages'               => 'array',
        'is_anonymous'            => 'boolean',
        'show_dept_field'         => 'boolean',
        'show_employee_id_field'  => 'boolean',
        'show_language_select'    => 'boolean',
        'allow_multiple'          => 'boolean',
        'is_active'               => 'boolean',
    ];

    // Misafir Anket ile aynı dil listesi
    const AVAILABLE_LANGUAGES = [
        'tr' => ['name' => 'Türkçe',   'flag' => '🇹🇷', 'dir' => 'ltr'],
        'en' => ['name' => 'English',  'flag' => '🇬🇧', 'dir' => 'ltr'],
        'de' => ['name' => 'Deutsch',  'flag' => '🇩🇪', 'dir' => 'ltr'],
        'ru' => ['name' => 'Русский',  'flag' => '🇷🇺', 'dir' => 'ltr'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦', 'dir' => 'rtl'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'dir' => 'ltr'],
    ];

    // --- İlişkiler ---
    public function branch()    { return $this->belongsTo(Branch::class); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function questions() { return $this->hasMany(StaffSurveyQuestion::class, 'survey_id')->orderBy('sort_order'); }
    public function responses() { return $this->hasMany(StaffSurveyResponse::class, 'survey_id'); }

    // --- Yardımcılar ---
    public function getTitle(string $lang = 'tr'): string
    {
        return $this->title[$lang]
            ?? $this->title['tr']
            ?? (count($this->title ?? []) ? array_values($this->title)[0] : '')
            ?: '';
    }

    public function getDescription(string $lang = 'tr'): string
    {
        $d = $this->description ?? [];
        return $d[$lang] ?? $d['tr'] ?? (count($d) ? array_values($d)[0] : '') ?: '';
    }

    public function publicUrl(): string
    {
        return route('staff-surveys.public.form', $this->slug);
    }

    public function getLangName(string $lang): string
    {
        return self::AVAILABLE_LANGUAGES[$lang]['name'] ?? strtoupper($lang);
    }
}
