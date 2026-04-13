<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MenuShowcase extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'cover_image',
        'accent_color', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuShowcaseItem::class, 'showcase_id')->orderBy('sort_order');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(QrMenu::class, 'menu_showcase_items', 'showcase_id', 'qr_menu_id')
                    ->withPivot('label', 'sort_order')
                    ->orderByPivot('sort_order');
    }

    public function publicUrl(): string
    {
        return route('showcase.show', $this->slug);
    }
}
