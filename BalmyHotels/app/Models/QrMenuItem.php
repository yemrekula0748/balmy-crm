<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrMenuItem extends Model
{
    /** Aktif fiyat: override varsa onu kullan, yoksa library fiyatı */
    public function effectivePrice(): ?float
    {
        return $this->price_override ?? $this->price;
    }

    protected $fillable = [
        'category_id', 'food_product_id',
        'title', 'description', 'price', 'price_override', 'image',
        'is_active', 'is_featured', 'badges', 'sort_order',
    ];

    protected $casts = [
        'title'          => 'array',
        'description'    => 'array',
        'badges'         => 'array',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'price'          => 'float',
        'price_override' => 'float',
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

    public function foodProduct(): BelongsTo
    {
        return $this->belongsTo(FoodProduct::class, 'food_product_id');
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
        $p = $this->effectivePrice();
        if ($p === null) return '';
        return $symbol . ' ' . number_format($p, 2);
    }
}
