<?php

namespace App\Models;

use App\Models\Base\Semester as BaseModel;
use Wibiesana\Padi\Core\Query;

class Semester extends BaseModel
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
        ];

        $whereSql = "semester.name LIKE :kw1 
            OR semester.status LIKE :kw2 
            OR users.username LIKE :kw3
            OR users_updated_by.username LIKE :kw4";

        // 1. Manual Count
        $count = (int) Query::find()
            ->from("semester")
            ->leftJoin('users', 'semester.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'semester.updated_by = users_updated_by.id')
            ->where($whereSql, $params)
            ->count();

        // 2. Manual Data Fetch
        $query = Query::find()
            ->select("semester.*")
            ->from("semester")
            ->leftJoin('users', 'semester.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'semester.updated_by = users_updated_by.id')
            ->where($whereSql, $params);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "semester.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("semester.id DESC");
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
        ];

        $whereSql = "semester.name LIKE :kw1 
            OR semester.status LIKE :kw2 
            OR users.username LIKE :kw3
            OR users_updated_by.username LIKE :kw4";

        $query = Query::find()
            ->select("semester.*")
            ->from("semester")
            ->leftJoin('users', 'semester.created_by = users.id')
            ->leftJoin('users AS users_updated_by', 'semester.updated_by = users_updated_by.id')
            ->where($whereSql, $params)
            ->limit(100);

        if ($orderBy) {
            if (strpos($orderBy, '.') === false) {
                $orderBy = "semester.{$orderBy}";
            }
            $query->orderBy($orderBy);
        } else {
            $query->orderBy("semester.id DESC");
        }

        $results = $query->all();

        if (!empty($results)) {
            $this->loadRelations($results);
            $results = $this->hideFields($results);
        }

        return $results;
    }

    /**
     * Override searchPaginate to support school_year_id filter
     */
    public function searchWithSchoolYear(string $keyword, int $page = 1, int $perPage = 10, ?int $schoolYearId = null): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        $conditions = ["semester.name LIKE :k1"];
        $params = ['k1' => $searchTerm];

        if ($schoolYearId) {
            $conditions[] = "semester.school_year_id = :school_year_id";
            $params['school_year_id'] = $schoolYearId;
        }

        $whereClause = implode(' AND ', $conditions);

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT * FROM {$this->table} 
                WHERE {$whereClause}
                ORDER BY {$this->primaryKey} DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($results)) {
            $this->loadRelations($results);
        }

        return [
            'data' => $this->hideFields($results),
            'meta' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Paginate with optional filter
     */
    public function paginateWithSchoolYear(int $page = 1, int $perPage = 10, ?int $schoolYearId = null): array
    {
        if ($schoolYearId === null) {
            return parent::paginate($page, $perPage);
        }

        $offset = ($page - 1) * $perPage;
        $params = ['school_year_id' => $schoolYearId];

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE school_year_id = :school_year_id";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT * FROM {$this->table} WHERE school_year_id = :school_year_id 
                ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':school_year_id', $schoolYearId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $this->hideFields($stmt->fetchAll(\PDO::FETCH_ASSOC));

        if (!empty($this->with) && !empty($results)) {
            $this->loadRelations($results);
        }

        return [
            'data' => $results,
            'meta' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
}
