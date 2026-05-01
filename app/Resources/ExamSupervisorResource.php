<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamSupervisorResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'teacher_id' => $this->teacher_id,
            'classroom_id' => $this->classroom_id,
            'description' => $this->description,

            // Relations
            'teacher' => $this->whenLoaded('teacher'),
            'classroom' => $this->whenLoaded('classroom'),
            'exam' => $this->whenLoaded('exam'),

            // Flattened Fields
            'teacher_name' => $this->teacher?->name,
            'classroom_name' => $this->classroom?->name,
            'exam_name' => $this->exam?->name,

        ];
    }
}
