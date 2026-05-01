<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class UserReadArticleResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,
            'class_id' => $this->class_id,
            'article_id' => $this->article_id,

            // Relations
            'article' => $this->whenLoaded('article'),
            'class' => $this->whenLoaded('class'),
            'user' => $this->whenLoaded('user'),

            // Flattened Fields
            'article_name' => $this->article['name'] ?? null,
            'class_name' => $this->class['name'] ?? null,
            'user_name' => $this->user['name'] ?? null,

        ];
    }
}
