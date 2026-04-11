<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ArticleComment extends ActiveRecord
{
    protected string $table = 'article_comment';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'comment', 'article_id', 'like', 'dislike', 'rating'
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


    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function article()
    {
        return $this->belongsTo(\App\Models\Article::class, 'article_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function articlecommentlike()
    {
        return $this->hasOne(\App\Models\ArticleCommentLike::class, 'article_coment_id');
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
            ->leftJoin('users AS users', 'article_comment.updated_by = users.id')
            ->leftJoin('article AS article', 'article_comment.article_id = article.id')
            ->leftJoin('users AS users_created_by', 'article_comment.created_by = users_created_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'article.id', $keyword],
                ['LIKE', 'users_created_by.username', $keyword]
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
     * Search article_comment (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'article_comment.updated_by = users.id')
            ->leftJoin('article AS article', 'article_comment.article_id = article.id')
            ->leftJoin('users AS users_created_by', 'article_comment.created_by = users_created_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'article.id', $keyword],
                ['LIKE', 'users_created_by.username', $keyword]
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