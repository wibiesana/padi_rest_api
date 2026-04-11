<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AssignmentClassResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'assignment_id' => $this->assignment_id,
            'class_id' => $this->class_id,
            'semester_id' => $this->semester_id,

            // Relations
            'semester' => $this->whenLoaded('semester'),
            'assignment' => $this->whenLoaded('assignment'),
            'class' => $this->whenLoaded('class'),

            // Flattened Fields
            'semester_name' => $this->semester['name'] ?? null,
            'assignment_name' => $this->assignment['name'] ?? null,
            'class_name' => $this->class['name'] ?? null,

        ];
    }
}
