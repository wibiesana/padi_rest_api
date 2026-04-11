<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ViolationTypeResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'score_penalty' => $this->score_penalty,
            'status' => $this->status,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,

        ];
    }
}
