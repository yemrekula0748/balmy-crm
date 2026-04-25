<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentComputerBrowserHistory extends Model
{
    protected $table = 'agent_computer_browser_history';

    protected $fillable = [
        'agent_computer_id',
        'username',
        'browser',
        'profile',
        'url',
        'title',
        'visit_time',
        'visit_count',
    ];

    protected $casts = [
        'visit_count' => 'integer',
    ];

    /**
     * Agent UTC olarak gönderiyor; Europe/Istanbul (UTC+3) olarak dönüştür.
     */
    public function getVisitTimeAttribute($value)
    {
        return \Carbon\Carbon::parse($value, 'UTC')->setTimezone(config('app.timezone'));
    }

    public function computer()
    {
        return $this->belongsTo(AgentComputer::class, 'agent_computer_id');
    }
}
