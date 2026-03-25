<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputer extends Model
{
    protected $fillable = [
        'machine_guid', 'hostname',
        'os_product_name', 'os_version', 'os_release', 'os_build_number',
        'os_architecture', 'os_install_date', 'os_registered_owner', 'os_serial_number',
        'is_domain_joined', 'domain_name', 'workgroup_name', 'domain_controller',
        'current_users', 'last_boot_time', 'agent_version', 'reported_at', 'last_seen_at',
    ];

    protected $casts = [
        'is_domain_joined' => 'boolean',
        'current_users'    => 'array',
        'last_boot_time'   => 'datetime',
        'reported_at'      => 'datetime',
        'last_seen_at'     => 'datetime',
        'os_install_date'  => 'date',
    ];

    public function hardware()
    {
        return $this->hasOne(AgentComputerHardware::class, 'agent_computer_id');
    }

    public function security()
    {
        return $this->hasOne(AgentComputerSecurity::class, 'agent_computer_id');
    }

    public function networkAdapters()
    {
        return $this->hasMany(AgentComputerNetworkAdapter::class, 'agent_computer_id');
    }

    public function disks()
    {
        return $this->hasMany(AgentComputerDisk::class, 'agent_computer_id');
    }

    public function antivirus()
    {
        return $this->hasMany(AgentComputerAntivirus::class, 'agent_computer_id');
    }

    public function installedPrograms()
    {
        return $this->hasMany(AgentComputerInstalledProgram::class, 'agent_computer_id');
    }

    /** IP adresi — ilk aktif ağ adaptöründen */
    public function getIpAddressAttribute(): ?string
    {
        return $this->networkAdapters->where('is_active', true)->first()?->ip_address;
    }

    /** Herhangi bir disk >85% doluysa true */
    public function getDiskWarningAttribute(): bool
    {
        return $this->disks->contains(fn($d) => ($d->usage_percent ?? 0) > 85);
    }

    /** Tüm antivirüsler kapalıysa true */
    public function getAvDisabledAttribute(): bool
    {
        return $this->antivirus->isNotEmpty() && $this->antivirus->every(fn($a) => !$a->is_enabled);
    }
}
