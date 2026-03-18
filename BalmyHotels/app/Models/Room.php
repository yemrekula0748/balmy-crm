<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_number', 'floor', 'block',
        'room_type_id', 'extra_features', 'description', 'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bedTypes(): BelongsToMany
    {
        return $this->belongsToMany(BedType::class, 'bed_type_room');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
