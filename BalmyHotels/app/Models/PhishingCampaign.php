<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhishingCampaign extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'name',
        'status',
        'redirect_url',
        'created_by',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function targets()
    {
        return $this->hasMany(PhishingTarget::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpen(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->started_at && $this->started_at->isFuture()) {
            return false;
        }

        return !$this->ended_at || $this->ended_at->isFuture();
    }
}
