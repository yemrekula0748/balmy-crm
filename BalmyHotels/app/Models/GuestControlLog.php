<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestControlLog extends Model
{
    public const ACTION_CHECK_IN = 'check_in';
    public const ACTION_CHECK_OUT = 'check_out';

    public const ACTION_LABELS = [
        self::ACTION_CHECK_IN => 'Giris Yapti',
        self::ACTION_CHECK_OUT => 'Cikis Yapti',
    ];

    public const ACTION_COLORS = [
        self::ACTION_CHECK_IN => 'success',
        self::ACTION_CHECK_OUT => 'warning',
    ];

    protected $fillable = [
        'hotel_key',
        'hotel_name',
        'hotel_id',
        'branch_id',
        'room_no',
        'action_type',
        'selection_key',
        'stay_signature',
        'api_guest_id',
        'api_reservation_id',
        'api_reservation_name_id',
        'first_name',
        'last_name',
        'full_name',
        'phone',
        'email',
        'nationality',
        'national_id_no',
        'passport_no',
        'hotel_checkin_date',
        'hotel_checkout_date',
        'arrival_time',
        'departure_time',
        'payload',
        'created_by',
        'action_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'hotel_checkin_date' => 'date',
        'hotel_checkout_date' => 'date',
        'action_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action_type] ?? $this->action_type;
    }
}
