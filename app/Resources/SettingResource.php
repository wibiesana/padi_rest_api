<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class SettingResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'setting' => $this->setting,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->is_active,
        ];
    }
}
