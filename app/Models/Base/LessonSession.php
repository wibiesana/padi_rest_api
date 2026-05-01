<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class LessonSession extends ActiveRecord
{
    protected string $table = 'lesson_session';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'teaching_schedule_id', 'session_date', 'teacher_id', 'start_time_actual', 'end_time_actual', 'material', 'note', 'status', 'allow_self_attendance', 'qr_token'
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


    public function teachingSchedule()
    {
        return $this->belongsTo(\App\Models\TeachingSchedule::class, 'teaching_schedule_id');
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function attendancestudent()
    {
        return $this->hasOne(\App\Models\AttendanceStudent::class, 'lesson_session_id');
    }

    public function violations()
    {
        return $this->hasMany(\App\Models\Violation::class, 'lesson_session_id');
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
            ->leftJoin('teaching_schedule AS teaching_schedule', 'lesson_session.teaching_schedule_id = teaching_schedule.id')
            ->leftJoin('teacher AS teacher', 'lesson_session.teacher_id = teacher.id')
            ->where(['OR',
                ['LIKE', 'teaching_schedule.id', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'lesson_session.status', $keyword]
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
     * Search lesson_session (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('teaching_schedule AS teaching_schedule', 'lesson_session.teaching_schedule_id = teaching_schedule.id')
            ->leftJoin('teacher AS teacher', 'lesson_session.teacher_id = teacher.id')
            ->where(['OR',
                ['LIKE', 'teaching_schedule.id', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'lesson_session.status', $keyword]
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