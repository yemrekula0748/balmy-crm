<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgencyContract extends Model
{
    protected $fillable = [
        'contract_code', 'agency_id', 'room_type_id',
        'start_date', 'end_date',
        'price_single', 'price_double', 'price_triple', 'price_quad',
        'price_baby1', 'price_baby2', 'price_child1', 'price_child2',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'agency_contract_id');
    }

    public static function generateCode(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'KON-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function isActive(): bool
    {
        $today = now()->toDateString();
        return $this->start_date->toDateString() <= $today
            && $this->end_date->toDateString() >= $today;
    }
}
