<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerProgramEvent extends Model
{
    protected $fillable = [
        'agent_computer_id',
        'event_type',
        'program_name',
        'version',
        'publisher',
        'detected_at',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
