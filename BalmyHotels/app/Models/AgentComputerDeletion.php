<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerDeletion extends Model
{
    protected $fillable = [
        'agent_computer_id',
        'name',
        'path',
        'directory',
        'size',
        'modified_at',
        'deleted_at',
    ];

    protected $casts = [
        'modified_at' => 'datetime',
        'deleted_at'  => 'datetime',
        'size'        => 'integer',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }

    /** Boyutu okunabilir formata çevirir (KB / MB / GB) */
    public function getHumanSizeAttribute(): string
    {
        if ($this->size === null) return '—';
        $bytes = $this->size;
        if ($bytes < 1024)         return $bytes . ' B';
        if ($bytes < 1048576)      return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824)   return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }
}
