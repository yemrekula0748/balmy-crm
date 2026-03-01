<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrMenuCategory extends Model
{
    protected $fillable = [
        'qr_menu_id', 'title', 'description', 'icon', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'title'       => 'array',
        'description' => 'array',
        'is_active'   => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(QrMenu::class, 'qr_menu_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QrMenuItem::class, 'category_id')->orderBy('sort_order');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->where('is_active', true);
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
}
