<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class BookBorrowingResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'user_id' => $this->user_id,
            'borrow_date' => $this->borrow_date,
            'due_date' => $this->due_date,
            'return_date' => $this->return_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'fine_amount' => $this->fine_amount,
            'processed_by' => $this->processed_by,

            // Relations
            'book' => $this->whenLoaded('book'),
            'processedBy' => $this->whenLoaded('processedBy'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'book_name' => $this->book['name'] ?? null,
            'processedBy_name' => $this->processedBy['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
