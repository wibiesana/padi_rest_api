<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class FinancialTransactionResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transaction_date' => $this->transaction_date,
            'transaction_type' => $this->transaction_type,
            'category' => $this->category,
            'amount' => $this->amount,
            'description' => $this->description,
            'reference_id' => $this->reference_id,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
