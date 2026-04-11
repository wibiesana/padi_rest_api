<?php

namespace App\Models;

use App\Models\Base\AttendanceDailyStudent as BaseModel;

class AttendanceDailyStudent extends BaseModel
{
    // Add custom model logic here
    public function loadStudent(array &$records)
    {
        $this->with(['student'])->loadRelations($records);
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
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where[] = "attendance_daily_student.attendance_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $filters['start_date'];
            $params['end_date'] = $filters['end_date'];
        } elseif (!empty($filters['date'])) {
            $where[] = "attendance_daily_student.attendance_date = :date";
            $params['date'] = $filters['date'];
        }

        // Filter by Class
        $join = "JOIN student s ON attendance_daily_student.student_id = s.id 
                 LEFT JOIN classroom_member sc_main ON s.id = sc_main.student_id
                 LEFT JOIN classroom c_main ON sc_main.class_id = c_main.id";

        if (!empty($filters['class_id'])) {
            $where[] = "sc_main.class_id = :class_id";
            $params['class_id'] = $filters['class_id'];
        }

        // Filter by Status
        if (!empty($filters['status'])) {
            $where[] = "attendance_daily_student.status = :status";
            $params['status'] = $filters['status'];
        }

        // Filter by Student ID
        if (!empty($filters['student_id'])) {
            if (is_array($filters['student_id'])) {
                // Handle array of IDs
                $placeholders = [];
                foreach ($filters['student_id'] as $k => $id) {
                    $key = "student_id_$k";
                    $placeholders[] = ":$key";
                    $params[$key] = $id;
                }
                $where[] = "attendance_daily_student.student_id IN (" . implode(',', $placeholders) . ")";
            } else {
                $where[] = "attendance_daily_student.student_id = :student_id";
                $params['student_id'] = $filters['student_id'];
            }
        }

        // Filter by Search
        // Filter by Search
        if (!empty($filters['search'])) {
            $where[] = "(s.name LIKE :search_name OR attendance_daily_student.note LIKE :search_note)";
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
        $sql = "SELECT {$this->table}.*, c_main.name as class_name 
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
            $this->with(['student:id,name,nis'])->loadRelations($results);
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
