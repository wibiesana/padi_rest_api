<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class AssignmentClass extends ActiveRecord
{
    protected string $table = 'assignment_class';
    protected string|array $primaryKey = ['assignment_id', 'classroom_id'];
    
    protected array $fillable = [
        'assignment_id', 'classroom_id'
    ];
    
    protected array $hidden = [];


    public function assignment()
    {
        return $this->belongsTo(\App\Models\Assignment::class, 'assignment_id');
    }

    public function classroom()
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'classroom_id');
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
            ->leftJoin('assignment AS assignment', 'assignment_class.assignment_id = assignment.id')
            ->leftJoin('classroom AS classroom', 'assignment_class.classroom_id = classroom.id')
            ->where(['OR',
                ['LIKE', 'assignment.name', $keyword],
                ['LIKE', 'classroom.name', $keyword]
            ]);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.assignment_id DESC");
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
     * Search assignment_class (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('assignment AS assignment', 'assignment_class.assignment_id = assignment.id')
            ->leftJoin('classroom AS classroom', 'assignment_class.classroom_id = classroom.id')
            ->where(['OR',
                ['LIKE', 'assignment.name', $keyword],
                ['LIKE', 'classroom.name', $keyword]
            ])
            ->limit(100);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.assignment_id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}