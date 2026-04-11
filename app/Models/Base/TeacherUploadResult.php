<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class TeacherUploadResult extends ActiveRecord
{
    protected string $table = 'teacher_upload_result';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'teacher_upload_id', 'teacher_id', 'status', 'upload_file', 'description'
    ];
    
    protected array $hidden = [];


    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function teacherUpload()
    {
        return $this->belongsTo(\App\Models\TeacherUpload::class, 'teacher_upload_id');
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
            ->leftJoin('teacher AS teacher', 'teacher_upload_result.teacher_id = teacher.id')
            ->leftJoin('teacher_upload AS teacher_upload', 'teacher_upload_result.teacher_upload_id = teacher_upload.id')
            ->where(['OR',
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'teacher_upload.name', $keyword],
                ['LIKE', 'teacher_upload_result.status', $keyword],
                ['LIKE', 'teacher_upload_result.description', $keyword]
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
     * Search teacher_upload_result (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('teacher AS teacher', 'teacher_upload_result.teacher_id = teacher.id')
            ->leftJoin('teacher_upload AS teacher_upload', 'teacher_upload_result.teacher_upload_id = teacher_upload.id')
            ->where(['OR',
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'teacher_upload.name', $keyword],
                ['LIKE', 'teacher_upload_result.status', $keyword],
                ['LIKE', 'teacher_upload_result.description', $keyword]
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