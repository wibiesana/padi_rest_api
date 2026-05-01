<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class StudentUploadResultResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_upload_id' => $this->student_upload_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'upload_file' => $this->upload_file,
            'description' => $this->description,

            // Relations
            'student' => $this->whenLoaded('student'),
            'studentUpload' => $this->whenLoaded('studentUpload'),

            // Flattened Fields
            'student_name' => $this->student['name'] ?? null,
            'studentUpload_name' => $this->studentUpload['name'] ?? null,

        ];
    }
}
