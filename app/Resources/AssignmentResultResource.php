<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AssignmentResultResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'class_id' => $this->class_id,
            'assignment_id' => $this->assignment_id,
            'description' => $this->description,
            'upload_file' => $this->upload_file,
            'score' => $this->score,
            'status' => $this->status,

            // Relations
            'assignment' => AssignmentResource::make($this->whenLoaded('assignment')),
            'class' => $this->whenLoaded('class'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'created_by_user' => $this->whenLoaded('createdBy'), // alias for consistency
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'assignment_name' => $this->assignment['name'] ?? null,
            'class_name' => $this->class['name'] ?? null,
            'createdBy_name' => $this->createdBy['teacher']['name'] ?? $this->createdBy['name'] ?? $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
