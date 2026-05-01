<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Payment extends ActiveRecord
{
    protected string $table = 'payment';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'billing_id', 'payment_date', 'amount', 'payment_method', 'reference_number', 'notes', 'received_by'
    ];
    
    protected array $hidden = [];

    /**
     * Audit fields detected: created_at, created_by
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';


    public function billing()
    {
        return $this->belongsTo(\App\Models\Billing::class, 'billing_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
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
            ->leftJoin('billing AS billing', 'payment.billing_id = billing.id')
            ->leftJoin('users AS users', 'payment.created_by = users.id')
            ->leftJoin('users AS users_received_by', 'payment.received_by = users_received_by.id')
            ->where(['OR',
                ['LIKE', 'billing.id', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_received_by.username', $keyword]
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
     * Search payment (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('billing AS billing', 'payment.billing_id = billing.id')
            ->leftJoin('users AS users', 'payment.created_by = users.id')
            ->leftJoin('users AS users_received_by', 'payment.received_by = users_received_by.id')
            ->where(['OR',
                ['LIKE', 'billing.id', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_received_by.username', $keyword]
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