<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class NotificationResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'target_type' => $this->target_type,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'related_id' => $this->related_id,
            'related_type' => $this->related_type,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
