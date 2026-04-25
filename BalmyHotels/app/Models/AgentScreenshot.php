<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AgentScreenshot extends Model
{
    protected $fillable = [
        'agent_computer_id',
        'command_id',
        'image_path',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function agentComputer(): BelongsTo
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }

    public function command(): BelongsTo
    {
        return $this->belongsTo(AgentComputerCommand::class, 'command_id');
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
