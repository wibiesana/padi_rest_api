<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AssignmentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
            'year_active_id' => $this->year_active_id,
            'semester_id' => $this->semester_id,
            'storage_type' => $this->storage_type,
            'storage_path' => $this->storage_path,
            'file_size' => $this->file_size,
            'file_type' => $this->file_type,
            'subject_id' => $this->subject_id,
            'class_ids' => $this->whenLoaded('my_class', function ($val) {
                $ids = [];
                foreach ($val as $c) {
                    $ids[] = (int) (is_array($c) ? ($c['id'] ?? 0) : ($c->id ?? 0));
                }
                return array_values(array_unique($ids));
            }, []),

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'created_by_user' => $this->whenLoaded('createdBy'), // alias for frontend consistency
            'subject' => $this->whenLoaded('subject'),
            'semester' => $this->whenLoaded('semester'),
            'my_class' => $this->whenLoaded('my_class'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'yearActive' => $this->whenLoaded('yearActive'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['teacher']['name'] ?? $this->createdBy['name'] ?? $this->createdBy['username'] ?? null,
            'subject_name' => $this->subject['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,
            'yearActive_name' => $this->yearActive['name'] ?? null,

        ];
    }
}
