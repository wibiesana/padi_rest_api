<?php

namespace App\Models;

use App\Models\Base\AttendanceStudent as BaseModel;
use Wibiesana\Padi\Core\Query;

class AttendanceStudent extends BaseModel
{
    /**
     * Get attendance summary by subject for a specific student within a date range
     */
    public function getSummaryByStudent(int $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        $params = ['student_id' => $studentId];
        $whereClause = "";

        if ($startDate && $endDate) {
            $whereClause = " AND l.session_date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }

        $sql = "SELECT 
                    sub.id as subject_id,
                    sub.name as subject_name,
                    COUNT(CASE WHEN {$this->table}.status = '1' THEN 1 END) as present,
                    COUNT(CASE WHEN {$this->table}.status = '2' THEN 1 END) as permission,
                    COUNT(CASE WHEN {$this->table}.status = '3' THEN 1 END) as sick,
                    COUNT(CASE WHEN {$this->table}.status = '4' THEN 1 END) as alpha,
                    COUNT(*) as total_sessions
                FROM {$this->table}
                JOIN lesson_session l ON {$this->table}.lesson_session_id = l.id
                JOIN teaching_schedule ts ON l.teaching_schedule_id = ts.id
                JOIN subject sub ON ts.subject_id = sub.id
                WHERE {$this->table}.student_id = :student_id
                {$whereClause}
                GROUP BY sub.id, sub.name
                ORDER BY sub.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Detect personal attendance issues (anomalies) for a student within a date range
     */
    public function getAnomaliesByStudent(int $studentId, string $startDate, ?string $endDate = null): array
    {
        if (!$endDate) {
            return $this->getAnomaliesPerDay($studentId, $startDate);
        }

        $allAnomalies = [];
        $current = new \DateTime($startDate);
        $last = new \DateTime($endDate);

        // Loop through each day (limit to 31 days to avoid performance issues)
        $daysCount = 0;
        while ($current <= $last && $daysCount < 31) {
            $date = $current->format('Y-MM-DD');
            $dayAnomalies = $this->getAnomaliesPerDay($studentId, $date);
            $allAnomalies = array_merge($allAnomalies, $dayAnomalies);
            $current->modify('+1 day');
            $daysCount++;
        }

        return $allAnomalies;
    }

    private function getAnomaliesPerDay(int $studentId, string $date): array
    {
        $anomalies = [];

        // 1. Get Daily Attendance Status
        $daily = Query::find()
            ->from('attendance_daily_student')
            ->where(['student_id' => $studentId, 'attendance_date' => $date])
            ->one();

        // 2. Check "Present Daily but NO Lesson Attendance Record"
        if ($daily && (int)$daily['status'] === 1) {
            $classMember = Query::find()
                ->from('classroom_member')
                ->where(['student_id' => $studentId])
                ->one();

            if ($classMember) {
                $classId = $classMember['class_id'];
                $sessions = Query::find()
                    ->from('lesson_session')
                    ->leftJoin('teaching_schedule', 'lesson_session.teaching_schedule_id = teaching_schedule.id')
                    ->where([
                        'teaching_schedule.classroom_id' => $classId,
                        'lesson_session.session_date' => $date,
                        'lesson_session.status' => ['Present', 'Completed']
                    ])
                    ->all();

                foreach ($sessions as $session) {
                    $att = Query::find()
                        ->from('attendance_student')
                        ->where([
                            'lesson_session_id' => $session['id'],
                            'student_id' => $studentId
                        ])
                        ->one();

                    if (!$att) {
                        $anomalies[] = [
                            'type' => 'present_daily_no_lesson',
                            'severity' => 'warning',
                            'date' => $date,
                            'session_id' => $session['id'],
                            'session_info' => $session['material'] ?: ('Session ' . $session['id'])
                        ];
                    }
                }
            }
        }

        // 3. Check "Present in Lesson but Absent/No Record in Daily Attendance"
        $lessonAtts = Query::find()
            ->from('attendance_student')
            ->leftJoin('lesson_session', 'attendance_student.lesson_session_id = lesson_session.id')
            ->where([
                'attendance_student.student_id' => $studentId,
                'lesson_session.session_date' => $date,
                'attendance_student.status' => 1
            ])
            ->all();

        if (!empty($lessonAtts) && (!$daily || (int)$daily['status'] !== 1)) {
            $anomalies[] = [
                'type' => 'present_lesson_absent_daily',
                'severity' => 'error',
                'date' => $date,
                'info' => 'Present in ' . count($lessonAtts) . ' lesson(s)'
            ];
        }

        return $anomalies;
    }
}
