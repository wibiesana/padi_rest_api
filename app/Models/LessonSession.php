<?php

namespace App\Models;

use App\Models\Base\LessonSession as BaseModel;

class LessonSession extends BaseModel
{
    // Add custom model logic here

    public function teacher()
    {
        return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id');
    }

    public function searchPaginate(string $keyword, int $page = 1, int $perPage = 25, ?string $orderBy = null, array $filters = []): array
    {
        $searchTerm = "%$keyword%";
        $offset = ($page - 1) * $perPage;

        // Base Query
        $baseSql = "FROM {$this->table} 
                    LEFT JOIN teaching_schedule AS teaching_schedule ON lesson_session.teaching_schedule_id = teaching_schedule.id
                    LEFT JOIN teacher AS teacher ON lesson_session.teacher_id = teacher.id
                    LEFT JOIN subject AS subject ON teaching_schedule.subject_id = subject.id
                    LEFT JOIN classroom AS classroom ON teaching_schedule.classroom_id = classroom.id
                    WHERE (teaching_schedule.id LIKE :k1 OR teacher.name LIKE :k2 OR lesson_session.status LIKE :k3 OR subject.name LIKE :k4 OR classroom.name LIKE :k5)";

        $params = [
            'k1' => $searchTerm,
            'k2' => $searchTerm,
            'k3' => $searchTerm,
            'k4' => $searchTerm,
            'k5' => $searchTerm
        ];

        // Apply Filters
        if (!empty($filters['session_date'])) {
            $baseSql .= " AND lesson_session.session_date = :session_date";
            $params['session_date'] = $filters['session_date'];
        }

        if (!empty($filters['teacher_id'])) {
            $baseSql .= " AND lesson_session.teacher_id = :teacher_id";
            $params['teacher_id'] = $filters['teacher_id'];
        }

        if (!empty($filters['status'])) {
            $baseSql .= " AND lesson_session.status = :status";
            $params['status'] = $filters['status'];
        }

        // 1. Get Total Count
        $countSql = "SELECT COUNT(*) as total " . $baseSql;
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Get Data
        $dataSql = "SELECT {$this->table}.*, 
                           teacher.name as teacher_name, 
                           subject.name as subject_name,
                           classroom.name as class_name
                    " . $baseSql . "
                    ORDER BY {$this->table}.session_date DESC, {$this->table}.{$this->primaryKey} DESC
                    LIMIT :limit OFFSET :offset";

        // Add limit params for data query
        $stmt = $this->db->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Eager load (optional, but relations are manually joined nicely above)
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
     * Search lesson_session (simple limit)
     */
    public function search(string $keyword, ?string $orderBy = null): array
    {
        $searchTerm = "%$keyword%";

        $sql = "SELECT {$this->table}.* FROM {$this->table} 
                LEFT JOIN teaching_schedule AS teaching_schedule ON lesson_session.teaching_schedule_id = teaching_schedule.id
                LEFT JOIN teacher AS teacher ON lesson_session.teacher_id = teacher.id
                WHERE teaching_schedule.id LIKE :k1 OR teacher.name LIKE :k2 OR lesson_session.status LIKE :k3
                LIMIT 100";

        $results = $this->query($sql, [
            'k1' => $searchTerm,
            'k2' => $searchTerm,
            'k3' => $searchTerm
        ]);

        if (!empty($results)) {
            $this->loadRelations($results);
        }

        return $results;
    }

    /**
     * Get teaching schedule for a specific date with session and attendance status
     */
    public function getScheduleForDate(string $date, ?int $teacherId = null, int $page = 1, int $perPage = 10, string $search = '', array $filters = []): array
    {
        $dayOfWeek = date('N', strtotime($date)); // 1 (Mon) - 7 (Sun)
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%$search%";

        // 1. Try to find Semester encompassing the date
        $semesterIds = [];
        $semSql = "SELECT id FROM semester WHERE start_date <= :d1 AND end_date >= :d2";
        $stmt = $this->db->prepare($semSql);
        $stmt->execute(['d1' => $date, 'd2' => $date]);
        $semesterIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // 2. If none covers the date, fallback to ALL active semesters
        if (empty($semesterIds)) {
            $activeSemSql = "SELECT id FROM semester WHERE status = 1";
            $stmt = $this->db->prepare($activeSemSql);
            $stmt->execute();
            $semesterIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        }

        // 3. Fallback: If still empty (no active semesters), maybe show data from Semester 1 or all?
        // To be safe and help the user see their data, if we found 0 semesters, 
        // we might want to just show all active schedules for that day.

        // Base query for TeachingSchedule
        $baseSql = "FROM teaching_schedule
                    LEFT JOIN classroom ON teaching_schedule.classroom_id = classroom.id
                    LEFT JOIN subject ON teaching_schedule.subject_id = subject.id
                    LEFT JOIN teacher ON teaching_schedule.teacher_id = teacher.id
                    -- Check for existing session
                    LEFT JOIN lesson_session ON teaching_schedule.id = lesson_session.teaching_schedule_id AND lesson_session.session_date = :date_join
                    -- Check for existing attendance (simplified count)
                    LEFT JOIN (SELECT lesson_session_id, count(id) as total_attendance FROM attendance_student GROUP BY lesson_session_id) as attendance_summary ON lesson_session.id = attendance_summary.lesson_session_id
                    WHERE teaching_schedule.day_of_week = :day
                    AND teaching_schedule.status = 1"; // Ensure schedule is active

        $params = [
            'date_join' => $date,
            'date_calc_1' => $date,
            'date_calc_2' => $date,
            'day' => $dayOfWeek,
            'time_calc_1' => date('Y-m-d H:i:s'),
            'time_calc_2' => date('Y-m-d H:i:s')
        ];

        // Filter by semester if we found any relevant ones
        /* 
        if (!empty($semesterIds)) {
            $baseSql .= " AND teaching_schedule.semester_id IN (" . implode(',', $semesterIds) . ")";
        }
        */

        if (!empty($search)) {
            $baseSql .= " AND (classroom.name LIKE :k1 OR subject.name LIKE :k2 OR teacher.name LIKE :k3)";
            $params['k1'] = $searchTerm;
            $params['k2'] = $searchTerm;
            $params['k3'] = $searchTerm;
        }

        if ($teacherId) {
            $baseSql .= " AND teaching_schedule.teacher_id = :teacher_id";
            $params['teacher_id'] = $teacherId;
        }

        // Add Status filter (this is tricky because status is a calculated CASE in SQL)
        // We can wrap the query or use the same logic in WHERE.
        if (!empty($filters['status'])) {
            $statusFilter = $filters['status'];
            if ($statusFilter === 'not_started') {
                $baseSql .= " AND lesson_session.status IS NULL AND CONCAT(:date_calc_1_s, ' ', teaching_schedule.start_time) > :time_calc_1_s";
                $params['date_calc_1_s'] = $date;
                $params['time_calc_1_s'] = date('Y-m-d H:i:s');
            } elseif ($statusFilter === 'missed') {
                $baseSql .= " AND lesson_session.status IS NULL AND CONCAT(:date_calc_2_s, ' ', teaching_schedule.end_time) < :time_calc_2_s";
                $params['date_calc_2_s'] = $date;
                $params['time_calc_2_s'] = date('Y-m-d H:i:s');
            } elseif ($statusFilter === 'pending') {
                $baseSql .= " AND lesson_session.status IS NULL AND CONCAT(:date_calc_1_s, ' ', teaching_schedule.start_time) <= :time_calc_1_s AND CONCAT(:date_calc_2_s, ' ', teaching_schedule.end_time) >= :time_calc_2_s";
                $params['date_calc_1_s'] = $date;
                $params['time_calc_1_s'] = date('Y-m-d H:i:s');
                $params['date_calc_2_s'] = $date;
                $params['time_calc_2_s'] = date('Y-m-d H:i:s');
            } else {
                // Actual recorded statuses (Hadir, Izin, etc)
                $baseSql .= " AND lesson_session.status = :status_filter";
                $params['status_filter'] = $statusFilter;
            }
        }

        // 1. Count
        $countSql = "SELECT COUNT(DISTINCT teaching_schedule.id) as total " . $baseSql;
        $stmt = $this->db->prepare($countSql);

        // Filter params for count query (remove 'calc' params as they are not in $baseSql)
        $countParams = $params;
        unset($countParams['date_calc_1'], $countParams['date_calc_2'], $countParams['time_calc_1'], $countParams['time_calc_2']);

        $stmt->execute($countParams);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];

        // 2. Data
        $dataSql = "SELECT 
                        teaching_schedule.*,
                        classroom.name as class_name,
                        subject.name as subject_name,
                        teacher.name as teacher_name,
                        teaching_schedule.start_time as start_time,
                        teaching_schedule.end_time as end_time,
                        lesson_session.id as existing_session_id,
                        lesson_session.status as existing_session_status,
                        lesson_session.material,
                        lesson_session.note,
                        CASE 
                            WHEN lesson_session.status IS NOT NULL THEN lesson_session.status
                            WHEN CONCAT(:date_calc_1, ' ', teaching_schedule.start_time) > :time_calc_1 THEN 'not_started'
                            WHEN CONCAT(:date_calc_2, ' ', teaching_schedule.end_time) < :time_calc_2 THEN 'missed'
                            ELSE 'pending'
                        END as status,
                        CASE WHEN attendance_summary.total_attendance > 0 THEN 1 ELSE 0 END as has_attendance
                    " . $baseSql . "
                    ORDER BY teaching_schedule.start_time ASC, teaching_schedule.id ASC
                    LIMIT :limit OFFSET :offset";



        $stmt = $this->db->prepare($dataSql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Map Period Name from Settings
        $activeSetting = \App\Models\Setting::findQuery()->where(['status' => 1])->one();
        $dailySchedule = [];
        if ($activeSetting) {
            $settingData = json_decode($activeSetting['setting'], true);
            $dailySchedule = $settingData['daily_schedule'] ?? [];
        }

        foreach ($results as &$row) {
            $periodName = "Jam Ke-" . $row['period_number'];
            foreach ($dailySchedule as $period) {
                if (($period['jam_ke'] ?? null) == $row['period_number']) {
                    $periodName = $period['label'] ?? $periodName;
                    break;
                }
            }
            $row['period_name'] = $periodName;
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
