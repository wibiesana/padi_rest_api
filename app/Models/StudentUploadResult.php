<?php

namespace App\Models;

use App\Models\Base\StudentUploadResult as BaseModel;

class StudentUploadResult extends BaseModel
{
    /**
     * Search with pagination and joins
     */
    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null, array $filters = []): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        $whereClauses = ["(student.name LIKE :k1 OR student_upload.name LIKE :k2 OR student_upload_result.status LIKE :k3 OR student_upload_result.description LIKE :k4)"];
        $params = [
            'k1' => $searchTerm,
            'k2' => $searchTerm,
            'k3' => $searchTerm,
            'k4' => $searchTerm
        ];

        if (!empty($filters['student_upload_id'])) {
            $whereClauses[] = "student_upload_result.student_upload_id = :filter_suid";
            $params['filter_suid'] = $filters['student_upload_id'];
        }

        if (!empty($filters['student_id'])) {
            $whereClauses[] = "student_upload_result.student_id = :filter_sid";
            $params['filter_sid'] = $filters['student_id'];
        }

        $wherePart = "WHERE " . implode(" AND ", $whereClauses);

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} 
                     LEFT JOIN student AS student ON student_upload_result.student_id = student.id
                     LEFT JOIN student_upload AS student_upload ON student_upload_result.student_upload_id = student_upload.id
                     {$wherePart}";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT {$this->table}.* 
                FROM {$this->table} 
                LEFT JOIN student AS student ON student_upload_result.student_id = student.id
                LEFT JOIN student_upload AS student_upload ON student_upload_result.student_upload_id = student_upload.id
                {$wherePart}
                ORDER BY " . ($orderBy ?: "{$this->table}.{$this->primaryKey} DESC") . "
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
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
}
