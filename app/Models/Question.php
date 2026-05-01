<?php

namespace App\Models;

use App\Models\Base\Question as BaseModel;

class Question extends BaseModel
{
    public function searchByBankPaginate(string $keyword, int $qbId, int $page = 1, int $perPage = 10): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} 
                     WHERE question_bank_id = :qb_id AND question.question LIKE :keyword";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute([
            'qb_id' => $qbId,
            'keyword' => $searchTerm
        ]);

        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT {$this->table}.* 
                FROM {$this->table} 
                WHERE question_bank_id = :qb_id AND question.question LIKE :keyword
                ORDER BY {$this->table}.{$this->primaryKey} DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':qb_id', $qbId, \PDO::PARAM_INT);
        $stmt->bindValue(':keyword', $searchTerm);
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

    public function searchByBank(string $keyword, int $qbId): array
    {
        $searchTerm = "%$keyword%";

        $sql = "SELECT {$this->table}.* FROM {$this->table} 
                WHERE question_bank_id = :qb_id AND question.question LIKE :keyword
                ORDER BY {$this->table}.{$this->primaryKey} DESC
                LIMIT 100";

        $results = $this->query($sql, [
            'qb_id' => $qbId,
            'keyword' => $searchTerm
        ]);

        if (!empty($results)) {
            $this->loadRelations($results);
        }

        return $results;
    }
}
