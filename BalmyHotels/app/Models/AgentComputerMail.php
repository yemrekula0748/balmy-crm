<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerMail extends Model
{
    protected $table = 'agent_computer_mail';

    protected $fillable = [
        'agent_computer_id',
        'default_mail_client',
        'default_mail_progid',
        'is_new_outlook',
        'outlook_version',
    ];

    protected $casts = [
        'is_new_outlook' => 'boolean',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
