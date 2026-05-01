<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AscMappingResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'asc_id' => $this->asc_id,
            'asc_name' => $this->asc_name,
            'asc_short' => $this->asc_short,
            'local_id' => $this->local_id,
            'local_table' => $this->local_table,
            'status' => $this->is_active,
            'last_sync' => $this->last_sync,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
