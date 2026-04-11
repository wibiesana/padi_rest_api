<?php

namespace App\Models;

use App\Models\Base\Student as BaseModel;
use Wibiesana\Padi\Core\Env;
use Wibiesana\Padi\Core\Query;

class Student extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->fillable[] = 'id';
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
            ':kw7' => $keyword,
            ':kw8' => $keyword,
            ':kw9' => $keyword,
            ':kw10' => $keyword,
            ':kw11' => $keyword,
        ];
        $whereSql = "student.name LIKE :kw1 
            OR student.nis LIKE :kw2 
            OR student.nisn LIKE :kw3 
            OR student.jenis_kelamin LIKE :kw4 
            OR student.status LIKE :kw5 
            OR student.email LIKE :kw6 
            OR student.father_name LIKE :kw7 
            OR student.mother_name LIKE :kw8 
            OR student.guardian_name LIKE :kw9 
            OR users.username LIKE :kw10
            OR student.ortu_username LIKE :kw11";
        // Note: added ortu_username check since it might be useful, but let's stick to base fields + user

        // Remove ortu_username as it's not base, let's just stick to the fields from the base query
        $whereSql = "student.name LIKE :kw1 
            OR student.nis LIKE :kw2 
            OR student.nisn LIKE :kw3 
            OR student.jenis_kelamin LIKE :kw4 
            OR student.status LIKE :kw5 
            OR student.email LIKE :kw6 
            OR student.father_name LIKE :kw7 
            OR student.mother_name LIKE :kw8 
            OR student.guardian_name LIKE :kw9 
            OR users.username LIKE :kw10";

        unset($params[':kw11']);

        // 1. Manual Count
        $count = (int) Query::find()
            ->from("student")
            ->leftJoin('users', 'student.id = users.id')
            ->where($whereSql, $params)
            ->count();

        // 2. Manual Data Fetch
        $query = Query::find()
            ->select("student.*")
            ->from("student")
            ->leftJoin('users', 'student.id = users.id')
            ->where($whereSql, $params);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "student.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("student.id DESC");
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
            ':kw6' => $keyword,
            ':kw7' => $keyword,
            ':kw8' => $keyword,
            ':kw9' => $keyword,
            ':kw10' => $keyword,
        ];

        $whereSql = "student.name LIKE :kw1 
            OR student.nis LIKE :kw2 
            OR student.nisn LIKE :kw3 
            OR student.jenis_kelamin LIKE :kw4 
            OR student.status LIKE :kw5 
            OR student.email LIKE :kw6 
            OR student.father_name LIKE :kw7 
            OR student.mother_name LIKE :kw8 
            OR student.guardian_name LIKE :kw9 
            OR users.username LIKE :kw10";

        $query = Query::find()
            ->select("student.*")
            ->from("student")
            ->leftJoin('users', 'student.id = users.id')
            ->where($whereSql, $params)
            ->limit(100);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "student.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("student.id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }

    /**
     * Helper to add photo_url after data is loaded
     */
    public function afterLoad(array &$results): void
    {
        $baseUrl = Env::get('APP_URL', 'http://localhost:8085');
        foreach ($results as &$item) {
            if (!empty($item['photo'])) {
                // If it's already a URL, leave it
                if (filter_var($item['photo'], FILTER_VALIDATE_URL)) {
                    $item['photo_url'] = $item['photo'];
                } else {
                    $item['photo_url'] = $baseUrl . '/storage/' . $item['photo'];
                }
            } else {
                $item['photo_url'] = null;
            }
        }
    }
}
