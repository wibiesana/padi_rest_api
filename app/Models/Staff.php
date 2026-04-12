<?php

namespace App\Models;

use App\Models\Base\Staff as BaseModel;
use Wibiesana\Padi\Core\Query;

class Staff extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->fillable[] = 'id';
        $this->fillable = array_merge($this->fillable, ['created_at', 'updated_at', 'created_by', 'updated_by']);
    }

    /**
     * Override searchPaginate to fix 'Invalid parameter number' error in PDO.
     * Uses explicit string-based WHERE clause with unique parameters.
     */
    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $params = [
            ':kw1' => $keyword,
            ':kw2' => $keyword,
            ':kw3' => $keyword,
            ':kw4' => $keyword,
            ':kw5' => $keyword,
            ':kw6' => $keyword,
        ];

        $whereSql = "staff.name LIKE :kw1 
            OR staff.job_status LIKE :kw2 
            OR staff.email LIKE :kw3 
            OR staff.status LIKE :kw4 
            OR users.username LIKE :kw5
            OR staff.nik LIKE :kw6"; // Add nik or nip if relevant, let's just stick to base fields

        $whereSql = "staff.name LIKE :kw1 
            OR staff.job_status LIKE :kw2 
            OR staff.email LIKE :kw3 
            OR staff.status LIKE :kw4 
            OR users.username LIKE :kw5";
        unset($params[':kw6']);

        // 1. Manual Count
        $count = (int) Query::find()
            ->from("staff")
            ->leftJoin('users', 'staff.id = users.id')
            ->where($whereSql, $params)
            ->count();

        // 2. Manual Data Fetch
        $query = Query::find()
            ->select("staff.*")
            ->from("staff")
            ->leftJoin('users', 'staff.id = users.id')
            ->where($whereSql, $params);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "staff.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("staff.id DESC");
        }

        $items = $query->limit($perPage)->offset(($page - 1) * $perPage)->all();

        if (!empty($items)) {
            $this->loadRelations($items);
            $items = $this->hideFields($items);
        }

        return [
            'data' => $items,
            'meta' => [
                'total' => $count,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($count / $perPage),
                'from' => ($page - 1) * $perPage + 1,
                'to' => min($page * $perPage, $count)
            ]
        ];
    }

    /**
     * Override simple search.
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $keyword = "%{$keyword}%";
        $params = [
            ':kw1' => $keyword,
            ':kw2' => $keyword,
            ':kw3' => $keyword,
            ':kw4' => $keyword,
            ':kw5' => $keyword,
        ];

        $whereSql = "staff.name LIKE :kw1 
            OR staff.job_status LIKE :kw2 
            OR staff.email LIKE :kw3 
            OR staff.status LIKE :kw4 
            OR users.username LIKE :kw5";

        $query = Query::find()
            ->select("staff.*")
            ->from("staff")
            ->leftJoin('users', 'staff.id = users.id')
            ->where($whereSql, $params)
            ->limit(100);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "staff.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("staff.id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}
