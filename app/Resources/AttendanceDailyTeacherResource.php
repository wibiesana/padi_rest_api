<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AttendanceDailyTeacherResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'attendance_date' => $this->attendance_date,
            'status' => $this->status,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'note' => $this->note,

            // Relations
            'teacher' => $this->whenLoaded('teacher'),

            // Flattened Fields
            'teacher_name' => $this->teacher['name'] ?? null,

        ];
    }
}
