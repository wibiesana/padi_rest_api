<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExerciseResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'question_bank_id' => $this->question_bank_id,
            'show_result' => $this->show_result,
            'percentage_mc_value' => $this->percentage_mc_value,
            'percentage_essay_value' => $this->percentage_essay_value,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_use_token' => $this->is_use_token,
            'token' => $this->token,
            'view_count' => $this->view_count,
            'like_count' => $this->like_count,
            'comment_count' => $this->comment_count,
            'is_for_group' => $this->is_for_group,
            'is_active' => $this->is_active,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'questionBank' => $this->whenLoaded('questionBank'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'questionBank_name' => $this->questionBank['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
