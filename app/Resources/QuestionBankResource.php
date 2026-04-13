<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class QuestionBankResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'teacher_id' => $this->teacher_id,
            'question_count' => $this->question_count,
            'answer_score_list' => $this->answer_score_list,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'teacher' => $this->whenLoaded('teacher'),

            // Flattened Fields for Display
            'teacher_name' => $this->teacher['name'] ?? $this->teacher_name ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? $this->createdBy_name ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? $this->updatedBy_name ?? null,
            'author' => $this->teacher['name'] ?? $this->teacher_name ?? null,
            'creator' => $this->createdBy['username'] ?? $this->createdBy_name ?? null,
            'editor' => $this->updatedBy['username'] ?? $this->updatedBy_name ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
