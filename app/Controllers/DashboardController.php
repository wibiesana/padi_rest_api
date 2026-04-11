<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Auth;
use Wibiesana\Padi\Core\Query;

class DashboardController extends Controller
{
    /**
     * Get attendance dashboard statistics
     * GET /dashboard/attendance-stats
     * @param date optional date filter (YYYY-MM-DD format)
     */
    public function getAttendanceStats()
    {
        // Get date from query parameter, default to today
        $today = $_GET['date'] ?? date('Y-m-d');
        $classId = $_GET['class_id'] ?? null;
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        // Role-based classroom filtering for teachers
        $filteredClassIds = null;
        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            // Get classes where user is subject teacher
            $teachingClasses = Query::find()
                ->from('teaching_schedule')
                ->select(['DISTINCT classroom_id'])
                ->where(['teacher_id' => $userId])
                ->all();

            // Get classes where user is homeroom teacher
            $homeroomClasses = Query::find()
                ->from('classroom')
                ->select(['id'])
                ->where(['teacher_id' => $userId])
                ->all();

            $filteredClassIds = array_unique(array_merge(
                array_column($teachingClasses, 'classroom_id'),
                array_column($homeroomClasses, 'id')
            ));

            if (empty($filteredClassIds)) {
                $filteredClassIds = [0]; // Force no results if no classes assigned
            }
        }

        // Get student attendance stats
        $studentQuery = Query::find()
            ->from('attendance_daily_student')
            ->select([
                'attendance_daily_student.*',
                'student.name as student_name'
            ])
            ->leftJoin('student', 'attendance_daily_student.student_id = student.id')
            ->where(['attendance_daily_student.attendance_date' => $today]);

        if ($classId) {
            $studentQuery->leftJoin('classroom_member', 'student.id = classroom_member.student_id')
                ->andWhere(['classroom_member.class_id' => $classId]);
        } elseif ($filteredClassIds) {
            $studentQuery->leftJoin('classroom_member', 'student.id = classroom_member.student_id')
                ->andWhere(['classroom_member.class_id' => $filteredClassIds]);
        }

        $allStudentRecords = $studentQuery->all();
        $studentRecords = array_slice($allStudentRecords, 0, 10);

        // Get teacher attendance stats
        $teacherQuery = Query::find()
            ->from('attendance_daily_teacher')
            ->select([
                'attendance_daily_teacher.*',
                'teacher.name as teacher_name'
            ])
            ->leftJoin('teacher', 'attendance_daily_teacher.teacher_id = teacher.id')
            ->where(['attendance_daily_teacher.attendance_date' => $today])
            ->orderBy('attendance_daily_teacher.created_at DESC');

        // Note: For teachers, we might still show all teacher attendance or just their own?
        // Usually, dashboard shows overview. We keep it as is unless it's too much.
        // If it's a teacher dashboard specifically, maybe only their own?
        if ($filteredClassIds && $user) {
            // For teacher role, maybe they only want to see their own daily attendance status on the summary?
            // Or at least show their own prominently. Let's keep it restricted to their own for now to be "specific for teacher".
            $teacherQuery->andWhere(['attendance_daily_teacher.teacher_id' => $userId]);
        }

        $allTeacherRecords = $teacherQuery->all();
        $teacherRecords = array_slice($allTeacherRecords, 0, 10);

        // Get lesson sessions for today
        $sessionQuery = Query::find()
            ->from('lesson_session')
            ->where(['session_date' => $today]);

        if ($classId) {
            $sessionQuery->leftJoin('teaching_schedule', 'lesson_session.teaching_schedule_id = teaching_schedule.id')
                ->andWhere(['teaching_schedule.classroom_id' => $classId]);
        } elseif ($filteredClassIds) {
            $sessionQuery->leftJoin('teaching_schedule', 'lesson_session.teaching_schedule_id = teaching_schedule.id')
                ->andWhere(['teaching_schedule.classroom_id' => $filteredClassIds]);
        }

        $allSessionRecords = $sessionQuery->orderBy('lesson_session.created_at DESC')->all();
        $sessionRecords = array_slice($allSessionRecords, 0, 10);

        // Detect attendance anomalies
        $anomalies = [];

        // Build where clause for class filtering - Get present students (Status 1)
        // Adjust status check to be more robust (1 or '1')
        $dailyWhereClause = [
            'attendance_daily_student.attendance_date' => $today,
            'attendance_daily_student.status' => 1
        ];

        // Add class filter if provided
        if ($classId) {
            $dailyWhereClause['classroom.id'] = $classId;
        }

        // Get all students present in daily attendance today
        $presentDailyQuery = Query::find()
            ->from('attendance_daily_student')
            ->select([
                'attendance_daily_student.student_id',
                'student.name as student_name',
                'student.nis as student_nis',
                'classroom.name as class_name',
                'classroom.id as class_id'
            ])
            ->leftJoin('student', 'attendance_daily_student.student_id = student.id')
            ->leftJoin('classroom_member', 'student.id = classroom_member.student_id')
            ->leftJoin('classroom', 'classroom_member.class_id = classroom.id')
            ->where($dailyWhereClause);

