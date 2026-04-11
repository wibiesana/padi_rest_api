<?php

namespace App\Models;

use App\Models\Base\Assignment as BaseModel;

class Assignment extends BaseModel
{
    /**
     * Many-to-Many relationship with Classroom
     */
    public function my_class()
    {
        return $this->belongsToMany(
            \App\Models\Classroom::class,
            'assignment_class',
            'assignment_id',
            'classroom_id'
        );
    }
}
