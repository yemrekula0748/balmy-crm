<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSurveyAnswer extends Model
{
    protected $table = 'staff_survey_answers';

    protected $fillable = [
        'response_id', 'question_id', 'answer',
    ];

    public function response() { return $this->belongsTo(StaffSurveyResponse::class, 'response_id'); }
    public function question() { return $this->belongsTo(StaffSurveyQuestion::class, 'question_id'); }
}
