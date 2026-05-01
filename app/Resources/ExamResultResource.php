<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExamResultResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam_status_id' => $this->exam_status_id,
            'contain_essay' => $this->contain_essay,
            'attemp' => $this->attemp,
            'essay_result' => $this->essay_result,
            'mc_result' => $this->mc_result,
            'total_result' => $this->total_result,
            'class_id' => $this->class_id,
            'exam_giver_id' => $this->exam_giver_id,
            'answer_score_list' => $this->answer_score_list,
            'status' => $this->status,

            // Relations
            'class' => $this->whenLoaded('class'),
            'createdBy' => $this->whenLoaded('createdBy'),
            'examGiver' => $this->whenLoaded('examGiver'),
            'exam' => $this->whenLoaded('exam'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'class_name' => $this->class['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'examGiver_name' => $this->examGiver['name'] ?? null,
            'exam_name' => $this->exam['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
