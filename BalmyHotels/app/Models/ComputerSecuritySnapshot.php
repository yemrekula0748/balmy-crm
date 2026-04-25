<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComputerSecuritySnapshot extends Model
{
    protected $fillable = [
        'computer_id',
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
        'new_services_24h',
        'account_changes_24h',
        'unexpected_shutdowns',
        'rdp_events_24h',
        'scheduled_tasks_24h',
        'admin_logins_1h',
        'service_crashes_24h',
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
        'new_services_24h'     => 'array',
        'account_changes_24h'  => 'array',
        'unexpected_shutdowns' => 'array',
        'rdp_events_24h'       => 'array',
        'scheduled_tasks_24h'  => 'array',
        'admin_logins_1h'      => 'array',
        'service_crashes_24h'  => 'array',
        'reported_at'          => 'datetime',
    ];

    public function computer()
    {
        return $this->belongsTo(Computer::class);
    }

    public function agentComputer()
    {
        return $this->belongsTo(AgentComputer::class);
    }
}
