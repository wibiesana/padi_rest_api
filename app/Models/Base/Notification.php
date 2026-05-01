<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Notification extends ActiveRecord
{
    protected string $table = 'notification';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'user_id', 'target_type', 'title', 'message', 'type', 'related_id', 'related_type', 'is_read', 'read_at', 'status'
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

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
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
            ->leftJoin('users AS users', 'notification.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'notification.updated_by = users_updated_by.id')
            ->leftJoin('users AS users_user_id', 'notification.user_id = users_user_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'users_user_id.username', $keyword],
                ['LIKE', 'notification.target_type', $keyword],
                ['LIKE', 'notification.title', $keyword],
                ['LIKE', 'notification.type', $keyword],
                ['LIKE', 'notification.related_type', $keyword],
                ['LIKE', 'notification.status', $keyword]
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
     * Search notification (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'notification.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'notification.updated_by = users_updated_by.id')
            ->leftJoin('users AS users_user_id', 'notification.user_id = users_user_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'users_user_id.username', $keyword],
                ['LIKE', 'notification.target_type', $keyword],
                ['LIKE', 'notification.title', $keyword],
                ['LIKE', 'notification.type', $keyword],
                ['LIKE', 'notification.related_type', $keyword],
                ['LIKE', 'notification.status', $keyword]
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