<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class AssetBorrowing extends ActiveRecord
{
    protected string $table = 'asset_borrowing';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'asset_id', 'user_id', 'borrow_date', 'expected_return_date', 'actual_return_date', 'quantity', 'purpose', 'status', 'return_condition', 'notes', 'approved_by'
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


    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class, 'asset_id');
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
            ->leftJoin('users AS users', 'asset_borrowing.approved_by = users.id')
            ->leftJoin('asset AS asset', 'asset_borrowing.asset_id = asset.id')
            ->leftJoin('users AS users_user_id', 'asset_borrowing.user_id = users_user_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'asset.name', $keyword],
                ['LIKE', 'users_user_id.username', $keyword],
                ['LIKE', 'asset_borrowing.status', $keyword]
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
     * Search asset_borrowing (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'asset_borrowing.approved_by = users.id')
            ->leftJoin('asset AS asset', 'asset_borrowing.asset_id = asset.id')
            ->leftJoin('users AS users_user_id', 'asset_borrowing.user_id = users_user_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'asset.name', $keyword],
                ['LIKE', 'users_user_id.username', $keyword],
                ['LIKE', 'asset_borrowing.status', $keyword]
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