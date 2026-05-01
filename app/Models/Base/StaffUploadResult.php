<?php

namespace App\Models\Base;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;

class StaffUploadResult extends ActiveRecord
{
    protected string $table = 'staff_upload_result';
    protected string|array $primaryKey = 'id';
    
    protected array $fillable = [
        'staff_upload_id', 'staff_id', 'status', 'upload_file', 'description'
    ];
    
    protected array $hidden = [];


    public function staff()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }

    public function staffUpload()
    {
        return $this->belongsTo(\App\Models\StaffUpload::class, 'staff_upload_id');
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
            ->leftJoin('staff AS staff', 'staff_upload_result.staff_id = staff.id')
            ->leftJoin('staff_upload AS staff_upload', 'staff_upload_result.staff_upload_id = staff_upload.id')
            ->where(['OR',
                ['LIKE', 'staff.name', $keyword],
                ['LIKE', 'staff_upload.name', $keyword],
                ['LIKE', 'staff_upload_result.status', $keyword],
                ['LIKE', 'staff_upload_result.description', $keyword]
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
     * Search staff_upload_result (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $query = Query::find()
            ->select("{$this->table}.*")
            ->from($this->table)
            ->leftJoin('staff AS staff', 'staff_upload_result.staff_id = staff.id')
            ->leftJoin('staff_upload AS staff_upload', 'staff_upload_result.staff_upload_id = staff_upload.id')
            ->where(['OR',
                ['LIKE', 'staff.name', $keyword],
                ['LIKE', 'staff_upload.name', $keyword],
                ['LIKE', 'staff_upload_result.status', $keyword],
                ['LIKE', 'staff_upload_result.description', $keyword]
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