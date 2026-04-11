<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Teacher extends ActiveRecord
{
    protected string $table = 'teacher';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'id', 'data_sekolah_id', 'name', 'short_name', 'nuptk', 'nip', 'nik', 'gender', 'place_of_birth', 'date_of_birth', 'job_status', 'religion', 'address', 'no_hp', 'email', 'photo', 'status', 'asc_id', 'asc_partner_id'
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

    public function id()
    {
        return $this->belongsTo(\App\Models\User::class, 'id');
    }

    public function attendancedailyteacher()
    {
        return $this->hasOne(\App\Models\AttendanceDailyTeacher::class, 'teacher_id');
    }

    public function classrooms()
    {
        return $this->hasMany(\App\Models\Classroom::class, 'teacher_id');
    }

    public function departments()
    {
        return $this->hasMany(\App\Models\Department::class, 'teacher_id');
    }

    public function examexaminers()
    {
        return $this->hasMany(\App\Models\ExamExaminer::class, 'teacher_id');
    }

    public function examreports()
    {
        return $this->hasMany(\App\Models\ExamReport::class, 'supervisor_id');
    }

    public function examsupervisors()
    {
        return $this->hasMany(\App\Models\ExamSupervisor::class, 'teacher_id');
    }

    public function lessonsessions()
    {
        return $this->hasMany(\App\Models\LessonSession::class, 'teacher_id');
    }

    public function questionbanks()
    {
        return $this->hasMany(\App\Models\QuestionBank::class, 'teacher_id');
    }

    public function teacheruploadresults()
    {
        return $this->hasMany(\App\Models\TeacherUploadResult::class, 'teacher_id');
    }

    public function teachingschedules()
    {
        return $this->hasMany(\App\Models\TeachingSchedule::class, 'teacher_id');
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
            ->leftJoin('users AS users', 'teacher.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'teacher.updated_by = users_updated_by.id')
            ->leftJoin('users AS users_id', 'teacher.id = users_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'users_id.username', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'teacher.short_name', $keyword],
                ['LIKE', 'teacher.job_status', $keyword],
                ['LIKE', 'teacher.email', $keyword],
                ['LIKE', 'teacher.status', $keyword]
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
     * Search teacher (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'teacher.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'teacher.updated_by = users_updated_by.id')
            ->leftJoin('users AS users_id', 'teacher.id = users_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'users_id.username', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'teacher.short_name', $keyword],
                ['LIKE', 'teacher.job_status', $keyword],
                ['LIKE', 'teacher.email', $keyword],
                ['LIKE', 'teacher.status', $keyword]
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