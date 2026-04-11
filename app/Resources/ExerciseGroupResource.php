<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExerciseGroupResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'exercise_id' => $this->exercise_id,
            'class_id' => $this->class_id,
            'semester_id' => $this->semester_id,

            // Relations
            'semester' => $this->whenLoaded('semester'),
            'class' => $this->whenLoaded('class'),
            'exercise' => $this->whenLoaded('exercise'),

            // Flattened Fields
            'semester_name' => $this->semester['name'] ?? null,
            'class_name' => $this->class['name'] ?? null,
            'exercise_name' => $this->exercise['name'] ?? null,

        ];
    }
}
