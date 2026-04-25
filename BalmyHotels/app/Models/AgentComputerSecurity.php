<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerSecurity extends Model
{
    protected $table = 'agent_computer_security';

    protected $fillable = [
        'agent_computer_id',
        'rdp_enabled', 'uac_enabled',
        'firewall_domain', 'firewall_private', 'firewall_public',
        'auto_update', 'last_windows_update', 'bitlocker',
    ];

    protected $casts = [
        'rdp_enabled'      => 'boolean',
        'uac_enabled'      => 'boolean',
        'firewall_domain'  => 'boolean',
        'firewall_private' => 'boolean',
        'firewall_public'  => 'boolean',
    ];

    public function getBitlockerAttribute($value): array
    {
        return AgentComputer::decodeJsonArray($value);
    }

    public function setBitlockerAttribute($value): void
    {
        $this->attributes['bitlocker'] = is_array($value) ? json_encode($value) : $value;
    }

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
