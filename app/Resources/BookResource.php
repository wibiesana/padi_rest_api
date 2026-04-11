<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class BookResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'publication_year' => $this->publication_year,
            'category' => $this->category,
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'location' => $this->location,
            'cover_image' => $this->cover_image,
            'description' => $this->description,
            'is_active' => $this->is_active,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
