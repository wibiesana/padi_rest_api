<?php

namespace App\Models;

use App\Models\Base\TeacherUploadResult as BaseModel;

class TeacherUploadResult extends BaseModel
{
    /**
     * Search with pagination and joins
     */
    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null, array $filters = []): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        $whereClause = "WHERE (teacher.name LIKE :k1 OR teacher_upload.name LIKE :k2 OR teacher_upload_result.status LIKE :k3 OR teacher_upload_result.description LIKE :k4)";
        $params = [
            'k1' => $searchTerm,
            'k2' => $searchTerm,
            'k3' => $searchTerm,
            'k4' => $searchTerm
        ];

        if (!empty($filters['teacher_upload_id'])) {
            $whereClause .= " AND teacher_upload_result.teacher_upload_id = :filter_tuid";
            $params['filter_tuid'] = $filters['teacher_upload_id'];
        }

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} 
                     LEFT JOIN teacher AS teacher ON teacher_upload_result.teacher_id = teacher.id
                     LEFT JOIN teacher_upload AS teacher_upload ON teacher_upload_result.teacher_upload_id = teacher_upload.id
                     {$whereClause}";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);

        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT {$this->table}.* 
                FROM {$this->table} 
                LEFT JOIN teacher AS teacher ON teacher_upload_result.teacher_id = teacher.id
                LEFT JOIN teacher_upload AS teacher_upload ON teacher_upload_result.teacher_upload_id = teacher_upload.id
                {$whereClause}
                ORDER BY {$this->table}.{$this->primaryKey} DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Eager load if needed (optional)
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
