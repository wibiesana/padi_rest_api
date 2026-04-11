<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class QuestionAnswerResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'answer' => $this->answer,
            'answer_value' => $this->answer_value,
            'question_id' => $this->question_id,

            // Relations
            'question' => $this->whenLoaded('question'),

            // Flattened Fields
            'question_name' => $this->question['name'] ?? null,

        ];
    }
}
