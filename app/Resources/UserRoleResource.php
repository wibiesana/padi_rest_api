<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class UserRoleResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'is_active' => $this->is_active,

            // Relations
            'role' => $this->whenLoaded('role'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'role_name' => $this->role['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
