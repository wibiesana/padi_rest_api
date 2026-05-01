<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AssetBorrowingResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'user_id' => $this->user_id,
            'borrow_date' => $this->borrow_date,
            'expected_return_date' => $this->expected_return_date,
            'actual_return_date' => $this->actual_return_date,
            'quantity' => $this->quantity,
            'purpose' => $this->purpose,
            'status' => $this->status,
            'return_condition' => $this->return_condition,
            'notes' => $this->notes,
            'approved_by' => $this->approved_by,

            // Relations
            'approvedBy' => $this->whenLoaded('approvedBy'),
            'asset' => $this->whenLoaded('asset'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'approvedBy_name' => $this->approvedBy['name'] ?? null,
            'asset_name' => $this->asset['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
