<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class QuestionBank extends ActiveRecord
{
    protected string $table = 'question_bank';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'exam_event_id', 'name', 'description', 'status', 'teacher_id'
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


    public function examEvent()
    {
        return $this->belongsTo(\App\Models\ExamEvent::class, 'exam_event_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function exams()
    {
        return $this->hasMany(\App\Models\Exam::class, 'question_bank_id');
    }

    public function exercises()
    {
        return $this->hasMany(\App\Models\Exercise::class, 'question_bank_id');
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class, 'question_bank_id');
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
            ->leftJoin('exam_events AS exam_events', 'question_bank.exam_event_id = exam_events.id')
            ->leftJoin('users AS users', 'question_bank.created_by = users.id')
            ->leftJoin('teacher AS teacher', 'question_bank.teacher_id = teacher.id')
            ->leftJoin('users AS users_updated_by', 'question_bank.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'exam_events.name', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'question_bank.description', $keyword],
                ['LIKE', 'question_bank.status', $keyword]
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
     * Search question_bank (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('exam_events AS exam_events', 'question_bank.exam_event_id = exam_events.id')
            ->leftJoin('users AS users', 'question_bank.created_by = users.id')
            ->leftJoin('teacher AS teacher', 'question_bank.teacher_id = teacher.id')
            ->leftJoin('users AS users_updated_by', 'question_bank.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'exam_events.name', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'question_bank.description', $keyword],
                ['LIKE', 'question_bank.status', $keyword]
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