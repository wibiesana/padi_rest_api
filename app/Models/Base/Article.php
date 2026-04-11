<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Article extends ActiveRecord
{
    protected string $table = 'article';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'article', 'slug', 'article_preview', 'article_body', 'subject_id', 'like_count', 'dislike_count', 'view_count', 'rating_count', 'comment_count', 'tag', 'pin', 'publish', 'lock', 'publish_up', 'publish_down', 'storage_type', 'storage_path', 'file_size', 'file_type'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at, updated_at, created_by, updated_by
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';


    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function subject()
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function articleclass()
    {
        return $this->hasOne(\App\Models\ArticleClass::class, 'article_id');
    }

    public function articlecomments()
    {
        return $this->hasMany(\App\Models\ArticleComment::class, 'article_id');
    }

    public function articlelike()
    {
        return $this->hasOne(\App\Models\ArticleLike::class, 'article_id');
    }

    /**
     * Search with pagination and joins
     */
    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'article.created_by = users.id')
            ->leftJoin('subject AS subject', 'article.subject_id = subject.id')
            ->leftJoin('users AS users_updated_by', 'article.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'subject.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'article.storage_type', $keyword],
                ['LIKE', 'article.file_type', $keyword]
            ]);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.id DESC");
        }

        $result = $query->paginate($perPage, $page);

        if (!empty($result['data'])) {
            $this->loadRelations($result['data']);
            $result['data'] = $this->hideFields($result['data']);
        }

        return [
            'data' => $result['data'],
            'meta' => [
                'total' => (int)$result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'from' => ($result['current_page'] - 1) * $result['per_page'] + 1,
                'to' => min($result['current_page'] * $result['per_page'], $result['total'])
            ]
        ];
    }

    /**
     * Search article (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'article.created_by = users.id')
            ->leftJoin('subject AS subject', 'article.subject_id = subject.id')
            ->leftJoin('users AS users_updated_by', 'article.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'subject.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'article.storage_type', $keyword],
                ['LIKE', 'article.file_type', $keyword]
            ])
            ->limit(100);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}