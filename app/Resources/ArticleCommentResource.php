<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ArticleCommentResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'article_id' => $this->article_id,
            'like' => $this->like,
            'dislike' => $this->dislike,
            'rating' => $this->rating,

            // Relations
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'article' => $this->whenLoaded('article'),
            'createdBy' => $this->whenLoaded('createdBy'),

            // Flattened Fields
            'updatedBy_name' => $this->updatedBy['username'] ?? null,
            'article_name' => $this->article['name'] ?? null,
            'createdBy_name' => $this->createdBy['username'] ?? null,

        ];
    }
}
