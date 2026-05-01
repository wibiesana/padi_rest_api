<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StudentViolationResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'lesson_session_id' => $this->lesson_session_id,
            'violation_type_id' => $this->violation_type_id,
            'quantity' => $this->quantity,
            'total_penalty' => $this->total_penalty,
            'note' => $this->note,

            // Relations
            'lessonSession' => $this->whenLoaded('lessonSession'),
            'student' => $this->whenLoaded('student'),
            'violationType' => $this->whenLoaded('violationType'),
            'createdBy' => $this->whenLoaded('createdBy'),

            // Flattened Fields
            'lessonSession_name' => $this->lessonSession['name'] ?? null,
            'student_name' => $this->student['name'] ?? null,
            'violationType_name' => $this->violationType['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,

        ];
    }
}
