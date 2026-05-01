<?php

namespace App\Models;

use App\Models\Base\ExamEvent as BaseExamEvent;

class ExamEvent extends BaseExamEvent
{

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    public function exams()
    {
        return $this->hasMany(\App\Models\Exam::class, 'exam_event_id');
    }

    public function questionBanks()
    {
        return $this->hasMany(\App\Models\QuestionBank::class, 'exam_event_id');
    }
}
