<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Billing extends ActiveRecord
{
    protected string $table = 'billing';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'student_id', 'billing_type', 'amount', 'due_date', 'status', 'description', 'semester_id'
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

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'billing_id');
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
            ->leftJoin('users AS users', 'billing.created_by = users.id')
            ->leftJoin('semester AS semester', 'billing.semester_id = semester.id')
            ->leftJoin('student AS student', 'billing.student_id = student.id')
            ->leftJoin('users AS users_updated_by', 'billing.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'billing.billing_type', $keyword],
                ['LIKE', 'billing.status', $keyword],
                ['LIKE', 'billing.description', $keyword]
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
     * Search billing (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'billing.created_by = users.id')
            ->leftJoin('semester AS semester', 'billing.semester_id = semester.id')
            ->leftJoin('student AS student', 'billing.student_id = student.id')
            ->leftJoin('users AS users_updated_by', 'billing.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'billing.billing_type', $keyword],
                ['LIKE', 'billing.status', $keyword],
                ['LIKE', 'billing.description', $keyword]
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