<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ViolationCounselingSession extends ActiveRecord
{
    protected string $table = 'violation_counseling_session';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'student_id', 'counselor_id', 'session_date', 'session_type', 'topic', 'notes', 'follow_up_required', 'follow_up_date', 'status', 'is_confidential'
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


    public function counselor()
    {
        return $this->belongsTo(\App\Models\User::class, 'counselor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function violationcounselings()
    {
        return $this->hasMany(\App\Models\ViolationCounseling::class, 'counseling_session_id');
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
            ->leftJoin('users AS users', 'violation_counseling_session.counselor_id = users.id')
            ->leftJoin('users AS users_created_by', 'violation_counseling_session.created_by = users_created_by.id')
            ->leftJoin('student AS student', 'violation_counseling_session.student_id = student.id')
            ->leftJoin('users AS users_updated_by', 'violation_counseling_session.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_created_by.username', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'violation_counseling_session.session_type', $keyword],
                ['LIKE', 'violation_counseling_session.status', $keyword]
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
     * Search violation_counseling_session (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'violation_counseling_session.counselor_id = users.id')
            ->leftJoin('users AS users_created_by', 'violation_counseling_session.created_by = users_created_by.id')
            ->leftJoin('student AS student', 'violation_counseling_session.student_id = student.id')
            ->leftJoin('users AS users_updated_by', 'violation_counseling_session.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_created_by.username', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'violation_counseling_session.session_type', $keyword],
                ['LIKE', 'violation_counseling_session.status', $keyword]
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