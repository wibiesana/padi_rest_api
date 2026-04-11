<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StudentUploadResultDetailResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_upload_result_detail_id' => $this->student_upload_result_detail_id,
            'upload_file' => $this->upload_file,
            'description' => $this->description,

            // Relations
            'studentUploadResultDetail' => $this->whenLoaded('studentUploadResultDetail'),

            // Flattened Fields
            'studentUploadResultDetail_name' => $this->studentUploadResultDetail['name'] ?? null,

        ];
    }
}
