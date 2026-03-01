<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSurveyResponse extends Model
{
    protected $table = 'staff_survey_responses';

    protected $fillable = [
        'survey_id', 'respondent_name', 'respondent_dept',
        'respondent_employee_id', 'lang', 'respondent_token',
        'ip_address', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function survey()  { return $this->belongsTo(StaffSurvey::class, 'survey_id'); }
    public function answers() { return $this->hasMany(StaffSurveyAnswer::class, 'response_id'); }
}
