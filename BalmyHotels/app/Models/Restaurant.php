<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = ['branch_id', 'qr_menu_id', 'name', 'created_by'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function qrMenu(): BelongsTo
    {
        return $this->belongsTo(QrMenu::class, 'qr_menu_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class)->orderBy('sort_order')->orderBy('name');
    }
}
