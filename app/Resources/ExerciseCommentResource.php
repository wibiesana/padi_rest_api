<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ExerciseCommentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'exercise_id' => $this->exercise_id,
            'like' => $this->like,
            'dislike' => $this->dislike,
            'rating' => $this->rating,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'exercise' => $this->whenLoaded('exercise'),
            'updatedBy' => $this->whenLoaded('updatedBy'),

            // Flattened Fields
            'createdBy_name' => $this->createdBy['username'] ?? null,
            'exercise_name' => $this->exercise['name'] ?? null,
            'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
