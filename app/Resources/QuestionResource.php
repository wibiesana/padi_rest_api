<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class QuestionResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_bank_id' => $this->question_bank_id,
            'type' => $this->type,
            'answer' => $this->answer,
            'options_json' => $this->options_json,
            'number_of_choice' => $this->number_of_choice,
            'answer_score' => $this->answer_score,
            'answer_discussion' => $this->answer_discussion,

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
