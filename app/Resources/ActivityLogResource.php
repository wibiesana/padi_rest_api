<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ActivityLogResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'action' => $this->action,
            'module' => $this->module,
            'record_id' => $this->record_id,
            'description' => $this->description,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,

            // Relations
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'user_name' => $this->user['username'] ?? null,

        ];
    }
}
