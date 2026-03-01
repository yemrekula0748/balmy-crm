<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id', 'lang', 'respondent_token', 'ip_address', 'user_agent', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function survey()  { return $this->belongsTo(Survey::class); }
    public function answers() { return $this->hasMany(SurveyAnswer::class, 'response_id'); }
}
