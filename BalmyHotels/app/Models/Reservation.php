<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    protected $fillable = [
        'reservation_no', 'agency_id', 'agency_contract_id',
        'room_type_id', 'room_id',
        'check_in_date', 'check_out_date',
        'check_in_time', 'check_out_time',
        'adults', 'children', 'babies',
        'nationality', 'voucher_no', 'status', 'created_by',
    ];

    protected $casts = [
        'check_in_date'  => 'date',
        'check_out_date' => 'date',
    ];

    const STATUSES = [
        'pending'      => 'Beklemede',
        'confirmed'    => 'Onaylandı',
        'checked_in'   => 'Giriş Yapıldı',
        'checked_out'  => 'Çıkış Yapıldı',
        'cancelled'    => 'İptal',
    ];

    const STATUS_COLORS = [
        'pending'     => 'warning',
        'confirmed'   => 'primary',
        'checked_in'  => 'success',
        'checked_out' => 'secondary',
        'cancelled'   => 'danger',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(AgencyContract::class, 'agency_contract_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(ReservationGuest::class);
    }

    public function nightCount(): int
    {
        return (int) $this->check_in_date->diffInDays($this->check_out_date);
    }

    public static function generateNo(): string
    {
        $last = static::orderBy('id', 'desc')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'RES-' . date('Y') . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
