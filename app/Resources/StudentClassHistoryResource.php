<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StudentClassHistoryResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'class_semester_id' => $this->class_semester_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'roll_number' => $this->roll_number,

            // Relations
            'classSemester' => $this->whenLoaded('classSemester'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'student' => $this->whenLoaded('student'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'classSemester_name' => $this->classSemester['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'student_name' => $this->student['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
