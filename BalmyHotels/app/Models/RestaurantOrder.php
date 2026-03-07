<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantOrder extends Model
{
    protected $fillable = ['table_session_id', 'note', 'created_by'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TableSession::class, 'table_session_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestaurantOrderItem::class, 'order_id');
    }
}
