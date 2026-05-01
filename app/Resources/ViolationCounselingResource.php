<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ViolationCounselingResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'student_violation_id' => $this->student_violation_id,
            'counseling_session_id' => $this->counseling_session_id,
            'action_taken' => $this->action_taken,
            'result' => $this->result,
            'parent_notified' => $this->parent_notified,
            'parent_notified_date' => $this->parent_notified_date,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'counselingSession' => $this->whenLoaded('counselingSession'),
            'studentViolation' => $this->whenLoaded('studentViolation'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'counselingSession_name' => $this->counselingSession['name'] ?? null,
            'studentViolation_name' => $this->studentViolation['name'] ?? null,

        ];
    }
}
