<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StaffUploadResultResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tu_upload_id' => $this->tu_upload_id,
            'tu_id' => $this->tu_id,
            'status' => $this->status,
            'upload_file' => $this->upload_file,
            'description' => $this->description,

            // Relations
            'tu' => $this->whenLoaded('tu'),
            'tuUpload' => $this->whenLoaded('tuUpload'),

            // Flattened Fields
            'tu_name' => $this->tu['name'] ?? null,
            'tuUpload_name' => $this->tuUpload['name'] ?? null,

        ];
    }
}
