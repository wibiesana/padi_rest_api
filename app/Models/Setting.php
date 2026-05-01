<?php

namespace App\Models;

use App\Models\Base\Setting as BaseModel;

class Setting extends BaseModel
{
    /**
     * Automatically eager load these relations on every query.
     */
    // protected array $with = [];

    /**
     * Get active settings with pagination
     */
    public function paginateActive(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute();
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
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
