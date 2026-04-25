<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VncInput extends Model
{
    protected $table = 'vnc_inputs';

    protected $fillable = [
        'agent_computer_id',
        'events',
        'consumed',
    ];

    protected $casts = [
        'events'   => 'array',
        'consumed' => 'boolean',
    ];

    public function agentComputer(): BelongsTo
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
