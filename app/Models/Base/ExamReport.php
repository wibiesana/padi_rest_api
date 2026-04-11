<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ExamReport extends ActiveRecord
{
    protected string $table = 'exam_reports';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'exam_id', 'classroom_id', 'supervisor_id', 'report_date', 'student_count', 'present_count', 'absent_count', 'incident_report'
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


    public function classroom()
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'classroom_id');
    }

    public function exam()
    {
        return $this->belongsTo(\App\Models\Exam::class, 'exam_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'supervisor_id');
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
            ->leftJoin('classroom AS classroom', 'exam_reports.classroom_id = classroom.id')
            ->leftJoin('exam AS exam', 'exam_reports.exam_id = exam.id')
            ->leftJoin('teacher AS teacher', 'exam_reports.supervisor_id = teacher.id')
            ->where(['OR',
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'teacher.name', $keyword]
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
     * Search exam_reports (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('classroom AS classroom', 'exam_reports.classroom_id = classroom.id')
            ->leftJoin('exam AS exam', 'exam_reports.exam_id = exam.id')
            ->leftJoin('teacher AS teacher', 'exam_reports.supervisor_id = teacher.id')
            ->where(['OR',
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'teacher.name', $keyword]
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