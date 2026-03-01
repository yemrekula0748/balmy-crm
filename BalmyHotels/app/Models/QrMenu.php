<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class QrMenu extends Model
{
    protected $fillable = [
        'branch_id', 'created_by', 'name', 'title', 'description',
        'logo', 'cover_image', 'theme_color', 'is_active',
        'currency', 'currency_symbol',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'is_active'   => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(QrMenuLanguage::class)->orderBy('sort_order');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(QrMenuCategory::class)->orderBy('sort_order');
    }

    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(QrMenuItem::class, QrMenuCategory::class, 'qr_menu_id', 'category_id');
    }

    /**
     * Belirli dildeki başlığı döndürür, yoksa ilk mevcut dili kullanır
     */
    public function getTitle(string $lang = 'tr'): string
    {
        $titles = array_filter((array)($this->title ?? []), fn($v) => is_string($v) && $v !== '');
        return $titles[$lang] ?? array_values($titles)[0] ?? $this->name ?? '';
    }

    /**
     * QR kod için public URL
     */
    public function publicUrl(): string
    {
        return route('qrmenu.show', $this->name);
    }

    /**
     * Varsayılan dil kodu
     */
    public function defaultLanguage(): ?QrMenuLanguage
    {
        return $this->languages->firstWhere('is_default', true)
            ?? $this->languages->first();
    }
}
