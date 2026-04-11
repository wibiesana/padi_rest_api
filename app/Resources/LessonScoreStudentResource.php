<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class LessonScoreStudentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lesson_session_id' => $this->lesson_session_id,
            'student_id' => $this->student_id,
            'base_score' => $this->base_score,
            'penalty_score' => $this->penalty_score,
            'final_score' => $this->final_score,

            // Relations
            'lessonSession' => $this->whenLoaded('lessonSession'),
            'student' => $this->whenLoaded('student'),

            // Flattened Fields
            'lessonSession_name' => $this->lessonSession['name'] ?? null,
            'student_name' => $this->student['name'] ?? null,

        ];
    }
}
