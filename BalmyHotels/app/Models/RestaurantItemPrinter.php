<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantItemPrinter extends Model
{
    protected $fillable = ['restaurant_id', 'qr_menu_item_id', 'printer_id'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(QrMenuItem::class, 'qr_menu_item_id');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }
}
