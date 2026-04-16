<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerUsbDevice extends Model
{
    protected $fillable = [
        'agent_computer_id',
        'friendly_name',
        'device_id',
        'type',
        'first_connected',
        'last_connected',
        'first_seen_at',
    ];

    protected $casts = [
        'first_connected' => 'datetime',
        'last_connected'  => 'datetime',
        'first_seen_at'   => 'datetime',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
