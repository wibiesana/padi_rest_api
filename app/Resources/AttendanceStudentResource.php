<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AttendanceStudentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lesson_session_id' => $this->lesson_session_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'note' => $this->note,

            // Relations
            'lessonSession' => $this->whenLoaded('lessonSession'),
            'student' => $this->whenLoaded('student'),

            // Flattened Fields
            'lessonSession_name' => $this->lessonSession['name'] ?? null,
            'student_name' => $this->student['name'] ?? null,

        ];
    }
}
