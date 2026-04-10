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
        'sub_heading', 'price_glass', 'price_bottle', 'cl_glass', 'cl_bottle',
    ];

    protected $casts = [
        'title'          => 'array',
        'description'    => 'array',
        'badges'         => 'array',
        'sub_heading'    => 'array',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'price'          => 'float',
        'price_override' => 'float',
        'price_glass'    => 'float',
        'price_bottle'   => 'float',
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

    /**
     * Ürün kütüphanesiyle bağlantılıysa canlı görseli döndür.
     * Manuel yüklenen özel görseller (qrmenu/items/) her zaman önceliklidir.
     */
    public function getImageAttribute($value): ?string
    {
        // Manuel yüklenen özel görsel — her zaman öncelikli
        if ($value && str_starts_with((string) $value, 'qrmenu/items/')) {
            return $value;
        }
        // Kütüphane ürününe bağlıysa canlı görseli kullan
        if ($this->food_product_id) {
            return optional($this->foodProduct)->image ?? $value;
        }
        return $value;
    }

    public function getSubHeading(string $lang = 'tr'): string
    {
        $sh = array_filter((array)($this->sub_heading ?? []), fn($v) => is_string($v) && $v !== '');
        return $sh[$lang] ?? array_values($sh)[0] ?? '';
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
