<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Classroom extends ActiveRecord
{
    protected string $table = 'classroom';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'name', 'short_name', 'level', 'teacher_id', 'semester_id', 'department_id', 'grade_level_id', 'status', 'asc_id', 'asc_partner_id'
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


    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(\App\Models\GradeLevel::class, 'grade_level_id');
    }

    public function articleclass()
    {
        return $this->hasOne(\App\Models\ArticleClass::class, 'class_id');
    }

    public function assignmentclass()
    {
        return $this->hasOne(\App\Models\AssignmentClass::class, 'classroom_id');
    }

    public function classroommember()
    {
        return $this->hasOne(\App\Models\ClassroomMember::class, 'class_id');
    }

    public function examclass()
    {
        return $this->hasOne(\App\Models\ExamClass::class, 'class_id');
    }

    public function examreports()
    {
        return $this->hasMany(\App\Models\ExamReport::class, 'classroom_id');
    }

    public function examsupervisors()
    {
        return $this->hasMany(\App\Models\ExamSupervisor::class, 'classroom_id');
    }

    public function exercisegroup()
    {
        return $this->hasOne(\App\Models\ExerciseGroup::class, 'class_id');
    }

    public function teachingschedules()
    {
        return $this->hasMany(\App\Models\TeachingSchedule::class, 'classroom_id');
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
            ->leftJoin('department AS department', 'classroom.department_id = department.id')
            ->leftJoin('teacher AS teacher', 'classroom.teacher_id = teacher.id')
            ->leftJoin('semester AS semester', 'classroom.semester_id = semester.id')
            ->leftJoin('grade_level AS grade_level', 'classroom.grade_level_id = grade_level.id')
            ->where(['OR',
                ['LIKE', 'department.name', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'grade_level.name', $keyword],
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'classroom.short_name', $keyword],
                ['LIKE', 'classroom.status', $keyword]
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
     * Search classroom (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('department AS department', 'classroom.department_id = department.id')
            ->leftJoin('teacher AS teacher', 'classroom.teacher_id = teacher.id')
            ->leftJoin('semester AS semester', 'classroom.semester_id = semester.id')
            ->leftJoin('grade_level AS grade_level', 'classroom.grade_level_id = grade_level.id')
            ->where(['OR',
                ['LIKE', 'department.name', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'grade_level.name', $keyword],
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'classroom.short_name', $keyword],
                ['LIKE', 'classroom.status', $keyword]
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