        $presentDailyStudents = $presentDailyQuery->all();

        foreach ($presentDailyStudents as $student) {
            // Check if student has any lesson attendance records today
            // We check for ANY record in attendance_student for today's sessions
            $lessonAttCount = Query::find()
                ->from('attendance_student')
                ->leftJoin('lesson_session', 'attendance_student.lesson_session_id = lesson_session.id')
                ->where([
                    'attendance_student.student_id' => $student['student_id'],
                    'lesson_session.session_date' => $today
                ])
                ->count();

            // If Present Daily but NO Lesson Attendance Record
            if ($lessonAttCount == 0) {
                // Get teacher name from lesson_session scheduled for this class today
                $teacherInfo = Query::find()
                    ->from('lesson_session')
                    ->select(['teacher.name as teacher_name'])
                    ->leftJoin('teacher', 'lesson_session.teacher_id = teacher.id')
                    ->leftJoin('teaching_schedule', 'lesson_session.teaching_schedule_id = teaching_schedule.id')
                    ->where([
                        'lesson_session.session_date' => $today,
                        'teaching_schedule.classroom_id' => $student['class_id']
                    ])
                    ->limit(1)
                    ->one();

                $anomalies[] = [
                    'type' => 'present_daily_no_lesson',
                    'student_id' => $student['student_id'],
                    'student_name' => $student['student_name'],
                    'student_nis' => $student['student_nis'],
                    'class_name' => $student['class_name'] ?? '-',
                    'teacher_name' => isset($teacherInfo['teacher_name']) ? $teacherInfo['teacher_name'] : '-',
                    'severity' => 'warning',
                    'date' => $today
                ];
            }
        }

        // Get students with lesson attendance but absent/not in daily attendance
        $lessonWhereClause = [
            'lesson_session.session_date' => $today,
            'attendance_student.status' => 1
        ];

        // Add class filter if provided
        if ($classId) {
            $lessonWhereClause['classroom.id'] = $classId;
        }

        $lessonAttQuery = Query::find()
            ->from('attendance_student')
            ->select([
                'attendance_student.student_id',
                'MAX(student.name) as student_name',
                'MAX(student.nis) as student_nis',
                'MAX(classroom.name) as class_name',
                'MAX(classroom.id) as class_id',
                'MAX(teacher.name) as teacher_name',
                'MAX(lesson_session.teacher_id) as teacher_id'
            ])
            ->leftJoin('lesson_session', 'attendance_student.lesson_session_id = lesson_session.id')
            ->leftJoin('teacher', 'lesson_session.teacher_id = teacher.id')
            ->leftJoin('student', 'attendance_student.student_id = student.id')
            ->leftJoin('classroom_member', 'student.id = classroom_member.student_id')
            ->leftJoin('classroom', 'classroom_member.class_id = classroom.id')
            ->where($lessonWhereClause);

        $lessonAttToday = $lessonAttQuery->groupBy('attendance_student.student_id')->all();

        foreach ($lessonAttToday as $student) {
            // Check daily attendance status
            $dailyAtt = Query::find()
                ->from('attendance_daily_student')
                ->where([
                    'student_id' => $student['student_id'],
                    'attendance_date' => $today
                ])
                ->one();

            if (!$dailyAtt || $dailyAtt['status'] != 1) {
                $anomalies[] = [
                    'type' => 'present_lesson_absent_daily',
                    'student_id' => $student['student_id'],
                    'student_name' => $student['student_name'],
                    'student_nis' => $student['student_nis'],
                    'class_name' => $student['class_name'] ?? '-',
                    'teacher_name' => $student['teacher_name'] ?? '-',
                    'severity' => 'error',
                    'date' => $today
                ];
            }
        }


        return $this->json([
            'students' => [
                'data' => $studentRecords,
                'stats' => [
                    'total' => count($allStudentRecords),
                    'present' => count(array_filter($allStudentRecords, fn($r) => $r['status'] == 1)),
                    'permission' => count(array_filter($allStudentRecords, fn($r) => $r['status'] == 2)),
                    'sick' => count(array_filter($allStudentRecords, fn($r) => $r['status'] == 3)),
                    'absent' => count(array_filter($allStudentRecords, fn($r) => $r['status'] == 4)),
                ]
            ],
            'anomalies' => $anomalies,
            'teachers' => [
                'data' => $teacherRecords,
                'stats' => [
                    'total' => count($allTeacherRecords),
                    'present' => count(array_filter($allTeacherRecords, fn($r) => $r['status'] == 1)),
                    'absent' => count(array_filter($allTeacherRecords, fn($r) => $r['status'] != 1)),
                ]
            ],
            'sessions' => [
                'data' => $sessionRecords,
                'stats' => [
                    'total' => count($allSessionRecords),
                    'completed' => count(array_filter($allSessionRecords, fn($r) => in_array($r['status'], ['Present', 'Completed']))),
                    'pending' => count(array_filter($allSessionRecords, fn($r) => !in_array($r['status'], ['Present', 'Completed']))),
                ]
            ]
        ]);
    }
}
