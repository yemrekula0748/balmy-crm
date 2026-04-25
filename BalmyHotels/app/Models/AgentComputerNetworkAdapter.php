<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerNetworkAdapter extends Model
{
    protected $table = 'agent_computer_network_adapters';

    protected $fillable = [
        'agent_computer_id',
        'adapter_name', 'mac_address', 'ip_address', 'ip_address_v6',
        'subnet_mask', 'gateway', 'dns_servers', 'dhcp_enabled', 'dhcp_server', 'is_active',
    ];

    protected $casts = [
        'dhcp_enabled' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function getDnsServersAttribute($value): array
    {
        return AgentComputer::decodeJsonArray($value);
    }

    public function setDnsServersAttribute($value): void
    {
        $this->attributes['dns_servers'] = is_array($value) ? json_encode($value) : $value;
    }

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
