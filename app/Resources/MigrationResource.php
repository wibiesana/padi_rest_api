<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class MigrationResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'version' => $this->version,
            'apply_time' => $this->apply_time,

            // Relations

            // Flattened Fields

        ];
    }
}
