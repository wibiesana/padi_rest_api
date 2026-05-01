<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class UserParentStudentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'parent_user_id' => $this->parent_user_id,
            'student_user_id' => $this->student_user_id,
            'relation_type' => $this->relation_type,
            'is_primary' => $this->is_primary,

            // Relations
            'parentUser' => $this->whenLoaded('parentUser'),
            'studentUser' => $this->whenLoaded('studentUser'),

            // Flattened Fields
            'parentUser_name' => $this->parentUser['name'] ?? null,
            'studentUser_name' => $this->studentUser['name'] ?? null,

        ];
    }
}
