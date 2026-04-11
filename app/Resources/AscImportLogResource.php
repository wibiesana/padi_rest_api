<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class AscImportLogResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'import_date' => $this->import_date,
            'school_year_id' => $this->school_year_id,
            'semester_id' => $this->semester_id,
            'file_name' => $this->file_name,
            'total_lessons' => $this->total_lessons,
            'imported_lessons' => $this->imported_lessons,
            'total_periods' => $this->total_periods,
            'imported_periods' => $this->imported_periods,
            'status' => $this->status,
            'error_log' => $this->error_log,
            'notes' => $this->notes,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'schoolYear' => $this->whenLoaded('schoolYear'),
            'semester' => $this->whenLoaded('semester'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'schoolYear_name' => $this->schoolYear['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,

        ];
    }
}
