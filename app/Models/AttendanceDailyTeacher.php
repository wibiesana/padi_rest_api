<?php

namespace App\Models;

use App\Models\Base\AttendanceDailyTeacher as BaseModel;

class AttendanceDailyTeacher extends BaseModel
{
    /**
     * Automatically eager load these relations on every query.
     */
    /**
     * Helper to eager load teacher
     */
    public function loadTeacher(array &$records)
    {
        $this->with(['teacher'])->loadRelations($records);
    }

    /**
     * Filter data with pagination
     */
    public function filterPaginate(array $filters, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $where = ["1=1"];

        // Filter by Date
        if (!empty($filters['date'])) {
            $where[] = "attendance_daily_teacher.attendance_date = :date";
            $params['date'] = $filters['date'];
        }

        // Filter by Teacher
        $join = "JOIN teacher t ON attendance_daily_teacher.teacher_id = t.id"; // Always join teacher for search
        if (!empty($filters['teacher_id'])) {
            $where[] = "attendance_daily_teacher.teacher_id = :teacher_id";
            $params['teacher_id'] = $filters['teacher_id'];
        }

        // Filter by Status
        if (!empty($filters['status'])) {
            $where[] = "attendance_daily_teacher.status = :status";
            $params['status'] = $filters['status'];
        }

        // Filter by Search
        if (!empty($filters['search'])) {
            $where[] = "(t.name LIKE :search_name OR attendance_daily_teacher.note LIKE :search_note)";
            $params['search_name'] = "%" . $filters['search'] . "%";
            $params['search_note'] = "%" . $filters['search'] . "%";
        }

        $whereSql = implode(" AND ", $where);

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total 
                     FROM {$this->table} 
                     {$join}
                     WHERE {$whereSql}";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $sql = "SELECT {$this->table}.* 
                FROM {$this->table} 
                {$join}
                WHERE {$whereSql}
                ORDER BY {$this->table}.attendance_date DESC, {$this->table}.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Eager load
        if (!empty($results)) {
            $this->loadRelations($results);
            $this->with(['teacher:id,name,nip'])->loadRelations($results);
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
