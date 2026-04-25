<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerAntivirus extends Model
{
    protected $table = 'agent_computer_antivirus';

    protected $fillable = [
        'agent_computer_id',
        'product_name', 'product_state_raw', 'is_enabled', 'is_up_to_date', 'timestamp',
    ];

    protected $casts = [
        'is_enabled'    => 'boolean',
        'is_up_to_date' => 'boolean',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
