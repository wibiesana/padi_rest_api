<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamClassResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'exam_id' => $this->exam_id,
            'class_id' => $this->class_id,
            'semester_id' => $this->semester_id,

            // Relations
            'semester' => $this->whenLoaded('semester'),
            'class' => $this->whenLoaded('class'),
            'exam' => $this->whenLoaded('exam'),

            // Flattened Fields
            'semester_name' => $this->semester['name'] ?? null,
            'class_name' => $this->class['name'] ?? null,
            'exam_name' => $this->exam['name'] ?? null,

        ];
    }
}
