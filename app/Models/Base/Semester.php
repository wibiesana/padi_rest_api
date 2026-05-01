<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Semester extends ActiveRecord
{
    protected string $table = 'semester';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'name', 'start_date', 'end_date', 'status'
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

    public function ascimportlogs()
    {
        return $this->hasMany(\App\Models\AscImportLog::class, 'semester_id');
    }

    public function assignments()
    {
        return $this->hasMany(\App\Models\Assignment::class, 'semester_id');
    }

    public function billings()
    {
        return $this->hasMany(\App\Models\Billing::class, 'semester_id');
    }

    public function classrooms()
    {
        return $this->hasMany(\App\Models\Classroom::class, 'semester_id');
    }

    public function departments()
    {
        return $this->hasMany(\App\Models\Department::class, 'semester_id');
    }

    public function exams()
    {
        return $this->hasMany(\App\Models\Exam::class, 'semester_id');
    }

    public function examevents()
    {
        return $this->hasMany(\App\Models\ExamEvent::class, 'semester_id');
    }

    public function exercises()
    {
        return $this->hasMany(\App\Models\Exercise::class, 'semester_id');
    }

    public function staffuploads()
    {
        return $this->hasMany(\App\Models\StaffUpload::class, 'semester_id');
    }

    public function studentuploads()
    {
        return $this->hasMany(\App\Models\StudentUpload::class, 'semester_id');
    }

    public function teacheruploads()
    {
        return $this->hasMany(\App\Models\TeacherUpload::class, 'semester_id');
    }

    public function teachingschedules()
    {
        return $this->hasMany(\App\Models\TeachingSchedule::class, 'semester_id');
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
            ->leftJoin('users AS users', 'semester.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'semester.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'semester.status', $keyword]
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
     * Search semester (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'semester.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'semester.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'semester.status', $keyword]
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