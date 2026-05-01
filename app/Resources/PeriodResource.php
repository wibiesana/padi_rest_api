<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class PeriodResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'period_number' => $this->period_number,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'school_year_id' => $this->school_year_id,
            'is_active' => $this->is_active,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'schoolYear' => $this->whenLoaded('schoolYear'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'schoolYear_name' => $this->schoolYear['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
