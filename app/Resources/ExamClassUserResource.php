<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamClassUserResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,
            'exam_id' => $this->exam_id,
            'class_id' => $this->class_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'exam_giver_id' => $this->exam_giver_id,

            // Relations
            'class' => $this->whenLoaded('class'),
            'exam' => $this->whenLoaded('exam'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'class_name' => $this->class['name'] ?? null,
            'exam_name' => $this->exam['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
