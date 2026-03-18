<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RoomType extends Model
{
    protected $fillable = [
        'name', 'code', 'total_rooms',
        'max_adults', 'max_babies', 'max_children',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(AgencyContract::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
