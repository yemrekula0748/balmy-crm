<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerInstalledProgram extends Model
{
    protected $table = 'agent_computer_installed_programs';

    protected $fillable = [
        'agent_computer_id',
        'name', 'version', 'publisher', 'install_date', 'install_location',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
