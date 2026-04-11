<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamResultAnswerResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'question_answer_id' => $this->question_answer_id,
            'exam_result_id' => $this->exam_result_id,
            'number' => $this->number,
            'answer' => $this->answer,
            'answer_score' => $this->answer_score,
            'is_essay' => $this->is_essay,

            // Relations
            'examResult' => $this->whenLoaded('examResult'),
            'questionAnswer' => $this->whenLoaded('questionAnswer'),
            'question' => $this->whenLoaded('question'),

            // Flattened Fields
            'examResult_name' => $this->examResult['name'] ?? null,
            'questionAnswer_name' => $this->questionAnswer['name'] ?? null,
            'question_name' => $this->question['name'] ?? null,

        ];
    }
}
