<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ExamResultAnswer extends ActiveRecord
{
    protected string $table = 'exam_result_answer';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'exam_result_id', 'question_id', 'answer', 'score'
    ];
    
    protected array $hidden = [];


    public function examResult()
    {
        return $this->belongsTo(\App\Models\ExamResult::class, 'exam_result_id');
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class, 'question_id');
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
            ->leftJoin('exam_result AS exam_result', 'exam_result_answer.exam_result_id = exam_result.id')
            ->leftJoin('question AS question', 'exam_result_answer.question_id = question.id')
            ->where(['OR',
                ['LIKE', 'exam_result.id', $keyword],
                ['LIKE', 'question.id', $keyword]
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
     * Search exam_result_answer (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('exam_result AS exam_result', 'exam_result_answer.exam_result_id = exam_result.id')
            ->leftJoin('question AS question', 'exam_result_answer.question_id = question.id')
            ->where(['OR',
                ['LIKE', 'exam_result.id', $keyword],
                ['LIKE', 'question.id', $keyword]
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