<?php

namespace App\Models;

use App\Models\Base\ExamResult as BaseModel;

class ExamResult extends BaseModel
{
    protected array $fillable = [
        'status',
        'contain_essay',
        'attemp',
        'essay_result',
        'mc_result',
        'total_result',
        'answer_score_list',
        'duration',
        'exam_id',
        'student_id',
        'answer_list',
        'start_working',
        'created_at',
        'updated_at'
    ];

    // Add custom model logic here
}
