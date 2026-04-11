<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AssetResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'asset_code' => $this->asset_code,
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'purchase_date' => $this->purchase_date,
            'purchase_price' => $this->purchase_price,
            'condition' => $this->condition,
            'location' => $this->location,
            'quantity' => $this->quantity,
            'available_quantity' => $this->available_quantity,
            'is_borrowable' => $this->is_borrowable,
            'photo' => $this->photo,
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
