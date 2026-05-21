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
        'is_active',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
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

    public function getLanguageLabelAttribute(): string
    {
        return self::LANGUAGES[$this->language] ?? strtoupper((string) $this->language);
    }
}
