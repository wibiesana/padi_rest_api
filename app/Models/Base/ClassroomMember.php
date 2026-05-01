<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class ClassroomMember extends ActiveRecord
{
    protected string $table = 'classroom_member';
    protected string|array $primaryKey = ['student_id', 'class_id'];
    
    protected array $fillable = [
        'student_id', 'class_id'
    ];
    
    protected array $hidden = [];


    public function class()
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
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
            ->leftJoin('classroom AS classroom', 'classroom_member.class_id = classroom.id')
            ->leftJoin('student AS student', 'classroom_member.student_id = student.id')
            ->where(['OR',
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'student.name', $keyword]
            ]);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.student_id DESC");
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
     * Search classroom_member (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('classroom AS classroom', 'classroom_member.class_id = classroom.id')
            ->leftJoin('student AS student', 'classroom_member.student_id = student.id')
            ->where(['OR',
                ['LIKE', 'classroom.name', $keyword],
                ['LIKE', 'student.name', $keyword]
            ])
            ->limit(100);

        if ($orderBy) {
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("{$this->table}.student_id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}