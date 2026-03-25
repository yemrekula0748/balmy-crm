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
        'last_boot_time'   => 'datetime',
        'reported_at'      => 'datetime',
        'last_seen_at'     => 'datetime',
        'os_install_date'  => 'date',
    ];

    /** Safely decode current_users regardless of encoding depth */
    public function getCurrentUsersAttribute($value): array
    {
        return self::decodeJsonArray($value);
    }

    public function setCurrentUsersAttribute($value): void
    {
        $this->attributes['current_users'] = is_array($value) ? json_encode($value) : $value;
    }

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

    public function mail()
    {
        return $this->hasOne(AgentComputerMail::class, 'agent_computer_id');
    }

    public function mailAccounts()
    {
        return $this->hasMany(AgentComputerMailAccount::class, 'agent_computer_id');
    }

    public function fileEvents()
    {
        return $this->hasMany(AgentComputerFileEvent::class, 'agent_computer_id');
    }

    public function browserHistory()
    {
        return $this->hasMany(AgentComputerBrowserHistory::class, 'agent_computer_id');
    }

    /** IP adresi — ilk aktif ağ adaptöründen */
    public function getIpAddressAttribute(): ?string
    {
        return $this->networkAdapters->where('is_active', true)->first()?->ip_address;
    }

    /** Disk warning accessor */
    public function getDiskWarningAttribute(): bool
    {
        return $this->disks->contains(fn($d) => ($d->usage_percent ?? 0) > 85);
    }

    /** AV disabled accessor */
    public function getAvDisabledAttribute(): bool
    {
        return $this->antivirus->isNotEmpty() && $this->antivirus->every(fn($a) => !$a->is_enabled);
    }

    /** Decode a value that may be a plain array, a JSON string, or a double-encoded JSON string */
    public static function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value === null || $value === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        // double-encoded: decoded is still a string
        if (is_string($decoded)) {
            $decoded2 = json_decode($decoded, true);
            return is_array($decoded2) ? $decoded2 : [];
        }
        return [];
    }
}
