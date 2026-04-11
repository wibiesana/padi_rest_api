<?php

namespace App\Models;

use App\Models\Base\Teacher as BaseModel;
use Wibiesana\Padi\Core\Query;

class Teacher extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->fillable[] = 'id';
    }

    /**
     * Override searchPaginate to fix 'Invalid parameter number' error.
     * Use string-based where to stabilize parameter binding and manual pagination to avoid core reuse bugs.
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
        ];
        $whereSql = "teacher.name LIKE :kw1 OR teacher.email LIKE :kw2 OR teacher.nip LIKE :kw3 OR teacher.nik LIKE :kw4 OR users.username LIKE :kw5";

        // 1. Manual Count
        $count = (int) Query::find()
            ->from("teacher")
            ->leftJoin('users', 'teacher.id = users.id')
            ->where($whereSql, $params)
            ->count();

        // 2. Manual Data Fetch
        $query = Query::find()
            ->select("teacher.*")
            ->from("teacher")
            ->leftJoin('users', 'teacher.id = users.id')
            ->where($whereSql, $params);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "teacher.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("teacher.id DESC");
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
     * Also override simple search for consistency.
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
        $whereSql = "teacher.name LIKE :kw1 OR teacher.email LIKE :kw2 OR teacher.nip LIKE :kw3 OR teacher.nik LIKE :kw4 OR users.username LIKE :kw5";

        $query = Query::find()
            ->select("teacher.*")
            ->from("teacher")
            ->leftJoin('users', 'teacher.id = users.id')
            ->where($whereSql, $params)
            ->limit(100);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "teacher.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("teacher.id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }
}
