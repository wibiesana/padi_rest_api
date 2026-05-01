<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class TeacherUploadResultDetailResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'teacher_upload_result_detail_id' => $this->teacher_upload_result_detail_id,
            'upload_file' => $this->upload_file,
            'description' => $this->description,

            // Relations
            'teacherUploadResultDetail' => $this->whenLoaded('teacherUploadResultDetail'),

            // Flattened Fields
            'teacherUploadResultDetail_name' => $this->teacherUploadResultDetail['name'] ?? null,

        ];
    }
}
