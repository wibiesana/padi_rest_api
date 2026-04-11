<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamExaminerResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'teacher_id' => $this->teacher_id,

            // Relations
            'teacher' => $this->whenLoaded('teacher'),
            'exam' => $this->whenLoaded('exam'),

            // Flattened Fields
            'teacher_name' => $this->teacher->name,
            'exam_name' => $this->exam->name,

        ];
    }
}
