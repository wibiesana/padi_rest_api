<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Exam extends ActiveRecord
{
    protected string $table = 'exam';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'name', 'token', 'test_duration', 'exam_event_id', 'question_bank_id', 'subject_id', 'use_dynamic_token', 'show_pg', 'show_essay', 'show_result', 'percentage_mc_value', 'percentage_essay_value', 'start_date', 'end_date', 'is_random', 'status', 'randomize_questions', 'randomize_options', 'lock_on_switch', 'require_supervisor', 'semester_id'
    ];
    
    protected array $hidden = ['token'];

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

    public function questionBank()
    {
        return $this->belongsTo(\App\Models\QuestionBank::class, 'question_bank_id');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    public function subject()
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function examclass()
    {
        return $this->hasOne(\App\Models\ExamClass::class, 'exam_id');
    }

    public function examexaminers()
    {
        return $this->hasMany(\App\Models\ExamExaminer::class, 'exam_id');
    }

    public function examreports()
    {
        return $this->hasMany(\App\Models\ExamReport::class, 'exam_id');
    }

    public function examresults()
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'exam_id');
    }

    public function examsupervisors()
    {
        return $this->hasMany(\App\Models\ExamSupervisor::class, 'exam_id');
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
            ->leftJoin('users AS users', 'exam.created_by = users.id')
            ->leftJoin('question_bank AS question_bank', 'exam.question_bank_id = question_bank.id')
            ->leftJoin('semester AS semester', 'exam.semester_id = semester.id')
            ->leftJoin('subject AS subject', 'exam.subject_id = subject.id')
            ->leftJoin('users AS users_updated_by', 'exam.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'subject.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'exam.status', $keyword]
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
     * Search exam (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'exam.created_by = users.id')
            ->leftJoin('question_bank AS question_bank', 'exam.question_bank_id = question_bank.id')
            ->leftJoin('semester AS semester', 'exam.semester_id = semester.id')
            ->leftJoin('subject AS subject', 'exam.subject_id = subject.id')
            ->leftJoin('users AS users_updated_by', 'exam.updated_by = users_updated_by.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'subject.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'exam.status', $keyword]
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