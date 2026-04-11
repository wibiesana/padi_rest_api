<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class TeacherUploadResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'target' => $this->target,
            'description' => $this->description,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'assign_to' => $this->assign_to,
            'semester_id' => $this->semester_id,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'semester' => $this->whenLoaded('semester'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,

        ];
    }
}
