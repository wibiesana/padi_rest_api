<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class DepartmentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'teacher_id' => $this->teacher_id,
            'semester_id' => $this->semester_id,
            'status' => $this->status,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'teacher' => $this->whenLoaded('teacher'),
            'semester' => $this->whenLoaded('semester'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'teacher_name' => $this->teacher['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
