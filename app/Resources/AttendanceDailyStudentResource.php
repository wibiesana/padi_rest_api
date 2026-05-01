<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AttendanceDailyStudentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'attendance_date' => $this->attendance_date,
            'status' => $this->status,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'note' => $this->note,

            // Relations
            'student' => $this->whenLoaded('student'),

            // Flattened Fields
            'student_name' => $this->student['name'] ?? null,
            'class_name' => $this->class_name,

        ];
    }
}
