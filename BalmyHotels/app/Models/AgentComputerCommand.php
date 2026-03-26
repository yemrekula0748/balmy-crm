<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentComputerCommand extends Model
{
    protected $table = 'agent_computer_commands';

    protected $fillable = [
        'agent_computer_id',
        'type',
        'payload',
        'status',
        'output',
        'success',
        'executed_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'success'     => 'boolean',
        'executed_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public const TYPES = [
        'shutdown' => 'Kapat',
        'restart'  => 'Yeniden Başlat',
        'logoff'   => 'Oturumu Kapat',
        'msgbox'   => 'Mesaj Göster',
        'cmd'      => 'Komut Çalıştır',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'warning',
        'sent'      => 'info',
        'completed' => 'success',
        'failed'    => 'danger',
    ];

    public const STATUS_LABELS = [
        'pending'   => 'Bekliyor',
        'sent'      => 'Gönderildi',
        'completed' => 'Tamamlandı',
        'failed'    => 'Başarısız',
    ];

    public function computer(): BelongsTo
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
