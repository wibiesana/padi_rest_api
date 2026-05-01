<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Violation extends ActiveRecord
{
    protected string $table = 'violation';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'student_id', 'lesson_session_id', 'violation_type_id', 'quantity', 'total_penalty', 'note'
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


    public function lessonSession()
    {
        return $this->belongsTo(\App\Models\LessonSession::class, 'lesson_session_id');
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\User::class, 'student_id');
    }

    public function violationType()
    {
        return $this->belongsTo(\App\Models\ViolationType::class, 'violation_type_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function violationcounselings()
    {
        return $this->hasMany(\App\Models\ViolationCounseling::class, 'student_violation_id');
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
            ->leftJoin('lesson_session AS lesson_session', 'violation.lesson_session_id = lesson_session.id')
            ->leftJoin('users AS users', 'violation.student_id = users.id')
            ->leftJoin('violation_type AS violation_type', 'violation.violation_type_id = violation_type.id')
            ->leftJoin('users AS users_created_by', 'violation.created_by = users_created_by.id')
            ->where(['OR',
                ['LIKE', 'lesson_session.id', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'violation_type.name', $keyword],
                ['LIKE', 'users_created_by.username', $keyword],
                ['LIKE', 'violation.violation_type_id', $keyword]
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
     * Search violation (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('lesson_session AS lesson_session', 'violation.lesson_session_id = lesson_session.id')
            ->leftJoin('users AS users', 'violation.student_id = users.id')
            ->leftJoin('violation_type AS violation_type', 'violation.violation_type_id = violation_type.id')
            ->leftJoin('users AS users_created_by', 'violation.created_by = users_created_by.id')
            ->where(['OR',
                ['LIKE', 'lesson_session.id', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'violation_type.name', $keyword],
                ['LIKE', 'users_created_by.username', $keyword],
                ['LIKE', 'violation.violation_type_id', $keyword]
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