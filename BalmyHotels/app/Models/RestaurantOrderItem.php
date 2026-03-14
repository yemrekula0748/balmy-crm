<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantOrderItem extends Model
{
    protected $fillable = [
        'order_id', 'qr_menu_item_id', 'item_name', 'unit_price', 'quantity', 'note',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'quantity'   => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(RestaurantOrder::class, 'order_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(QrMenuItem::class, 'qr_menu_item_id');
    }

    public function lineTotal(): float
    {
        return ($this->unit_price ?? 0) * $this->quantity;
    }
}
