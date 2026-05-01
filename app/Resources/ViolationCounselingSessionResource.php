<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ViolationCounselingSessionResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'counselor_id' => $this->counselor_id,
            'session_date' => $this->session_date,
            'session_type' => $this->session_type,
            'topic' => $this->topic,
            'notes' => $this->notes,
            'follow_up_required' => $this->follow_up_required,
            'follow_up_date' => $this->follow_up_date,
            'status' => $this->status,
            'is_confidential' => $this->is_confidential,

            // Relations
            'counselor' => $this->whenLoaded('counselor'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'student' => $this->whenLoaded('student'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'counselor_name' => $this->counselor['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'student_name' => $this->student['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
