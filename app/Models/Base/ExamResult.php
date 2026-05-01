<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ExamResult extends ActiveRecord
{
    protected string $table = 'exam_result';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'status', 'exam_status_id', 'is_locked', 'contain_essay', 'attemp', 'essay_result', 'mc_result', 'total_result', 'answer_score_list', 'duration', 'exam_id', 'student_id'
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


    public function exam()
    {
        return $this->belongsTo(\App\Models\Exam::class, 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function examresultanswers()
    {
        return $this->hasMany(\App\Models\ExamResultAnswer::class, 'exam_result_id');
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
            ->leftJoin('exam AS exam', 'exam_result.exam_id = exam.id')
            ->leftJoin('student AS student', 'exam_result.student_id = student.id')
            ->where(['OR',
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'exam_result.status', $keyword],
                ['LIKE', 'exam_result.exam_status_id', $keyword]
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
     * Search exam_result (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('exam AS exam', 'exam_result.exam_id = exam.id')
            ->leftJoin('student AS student', 'exam_result.student_id = student.id')
            ->where(['OR',
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'exam_result.status', $keyword],
                ['LIKE', 'exam_result.exam_status_id', $keyword]
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