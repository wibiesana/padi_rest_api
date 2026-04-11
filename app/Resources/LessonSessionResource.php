<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class LessonSessionResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'teaching_schedule_id' => $this->teaching_schedule_id,
            'session_date' => $this->session_date,
            'teacher_id' => $this->teacher_id,
            'start_time_actual' => $this->start_time_actual,
            'end_time_actual' => $this->end_time_actual,
            'material' => $this->material,
            'note' => $this->note,
            'status' => $this->status,
            'allow_self_attendance' => (int)$this->allow_self_attendance,
            'qr_token' => $this->qr_token,
            'class_id' => $this->class_id ?? $this->teachingSchedule['classroom_id'] ?? $this->teachingSchedule['class_id'] ?? null,
            'classroom_id' => $this->class_id ?? $this->teachingSchedule['classroom_id'] ?? $this->teachingSchedule['class_id'] ?? null,

            // Relations
            'teachingSchedule' => $this->whenLoaded('teachingSchedule'),
            'teacher' => $this->whenLoaded('teacher'),

            // Flattened Fields
            'teachingSchedule_name' => $this->teachingSchedule['name'] ?? null,
            'teacher_name' => $this->teacher['name'] ?? null,
            'class_name' => $this->class_name ?? $this->teachingSchedule['classroom_name'] ?? $this->teachingSchedule['class_name'] ?? $this->teachingSchedule['classroom']['name'] ?? $this->teachingSchedule['class']['name'] ?? null,
            'subject_name' => $this->subject_name ?? $this->teachingSchedule['subject_name'] ?? $this->teachingSchedule['subject']['name'] ?? null,
            'start_time' => $this->start_time ?? $this->teachingSchedule['start_time'] ?? null,
            'end_time' => $this->end_time ?? $this->teachingSchedule['end_time'] ?? null,

        ];
    }
}
