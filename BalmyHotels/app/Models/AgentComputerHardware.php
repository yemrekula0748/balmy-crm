<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerHardware extends Model
{
    protected $table = 'agent_computer_hardware';

    protected $fillable = [
        'agent_computer_id',
        'cpu_name', 'cpu_cores_physical', 'cpu_cores_logical', 'cpu_speed_mhz', 'cpu_usage_percent',
        'total_ram_gb', 'available_ram_gb', 'ram_usage_percent', 'ram_slots',
        'motherboard', 'bios_version', 'bios_date',
    ];

    protected $casts = [];

    public function getRamSlotsAttribute($value): array
    {
        return AgentComputer::decodeJsonArray($value);
    }

    public function setRamSlotsAttribute($value): void
    {
        $this->attributes['ram_slots'] = is_array($value) ? json_encode($value) : $value;
    }

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
