<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class PaymentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'billing_id' => $this->billing_id,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
            'received_by' => $this->received_by,

            // Relations
            'billing' => $this->whenLoaded('billing'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'receivedBy' => $this->whenLoaded('receivedBy'),

            // Flattened Fields
            'billing_name' => $this->billing['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'receivedBy_name' => $this->receivedBy['name'] ?? null,

        ];
    }
}
