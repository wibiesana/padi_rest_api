<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class TeacherUploadResultResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'teacher_upload_id' => $this->teacher_upload_id,
            'teacher_id' => $this->teacher_id,
            'status' => $this->status,
            'upload_file' => $this->upload_file,
            'description' => $this->description,

            // Relations
            'teacher' => $this->whenLoaded('teacher'),
            'teacherUpload' => $this->whenLoaded('teacherUpload'),

            // Flattened Fields
            'teacher_name' => $this->teacher['name'] ?? null,
            'teacherUpload_name' => $this->teacherUpload['name'] ?? null,

        ];
    }
}
