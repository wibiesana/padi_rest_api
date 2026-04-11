<?php

namespace App\Resources;

use Wibiesana\Padi\Core\Resource;

class ArticleResource extends Resource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'article' => $this->article,
            'slug' => $this->slug,
            'article_preview' => $this->article_preview,
            'article_body' => $this->article_body,
            'subject_id' => $this->subject_id,
            // 'like_count' => $this->like_count,
            // 'dislike_count' => $this->dislike_count,
            // 'view_count' => $this->view_count,
            // 'rating_count' => $this->rating_count,
            // 'comment_count' => $this->comment_count,
            // 'tag' => $this->tag,
            // 'pin' => $this->pin,
            // 'publish' => $this->publish,
            // 'lock' => $this->lock,
            // 'publish_up' => $this->publish_up,
            // 'publish_down' => $this->publish_down,
            // 'is_for_group' => $this->is_for_group,
            // 'storage_type' => $this->storage_type,
            // 'storage_path' => $this->storage_path,
            // 'file_size' => $this->file_size,
            // 'file_type' => $this->file_type,

            // Relations
            'createdBy' => $this->whenLoaded('createdBy'),
            'subject' => $this->whenLoaded('subject'),
            'updatedBy' => $this->whenLoaded('updatedBy'),
            'class' => $this->whenLoaded('classes'),

            // Flattened Fields
            // 'createdBy_name' => $this->createdBy['username'] ?? null,
            // 'subject_name' => $this->subject['name'] ?? null,
            // 'updatedBy_name' => $this->updatedBy['username'] ?? null,

        ];
    }
}
