<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerMailAccount extends Model
{
    protected $table = 'agent_computer_mail_accounts';

    protected $fillable = [
        'agent_computer_id',
        'smtp_address',
        'display_name',
        'account_type',
        'exchange_server',
        'source',
    ];

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
