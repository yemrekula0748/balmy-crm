<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class TableSession extends Model
{
    protected $fillable = [
        'restaurant_table_id', 'opened_by', 'opened_at', 'closed_at', 'is_open',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_open'   => 'boolean',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(RestaurantOrder::class);
    }

    public function allItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            RestaurantOrderItem::class,
            RestaurantOrder::class,
            'table_session_id',
            'order_id'
        );
    }

    public function durationMinutes(): int
    {
        $end = $this->closed_at ?? now();
        return (int) $this->opened_at->diffInMinutes($end);
    }

    public function durationFormatted(): string
    {
        $mins = $this->durationMinutes();
        $h    = intdiv($mins, 60);
        $m    = $mins % 60;
        return $h > 0 ? "{$h}s {$m}dk" : "{$m}dk";
    }

    public function totalAmount(): float
    {
        return $this->allItems()->get()->sum(fn($i) => ($i->unit_price ?? 0) * $i->quantity);
    }
}
