<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class TeachingScheduleResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'classroom_id' => $this->classroom_id ?? $this->class_id,
            'class_id' => $this->classroom_id ?? $this->class_id,
            'class_semester_id' => $this->class_semester_id,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
            'semester_id' => $this->semester_id,
            'day_of_week' => $this->day_of_week,
            'period_id' => $this->period_id,
            'period_number' => $this->period_number,
            'period_number_end' => $this->period_number_end,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'classroom_code' => $this->classroom_code,
            'asc_lesson_id' => $this->asc_lesson_id,
            'asc_subject_id' => $this->asc_subject_id,
            'asc_teacher_id' => $this->asc_teacher_id,
            'asc_class_id' => $this->asc_class_id,
            'periods_per_week' => $this->periods_per_week,
            'status' => $this->status,

            // Relations
            'classroom' => $this->whenLoaded('classroom') ?? $this->whenLoaded('class'),
            'classSemester' => $this->whenLoaded('classSemester'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'period' => $this->whenLoaded('period'),
            'semester' => $this->whenLoaded('semester'),
            'subject' => $this->whenLoaded('subject'),
            'teacher' => $this->whenLoaded('teacher'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'classroom_name' => $this->classroom['name'] ?? $this->class['name'] ?? null,
            'class_name' => $this->classroom['name'] ?? $this->class['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'period_name' => $this->period['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,
            'subject_name' => $this->subject['name'] ?? null,
            'teacher_name' => $this->teacher['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
