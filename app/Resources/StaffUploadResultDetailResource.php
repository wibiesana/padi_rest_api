<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StaffUploadResultDetailResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'tu_upload_result_detail_id' => $this->tu_upload_result_detail_id,
            'upload_file' => $this->upload_file,
            'description' => $this->description,

            // Relations
            'tuUploadResultDetail' => $this->whenLoaded('tuUploadResultDetail'),

            // Flattened Fields
            'tuUploadResultDetail_name' => $this->tuUploadResultDetail['name'] ?? null,

        ];
    }
}
