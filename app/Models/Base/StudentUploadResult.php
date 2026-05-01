<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class StudentUploadResult extends ActiveRecord
{
    protected string $table = 'student_upload_result';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'student_upload_id', 'student_id', 'status', 'upload_file', 'description'
    ];
    
    protected array $hidden = [];


    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function studentUpload()
    {
        return $this->belongsTo(\App\Models\StudentUpload::class, 'student_upload_id');
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
            ->leftJoin('student AS student', 'student_upload_result.student_id = student.id')
            ->leftJoin('student_upload AS student_upload', 'student_upload_result.student_upload_id = student_upload.id')
            ->where(['OR',
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'student_upload.name', $keyword],
                ['LIKE', 'student_upload_result.status', $keyword],
                ['LIKE', 'student_upload_result.description', $keyword]
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
     * Search student_upload_result (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('student AS student', 'student_upload_result.student_id = student.id')
            ->leftJoin('student_upload AS student_upload', 'student_upload_result.student_upload_id = student_upload.id')
            ->where(['OR',
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'student_upload.name', $keyword],
                ['LIKE', 'student_upload_result.status', $keyword],
                ['LIKE', 'student_upload_result.description', $keyword]
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