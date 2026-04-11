<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ArticleLike extends ActiveRecord
{
    protected string $table = 'article_like';
    protected string|array $primaryKey = ['article_id', 'user_id'];
    
    protected array $fillable = [
        'article_id', 'user_id', 'status_like'
    ];
    
    protected array $hidden = [];


    public function article()
    {
        return $this->belongsTo(\App\Models\Article::class, 'article_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
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
            ->leftJoin('article AS article', 'article_like.article_id = article.id')
            ->leftJoin('users AS users', 'article_like.user_id = users.id')
            ->where(['OR',
                ['LIKE', 'article.id', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'article_like.status_like', $keyword]
            ]);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.article_id DESC");
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
     * Search article_like (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('article AS article', 'article_like.article_id = article.id')
            ->leftJoin('users AS users', 'article_like.user_id = users.id')
            ->where(['OR',
                ['LIKE', 'article.id', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'article_like.status_like', $keyword]
            ])
            ->limit(100);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.article_id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}