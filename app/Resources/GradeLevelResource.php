<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class GradeLevelResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'level_type' => $this->level_type,
            'sequence' => $this->sequence,
            'description' => $this->description,
            'status' => $this->is_active,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
