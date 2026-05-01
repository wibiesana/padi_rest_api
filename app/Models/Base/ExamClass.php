<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ExamClass extends ActiveRecord
{
    protected string $table = 'exam_class';
    protected string|array $primaryKey = ['exam_id', 'class_id'];
    
    protected array $fillable = [
        'exam_id', 'class_id'
    ];
    
    protected array $hidden = [];


    public function class()
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'class_id');
    }

    public function exam()
    {
        return $this->belongsTo(\App\Models\Exam::class, 'exam_id');
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
            ->leftJoin('classroom AS classroom', 'exam_class.class_id = classroom.id')
            ->leftJoin('exam AS exam', 'exam_class.exam_id = exam.id')
            ->where(['OR',
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'exam.name', $keyword]
            ]);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.exam_id DESC");
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
     * Search exam_class (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('classroom AS classroom', 'exam_class.class_id = classroom.id')
            ->leftJoin('exam AS exam', 'exam_class.exam_id = exam.id')
            ->where(['OR',
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'exam.name', $keyword]
            ])
            ->limit(100);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.exam_id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}