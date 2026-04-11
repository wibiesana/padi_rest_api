<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StudentClassResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'student_id' => $this->student_id,
            'class_id' => $this->class_id,

            // Relations
            'class' => $this->whenLoaded('class'),
            'student' => $this->whenLoaded('student'),

            // Flattened Fields
            'class_name' => $this->class['name'] ?? null,
            'student_name' => $this->student['name'] ?? null,

        ];
    }
}
