<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrMenuLanguage extends Model
{
    protected $fillable = [
        'qr_menu_id', 'code', 'name', 'flag', 'is_default', 'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Yaygın kullanılan diller için hazır seçenekler
     */
    const PRESETS = [
        'tr' => ['name' => 'Türkçe',    'flag' => '🇹🇷'],
        'en' => ['name' => 'English',   'flag' => '🇬🇧'],
        'de' => ['name' => 'Deutsch',   'flag' => '🇩🇪'],
        'ar' => ['name' => 'العربية',   'flag' => '🇸🇦'],
        'ru' => ['name' => 'Русский',   'flag' => '🇷🇺'],
        'fr' => ['name' => 'Français',  'flag' => '🇫🇷'],
        'es' => ['name' => 'Español',   'flag' => '🇪🇸'],
        'it' => ['name' => 'Italiano',  'flag' => '🇮🇹'],
        'nl' => ['name' => 'Nederlands','flag' => '🇳🇱'],
        'zh' => ['name' => '中文',      'flag' => '🇨🇳'],
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(QrMenu::class, 'qr_menu_id');
    }
}
