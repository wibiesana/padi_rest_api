<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ClassroomResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'grade_level_id' => $this->grade_level_id,
            'level' => $this->level,
            'department_id' => $this->department_id,
            'teacher_id' => $this->teacher_id,
            'semester_id' => $this->semester_id,
            'asc_id' => $this->asc_id,
            'asc_partner_id' => $this->asc_partner_id,
            'status' => $this->status,

            // Relations
            'department' => $this->whenLoaded('department'),
            'teacher' => $this->whenLoaded('teacher'),
            'semester' => $this->whenLoaded('semester'),
            'gradeLevel' => $this->whenLoaded('gradeLevel'),

            // Flattened Fields
            'department_name' => $this->department['name'] ?? null,
            'teacher_name' => $this->teacher['name'] ?? $this->teacher->name ?? $this->teacher_name ?? null,
            'semester_name' => $this->semester['name'] ?? null,
            'gradeLevel_name' => $this->gradeLevel['name'] ?? null,
            'students_count' => $this->students_count ?? 0,
        ];
    }
}
