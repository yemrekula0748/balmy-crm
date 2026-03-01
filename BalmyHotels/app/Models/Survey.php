<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = [
        'branch_id', 'created_by', 'title', 'description', 'slug',
        'languages', 'is_active', 'show_language_select',
    ];

    protected $casts = [
        'title'                => 'array',
        'description'          => 'array',
        'languages'            => 'array',
        'is_active'            => 'boolean',
        'show_language_select' => 'boolean',
    ];

    const AVAILABLE_LANGUAGES = [
        'tr' => ['name' => 'Türkçe',    'flag' => '🇹🇷', 'dir' => 'ltr'],
        'en' => ['name' => 'English',   'flag' => '🇬🇧', 'dir' => 'ltr'],
        'de' => ['name' => 'Deutsch',   'flag' => '🇩🇪', 'dir' => 'ltr'],
        'ru' => ['name' => 'Русский',   'flag' => '🇷🇺', 'dir' => 'ltr'],
        'ar' => ['name' => 'العربية',  'flag' => '🇸🇦', 'dir' => 'rtl'],
        'fr' => ['name' => 'Français',  'flag' => '🇫🇷', 'dir' => 'ltr'],
    ];

    public function branch()    { return $this->belongsTo(Branch::class); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function questions() { return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order'); }
    public function responses() { return $this->hasMany(SurveyResponse::class); }

    public function getTitle(string $lang = 'tr'): string
    {
        return $this->title[$lang]
            ?? $this->title['tr']
            ?? (count($this->title ?? []) ? array_values($this->title)[0] : '')
            ?: '';
    }

    public function getDescription(string $lang = 'tr'): string
    {
        $desc = $this->description ?? [];
        return $desc[$lang] ?? $desc['tr'] ?? (count($desc) ? array_values($desc)[0] : '') ?: '';
    }

    public function publicUrl(string $lang = null): string
    {
        return $lang
            ? route('surveys.public.form', [$this->slug, $lang])
            : route('surveys.public.splash', $this->slug);
    }

    public function getLangName(string $lang): string
    {
        return self::AVAILABLE_LANGUAGES[$lang]['name'] ?? strtoupper($lang);
    }
}
