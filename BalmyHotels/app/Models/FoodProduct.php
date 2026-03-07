<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodProduct extends Model
{
    protected $fillable = [
        'branch_id', 'food_category_id',
        'title', 'description', 'price',
        'image', 'badges', 'options',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'badges'      => 'array',
        'options'     => 'array',
        'is_active'   => 'boolean',
        'price'       => 'float',
    ];

    /** Rozet seçenekleri — QrMenuItem ile aynı */
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

    /** Dinamik opsiyon tipleri */
    const OPTION_TYPES = [
        'text'   => 'Metin',
        'number' => 'Sayı',
        'tags'   => 'Etiket listesi',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function foodCategory(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class);
    }

    /** Bu üründen türetilen menü kalemleri */
    public function menuItems(): HasMany
    {
        return $this->hasMany(QrMenuItem::class, 'food_product_id');
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
