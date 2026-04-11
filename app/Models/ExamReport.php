<?php

namespace App\Models;

use App\Models\Base\ExamReport as BaseExamReport;

class ExamReport extends BaseExamReport
{
    /**
     * Search with pagination and joins
     */
    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null, ?int $eventId = null): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        $whereClauses = ["exam.name LIKE :k1"];
        $params = ['k1' => $searchTerm];

        if ($eventId) {
            $whereClauses[] = "exam.exam_event_id = :event_id";
            $params['event_id'] = $eventId;
        }

        $wherePart = "WHERE " . implode(" AND ", $whereClauses);

        // 1. Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} 
                     LEFT JOIN exam ON exam_reports.exam_id = exam.id
                     {$wherePart}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get data
        $sql = "SELECT exam_reports.*, exam.name as exam_name, classroom.name as classroom_name, 
                       COALESCE(teacher.name, users.username, 'System') as supervisor_name
                FROM {$this->table}
                LEFT JOIN exam ON exam_reports.exam_id = exam.id
                LEFT JOIN classroom ON exam_reports.classroom_id = classroom.id
                LEFT JOIN users ON exam_reports.supervisor_id = users.id
                LEFT JOIN teacher ON exam_reports.supervisor_id = teacher.id
                {$wherePart}
                ORDER BY exam_reports.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $results,
            'meta' => [
                'total' => (int)$total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int)ceil($total / $perPage)
            ]
        ];
    }
}
