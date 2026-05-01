<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class UserParentStudent extends ActiveRecord
{
    protected string $table = 'user_parent_student';
    protected string|array $primaryKey = ['parent_user_id', 'student_user_id'];
    
    protected array $fillable = [
        'parent_user_id', 'student_user_id', 'relation_type', 'is_primary'
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


    public function parentUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'parent_user_id');
    }

    public function studentUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'student_user_id');
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
            ->leftJoin('users AS users', 'user_parent_student.parent_user_id = users.id')
            ->leftJoin('users AS users_student_user_id', 'user_parent_student.student_user_id = users_student_user_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_student_user_id.username', $keyword],
                ['LIKE', 'user_parent_student.relation_type', $keyword]
            ]);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.parent_user_id DESC");
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
     * Search user_parent_student (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'user_parent_student.parent_user_id = users.id')
            ->leftJoin('users AS users_student_user_id', 'user_parent_student.student_user_id = users_student_user_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_student_user_id.username', $keyword],
                ['LIKE', 'user_parent_student.relation_type', $keyword]
            ])
            ->limit(100);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.parent_user_id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}