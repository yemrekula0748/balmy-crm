<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VncFrame extends Model
{
    protected $table = 'vnc_frames';

    // Only has updated_at; no created_at
    const CREATED_AT = null;

    protected $fillable = [
        'agent_computer_id',
        'image_data',
        'screen_w',
        'screen_h',
        'seq',
    ];

    public function agentComputer(): BelongsTo
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
