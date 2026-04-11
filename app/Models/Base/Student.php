<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class Student extends ActiveRecord
{
    protected string $table = 'student';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'id', 'name', 'nis', 'nisn', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status', 'anak_ke', 'alamat', 'rt', 'rw', 'desa_kelurahan', 'kecamatan', 'kode_pos', 'no_telp', 'email', 'father_name', 'mother_name', 'father_occupation', 'mother_occupation', 'guardian_name', 'guardian_address', 'guardian_phone', 'guardian_occupation', 'photo', 'is_active'
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

    public function id()
    {
        return $this->belongsTo(\App\Models\User::class, 'id');
    }

    public function attendancedailystudent()
    {
        return $this->hasOne(\App\Models\AttendanceDailyStudent::class, 'student_id');
    }

    public function attendancestudent()
    {
        return $this->hasOne(\App\Models\AttendanceStudent::class, 'student_id');
    }

    public function billings()
    {
        return $this->hasMany(\App\Models\Billing::class, 'student_id');
    }

    public function classroommember()
    {
        return $this->hasOne(\App\Models\ClassroomMember::class, 'student_id');
    }

    public function examresults()
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'student_id');
    }

    public function studentuploadresults()
    {
        return $this->hasMany(\App\Models\StudentUploadResult::class, 'student_id');
    }

    public function violationcounselingsessions()
    {
        return $this->hasMany(\App\Models\ViolationCounselingSession::class, 'student_id');
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
            ->leftJoin('users AS users', 'student.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'student.updated_by = users_updated_by.id')
            ->leftJoin('users AS users_id', 'student.id = users_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'users_id.username', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'student.nis', $keyword],
                ['LIKE', 'student.nisn', $keyword],
                ['LIKE', 'student.jenis_kelamin', $keyword],
                ['LIKE', 'student.status', $keyword],
                ['LIKE', 'student.email', $keyword],
                ['LIKE', 'student.father_name', $keyword],
                ['LIKE', 'student.mother_name', $keyword],
                ['LIKE', 'student.guardian_name', $keyword]
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
     * Search student (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('users AS users', 'student.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'student.updated_by = users_updated_by.id')
            ->leftJoin('users AS users_id', 'student.id = users_id.id')
            ->where(['OR',
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'users_id.username', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'student.nis', $keyword],
                ['LIKE', 'student.nisn', $keyword],
                ['LIKE', 'student.jenis_kelamin', $keyword],
                ['LIKE', 'student.status', $keyword],
                ['LIKE', 'student.email', $keyword],
                ['LIKE', 'student.father_name', $keyword],
                ['LIKE', 'student.mother_name', $keyword],
                ['LIKE', 'student.guardian_name', $keyword]
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