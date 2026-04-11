<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ArticleLikeResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'article_id' => $this->article_id,
            'user_id' => $this->user_id,
            'status_like' => $this->status_like,

            // Relations
            'article' => $this->whenLoaded('article'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'article_name' => $this->article['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
