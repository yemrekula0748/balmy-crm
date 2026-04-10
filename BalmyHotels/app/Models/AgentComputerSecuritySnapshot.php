<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerSecuritySnapshot extends Model
{
    protected $fillable = [
        'agent_computer_id',
        'alert_count',
        'defender_threats',
        'shadow_copy_count',
        'failed_logins_1h',
        'account_lockouts_1h',
        'suspicious_processes',
        'open_ports',
        'usb_history',
        'smb_signing',
        'local_admins',
        'windows_update',
        'reported_at',
    ];

    protected $casts = [
        'defender_threats'     => 'array',
        'account_lockouts_1h'  => 'array',
        'suspicious_processes' => 'array',
        'open_ports'           => 'array',
        'usb_history'          => 'array',
        'smb_signing'          => 'array',
        'local_admins'         => 'array',
        'windows_update'       => 'array',
        'reported_at'          => 'datetime',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
