<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class BookBorrowing extends ActiveRecord
{
    protected string $table = 'book_borrowing';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'book_id', 'user_id', 'borrow_date', 'due_date', 'return_date', 'status', 'notes', 'fine_amount', 'processed_by'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at, updated_at
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';


    public function book()
    {
        return $this->belongsTo(\App\Models\Book::class, 'book_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'processed_by');
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
            ->leftJoin('book AS book', 'book_borrowing.book_id = book.id')
            ->leftJoin('users AS users', 'book_borrowing.processed_by = users.id')
            ->leftJoin('users AS users_user_id', 'book_borrowing.user_id = users_user_id.id')
            ->where(['OR',
                ['LIKE', 'book.title', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_user_id.username', $keyword],
                ['LIKE', 'book_borrowing.status', $keyword]
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
     * Search book_borrowing (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('book AS book', 'book_borrowing.book_id = book.id')
            ->leftJoin('users AS users', 'book_borrowing.processed_by = users.id')
            ->leftJoin('users AS users_user_id', 'book_borrowing.user_id = users_user_id.id')
            ->where(['OR',
                ['LIKE', 'book.title', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_user_id.username', $keyword],
                ['LIKE', 'book_borrowing.status', $keyword]
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