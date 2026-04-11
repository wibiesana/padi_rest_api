<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;
use App\Resources\StudentResource;

class ClassroomMemberResource extends Resource
{
    public function toArray($request): array
    {
        return [
            // Return virtual ID for pivot management
            'id' => $this->student_id . '_' . $this->class_id,
            'student_id' => $this->student_id,
            'class_id' => $this->class_id,

            // Relations
            'class' => $this->whenLoaded('class'),
            'student' => $this->whenLoaded('student') ? StudentResource::make($this->student)->resolve() : null,

            // Flattened Fields
            'class_name' => $this->class['name'] ?? null,
            'student_name' => $this->student['name'] ?? null,
            'student_nis' => $this->student['nis'] ?? null,
            'student_nisn' => $this->student['nisn'] ?? null,
            'student_gender' => $this->student['jenis_kelamin'] ?? $this->student['gender'] ?? null,
            'student_photo' => $this->student['photo'] ?? null,

        ];
    }
}
