<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AttendanceTeacherResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lesson_session_id' => $this->lesson_session_id,
            'teacher_id' => $this->teacher_id,
            'status' => $this->status,
            'note' => $this->note,
            'qr_code' => $this->qr_code,
            'qr_expires_at' => $this->qr_expires_at,

            // Relations
            'lessonSession' => $this->whenLoaded('lessonSession'),
            'teacher' => $this->whenLoaded('teacher'),

            // Flattened Fields
            'lessonSession_name' => $this->lessonSession['name'] ?? null,
            'teacher_name' => $this->teacher['name'] ?? null,

        ];
    }
}
