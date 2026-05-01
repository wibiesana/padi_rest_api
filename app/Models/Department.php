<?php

namespace App\Models;

use App\Models\Base\Department as BaseModel;

class Department extends BaseModel
{
    /**
     * Override searchPaginate to support semester_id filter
     */
    public function searchWithSemester(string $keyword, int $page = 1, int $perPage = 10, ?int $semesterId = null): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        $conditions = ["(users.name LIKE :k1 OR teacher.name LIKE :k2 OR users_updated_by.name LIKE :k3 OR department.name LIKE :k4 OR department.description LIKE :k5 OR semester.name LIKE :k6)"];
        $params = [
            'k1' => $searchTerm,
            'k2' => $searchTerm,
            'k3' => $searchTerm,
            'k4' => $searchTerm,
            'k5' => $searchTerm,
            'k6' => $searchTerm
        ];

        if ($semesterId) {
            $conditions[] = "department.semester_id = :semester_id";
            $params['semester_id'] = $semesterId;
        }

        $whereClause = implode(' AND ', $conditions);

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} 
                     LEFT JOIN users AS users ON department.created_by = users.id
                     LEFT JOIN teacher AS teacher ON department.teacher_id = teacher.id
                     LEFT JOIN semester AS semester ON department.semester_id = semester.id
                     LEFT JOIN users AS users_updated_by ON department.updated_by = users_updated_by.id
                     WHERE {$whereClause}";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT department.* 
                FROM {$this->table} 
                LEFT JOIN users AS users ON department.created_by = users.id
                     LEFT JOIN teacher AS teacher ON department.teacher_id = teacher.id
                     LEFT JOIN semester AS semester ON department.semester_id = semester.id
                     LEFT JOIN users AS users_updated_by ON department.updated_by = users_updated_by.id
                WHERE {$whereClause}
                ORDER BY department.{$this->primaryKey} DESC
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
    public function paginateWithSemester(int $page = 1, int $perPage = 10, ?int $semesterId = null): array
    {
        if ($semesterId === null) {
            return parent::paginate($page, $perPage);
        }

        $offset = ($page - 1) * $perPage;
        $params = ['semester_id' => $semesterId];

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE semester_id = :semester_id";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT * FROM {$this->table} WHERE semester_id = :semester_id 
                ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':semester_id', $semesterId, \PDO::PARAM_INT);
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
