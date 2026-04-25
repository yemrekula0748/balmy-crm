<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationGuest extends Model
{
    protected $fillable = [
        'reservation_id', 'first_name', 'last_name',
        'nationality', 'gender', 'birth_date',
        'id_no', 'passport_no', 'phone', 'email',
        'vehicle_plate', 'is_primary',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_primary' => 'boolean',
    ];

    const GENDERS = [
        'male'   => 'Erkek',
        'female' => 'Kadın',
        'other'  => 'Diğer',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
