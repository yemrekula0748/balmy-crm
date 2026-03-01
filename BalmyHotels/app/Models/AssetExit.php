<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetExit extends Model
{
    const STATUSES = [
        'pending'  => 'Onay Bekliyor',
        'approved' => 'Onaylandı',
        'rejected' => 'Reddedildi',
        'returned' => 'İade Edildi',
    ];

    const STATUS_COLORS = [
        'pending'  => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        'returned' => 'secondary',
    ];

    const TAKER_TYPES = [
        'staff' => 'Personel',
        'guest' => 'Misafir',
    ];

    protected $fillable = [
        'asset_id', 'branch_id', 'taker_type',
        'staff_id', 'guest_name', 'guest_room', 'guest_id_no', 'guest_phone',
        'purpose', 'taken_at', 'expected_return_at', 'returned_at',
        'status', 'approved_by', 'approved_at', 'rejected_reason', 'notes',
    ];

    protected $casts = [
        'taken_at'          => 'datetime',
        'expected_return_at'=> 'datetime',
        'returned_at'       => 'datetime',
        'approved_at'       => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Kimin adına çıkış yapıldığını string olarak döndürür
     */
    public function takerName(): string
    {
        if ($this->taker_type === 'staff') {
            return $this->staff?->name ?? 'Bilinmiyor';
        }
        return ($this->guest_name ?? 'Misafir') . ($this->guest_room ? " (Oda: {$this->guest_room})" : '');
    }

    /**
     * İade gecikme var mı?
     */
    public function isOverdue(): bool
    {
        return $this->expected_return_at &&
               $this->expected_return_at->isPast() &&
               !$this->returned_at &&
               $this->status === 'approved';
    }
}
