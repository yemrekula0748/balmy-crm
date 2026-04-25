<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerDisk extends Model
{
    protected $table = 'agent_computer_disks';

    protected $fillable = [
        'agent_computer_id',
        'drive_letter', 'mount_point', 'filesystem', 'label',
        'total_space_gb', 'used_space_gb', 'free_space_gb', 'usage_percent',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
