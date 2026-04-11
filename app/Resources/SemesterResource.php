<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class SemesterResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_odd_semester' => $this->is_odd_semester,
            'school_year_id' => $this->school_year_id,
            'grade_level_id' => $this->grade_level_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
