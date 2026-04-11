<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class TeacherUpload extends ActiveRecord
{
    protected string $table = 'teacher_upload';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'name', 'description', 'status', 'is_multiple', 'start_date', 'end_date', 'semester_id'
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


    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    public function teacheruploadresults()
    {
        return $this->hasMany(\App\Models\TeacherUploadResult::class, 'teacher_upload_id');
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
            ->leftJoin('users AS users', 'teacher_upload.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'teacher_upload.updated_by = users_updated_by.id')
            ->leftJoin('semester AS semester', 'teacher_upload.semester_id = semester.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'teacher_upload.name', $keyword],
                ['LIKE', 'teacher_upload.description', $keyword],
                ['LIKE', 'teacher_upload.status', $keyword]
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
     * Search teacher_upload (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'teacher_upload.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'teacher_upload.updated_by = users_updated_by.id')
            ->leftJoin('semester AS semester', 'teacher_upload.semester_id = semester.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'semester.name', $keyword],
                ['LIKE', 'teacher_upload.name', $keyword],
                ['LIKE', 'teacher_upload.description', $keyword],
                ['LIKE', 'teacher_upload.status', $keyword]
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