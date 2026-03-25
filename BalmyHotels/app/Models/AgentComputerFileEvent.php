<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerFileEvent extends Model
{
    protected $table = 'agent_computer_file_events';

    protected $fillable = [
        'agent_computer_id',
        'event_id',
        'event_time',
        'subject_user',
        'subject_domain',
        'subject_logon_id',
        'object_name',
        'object_type',
        'process_name',
        'access_mask',
        'handle_id',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'event_id'   => 'integer',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
