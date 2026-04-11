<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ArticleClassResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'article_id' => $this->article_id,
            'class_id' => $this->class_id,
            'semester_id' => $this->semester_id,

            // Relations
            'class' => $this->whenLoaded('class'),
            'semester' => $this->whenLoaded('semester'),
            'article' => $this->whenLoaded('article'),

            // Flattened Fields
            'class_name' => $this->class['name'] ?? null,
            'semester_name' => $this->semester['name'] ?? null,
            'article_name' => $this->article['name'] ?? null,

        ];
    }
}
