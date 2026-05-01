<?php

namespace App\Models;

use App\Models\Base\Exam as BaseModel;

class Exam extends BaseModel
{
    protected array $hidden = [];

    /**
     * Get classes associated with this exam
     */
    public function classes()
    {
        return $this->belongsToMany(
            \App\Models\Classroom::class,
            'exam_class',
            'exam_id',
            'class_id'
        );
    }
    /**
     * Get supervisors (pivot records)
     */
    public function supervisorAssignments()
    {
        return $this->hasMany(\App\Models\ExamSupervisor::class, 'exam_id');
    }

    /**
     * Get examiners (pivot records)
     */
    public function examinerAssignments()
    {
        return $this->hasMany(\App\Models\ExamExaminer::class, 'exam_id');
    }

    /**
     * Get supervisors (users)
     */
    public function supervisors()
    {
        return $this->belongsToMany(
            \App\Models\Teacher::class,
            'exam_supervisors',
            'exam_id',
            'teacher_id'
        );
    }

    /**
     * Get examiners (users)
     */
    public function examiners()
    {
        return $this->belongsToMany(
            \App\Models\Teacher::class,
            'exam_examiners',
            'exam_id',
            'teacher_id'
        );
    }
}
