<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class SubjectResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'asc_id' => $this->asc_id,
            'asc_partner_id' => $this->asc_partner_id,
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
