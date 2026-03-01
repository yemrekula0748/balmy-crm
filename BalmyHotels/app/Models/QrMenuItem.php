<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrMenuItem extends Model
{
    protected $fillable = [
        'category_id', 'title', 'description', 'price', 'image',
        'is_active', 'is_featured', 'badges', 'sort_order',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'badges'      => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'price'       => 'float',
    ];

    /**
     * Yaygın rozet/etiket seçenekleri
     */
    const BADGE_OPTIONS = [
        'Vegan', 'Vejeteryan', 'Glutensiz', 'Laktozsuz',
        'Acılı', 'Önerilen', 'Yeni', 'Popüler',
    ];

    const BADGE_COLORS = [
        'Vegan'      => '#2d6a4f',
        'Vejeteryan' => '#40916c',
        'Glutensiz'  => '#e9c46a',
        'Laktozsuz'  => '#f4a261',
        'Acılı'      => '#e63946',
        'Önerilen'   => '#c19b77',
        'Yeni'       => '#457b9d',
        'Popüler'    => '#9b2226',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(QrMenuCategory::class, 'category_id');
    }

    public function getTitle(string $lang = 'tr'): string
    {
        $titles = array_filter((array)($this->title ?? []), fn($v) => is_string($v) && $v !== '');
        return $titles[$lang] ?? array_values($titles)[0] ?? '—';
    }

    public function getDescription(string $lang = 'tr'): string
    {
        $descs = array_filter((array)($this->description ?? []), fn($v) => is_string($v) && $v !== '');
        return $descs[$lang] ?? array_values($descs)[0] ?? '';
    }

    public function formattedPrice(?string $symbol = '₺'): string
    {
        if ($this->price === null) return '';
        return $symbol . ' ' . number_format($this->price, 2);
    }
}
