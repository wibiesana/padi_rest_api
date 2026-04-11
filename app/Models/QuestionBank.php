<?php

namespace App\Models;

use App\Models\Base\QuestionBank as BaseModel;

class QuestionBank extends BaseModel
{
    /**
     * Hook called after records are loaded.
     * Use this to attach virtual attributes like question_count.
     */
    public function afterLoad(array &$items): void
    {
        $this->attachQuestionCount($items);
    }

    /**
     * Search with pagination and joins.
     * Overridden to ensure afterLoad is called since base model doesn't call it.
     */
    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null): array
    {
        $result = parent::searchPaginate($keyword, $page, $perPage, $orderBy);
        $this->afterLoad($result['data']);
        return $result;
    }

    /**
     * Search question_bank (simple limit).
     * Overridden to ensure afterLoad is called since base model doesn't call it.
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $results = parent::search($keyword, $orderBy);
        $this->afterLoad($results);
        return $results;
    }

    private function attachQuestionCount(array &$items): void
    {
        if (empty($items)) return;

        $ids = array_column($items, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT question_bank_id, COUNT(*) as count 
                FROM question 
                WHERE question_bank_id IN ($placeholders) 
                GROUP BY question_bank_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($ids);
        $counts = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($items as &$item) {
            $item['question_count'] = $counts[$item['id']] ?? 0;
        }
    }
}
