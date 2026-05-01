<?php

namespace App\Models;

use App\Models\Base\Article as BaseModel;

class Article extends BaseModel
{
    public function classes()
    {
        return $this->belongsToMany(\App\Models\Classroom::class, 'article_class', 'article_id', 'class_id');
    }
}
