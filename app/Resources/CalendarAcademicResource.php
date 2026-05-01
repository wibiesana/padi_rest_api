<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class CalendarAcademicResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'color' => $this->color,
            'is_holiday' => $this->is_holiday,
            'status' => $this->status,

            // Relations

            // Flattened Fields

        ];
    }
}
