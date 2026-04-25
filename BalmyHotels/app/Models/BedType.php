<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BedType extends Model
{
    protected $fillable = ['name', 'abbreviation'];

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'bed_type_room');
    }
}
