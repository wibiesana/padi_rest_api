<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ArticleCommentLikeResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'article_coment_id' => $this->article_coment_id,
            'user_id' => $this->user_id,
            'status_like' => $this->status_like,

            // Relations
            'articleComent' => $this->whenLoaded('articleComent'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'articleComent_name' => $this->articleComent['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
