<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ClassSemesterResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'semester_id' => $this->semester_id,
            'homeroom_teacher_id' => $this->homeroom_teacher_id,
            'max_capacity' => $this->max_capacity,
            'current_students' => $this->current_students,
            'notes' => $this->notes,
            'is_active' => $this->is_active,

            // Relations
            'class' => $this->whenLoaded('class'),
            'homeroomTeacher' => $this->whenLoaded('homeroomTeacher'),
            'semester' => $this->whenLoaded('semester'),

            // Flattened Fields
            'class_name' => $this->class['name'] ?? null,
            'homeroomTeacher_name' => $this->homeroomTeacher['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,

        ];
    }
}
