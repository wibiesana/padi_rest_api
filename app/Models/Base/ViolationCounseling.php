<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ViolationCounseling extends ActiveRecord
{
    protected string $table = 'violation_counseling';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'student_violation_id', 'counseling_session_id', 'action_taken', 'result', 'parent_notified', 'parent_notified_date'
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


    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function counselingSession()
    {
        return $this->belongsTo(\App\Models\ViolationCounselingSession::class, 'counseling_session_id');
    }

    public function studentViolation()
    {
        return $this->belongsTo(\App\Models\Violation::class, 'student_violation_id');
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
            ->leftJoin('users AS users', 'violation_counseling.created_by = users.id')
            ->leftJoin('violation_counseling_session AS violation_counseling_session', 'violation_counseling.counseling_session_id = violation_counseling_session.id')
            ->leftJoin('violation AS violation', 'violation_counseling.student_violation_id = violation.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'violation_counseling_session.id', $keyword],
                ['LIKE', 'violation.id', $keyword]
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
     * Search violation_counseling (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'violation_counseling.created_by = users.id')
            ->leftJoin('violation_counseling_session AS violation_counseling_session', 'violation_counseling.counseling_session_id = violation_counseling_session.id')
            ->leftJoin('violation AS violation', 'violation_counseling.student_violation_id = violation.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'violation_counseling_session.id', $keyword],
                ['LIKE', 'violation.id', $keyword]
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