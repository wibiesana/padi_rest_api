<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Auth;
use App\Models\Notification;
use App\Models\Teacher;
use App\Models\Semester;
use App\Models\Department;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\LessonSession;
use App\Models\TeachingSchedule;
use App\Models\QuestionBank;

class DataCenterController extends Controller
{
    /**
     * Handle data center requests
     * GET /data-center/{type}
     */
    public function index()
    {
        $type = $this->request->param('type');

        // Map types to their respective Models
        $modelMap = [
            'notification' => Notification::class,
            'teachers'     => Teacher::class,
            'teacher'      => Teacher::class,
            'semesters'    => Semester::class,
            'semester'     => Semester::class,
            'departments'  => Department::class,
            'department'   => Department::class,
            'classes'      => Classroom::class,
            'classrooms'   => Classroom::class,
            'classroom'    => Classroom::class,
            'subjects'     => Subject::class,
            'subject'      => Subject::class,
            'students'     => Student::class,
            'student'      => Student::class,
            'lesson-sessions' => LessonSession::class,
            'teaching-schedules' => TeachingSchedule::class,
            'question-banks' => QuestionBank::class,
            'question-bank'  => QuestionBank::class,
        ];

        if (isset($modelMap[$type])) {
            $model = new $modelMap[$type]();
            $query = $model->findQuery();

            // Set columns to id and name as requested
            if ($type === 'notification') {
                $query->select(['id', 'title as name']);
            } elseif ($type === 'question-banks' || $type === 'question-bank') {
                $query->select(['id', 'name', 'exam_event_id']);
            } elseif (in_array($type, ['classes', 'classrooms', 'classroom'])) {
                $query->select(['classroom.id', 'classroom.name', 'classroom.semester_id', 'classroom.teacher_id']);
            } elseif ($type === 'teaching-schedules') {
                $query->select(['id', 'subject_id', 'teacher_id', 'classroom_id', 'semester_id']);
            } elseif (in_array($type, ['students', 'student'])) {
                $query->select(['student.id', 'student.name', 'classroom_member.class_id'])
                    ->leftJoin('classroom_member', 'student.id = classroom_member.student_id');

                // Filter by class_ids if provided (comma-separated)
                $classIds = $this->request->query('class_ids');
                if ($classIds) {
                    $classIdArr = array_map('intval', explode(',', $classIds));
                    $query->andWhere(['classroom_member.class_id' => $classIdArr]);
                }
            } else {
                $query->select(['id', 'name']);
            }

            // Filter by active status if the table supports it
            // Based on base models, these types have status or status column
            $statusTypes = [
                'teachers',
                'teacher',
                'semesters',
                'semester',
                'students',
                'student',
                'question-banks',
                'question-bank',
                'classes',
                'classrooms',
                'classroom',
                'subjects',
                'subject',
                'departments',
                'department'
            ];
            $activeTypes = [];

            if (in_array($type, $statusTypes)) {
                $tableName = $model->getTable();
                $query->where(["{$tableName}.status", '=', 1]);
            } elseif (in_array($type, $activeTypes)) {
                $tableName = $model->getTable();
                $query->where(["{$tableName}.status", '=', 1]);
            }

            // Exclude superadmin (id=1) from teacher list
            if (in_array($type, ['teachers', 'teacher'])) {
                $query->andWhere(['id', '!=', 1]);
            }

            // Optimization for question-banks: filter by event_exam_id if provided
            if ($type === 'question-banks' && $this->request->query('event_id')) {
                $query->andWhere(['exam_event_id', '=', $this->request->query('event_id')]);
            }

            // Handle role-based filtering for non-admin users
            $user = Auth::user();
            $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

            $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

            // We want to avoid filtering semesters by created_by, as they are global resources
            if ($role && $role !== 'superadmin' && $role !== 'admin' && !in_array($type, ['semesters', 'semester', 'subjects', 'subject'])) {
                if (in_array($type, ['classes', 'classrooms', 'classroom'])) {
                    if ($userId) {
                        $query->leftJoin('teaching_schedule', 'classroom.id = teaching_schedule.classroom_id')
                            ->andWhere(['OR',
                                ['classroom.teacher_id', '=', $userId],
                                ['teaching_schedule.teacher_id', '=', $userId]
                            ])
                            ->groupBy('classroom.id');
                    }
                } elseif ($type === 'teaching-schedules') {
                    if ($userId) {
                        $query->andWhere(['teacher_id', '=', $userId]);
                    }
                } elseif (!in_array($type, ['students', 'student'])) {
                    // Filter records created by the user for other types (skip students as they are global)
                    $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
                    if ($userId) {
                        $query->andWhere(['created_by', '=', $userId]);
                    }
                }
            }

            return $query->all();
        }

        // Handle special cases
        switch ($type) {
            case 'today-absence':
                return $this->checkTodayAbsence();
            case 'dashboard-admin':
                return $this->getDashboardAdminStats();
            case 'dashboard-teacher':
                return $this->getDashboardTeacherStats();
            default:
                throw new \Exception("Data type '$type' not found", 404);
        }
    }

    private function checkTodayAbsence()
    {
        // TODO: Implement actual absence check based on user role
        // For example, check if attendance record exists for created_by = current_user_id and created_at = today
        return false;
    }

    private function getDashboardAdminStats()
    {
        // Counts for dashboard
        return [
            'teacherCount' => Teacher::findQuery()->count(),
            'studentCount' => Student::findQuery()->count(),
            'classCount'   => Classroom::findQuery()->count(),
            'tuCount'      => Staff::findQuery()->count(),
        ];
    }

    private function getDashboardTeacherStats()
    {
        $user = Auth::user();
        if (!$user) return [];

        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        $teacherId = $userId;

        // Murid yang dia ajar (unique students across all classes he teaches)
        $studentCount = TeachingSchedule::findQuery()
            ->leftJoin('classroom_member', 'teaching_schedule.classroom_id = classroom_member.classroom_id')
            ->where(['teaching_schedule.teacher_id' => $teacherId])
            ->andWhere(['teaching_schedule.status' => 1])
            ->count('DISTINCT classroom_member.student_id');

        // Mata pelajaran yang diampu
        $subjectCount = TeachingSchedule::findQuery()
            ->where(['teacher_id' => $teacherId])
            ->andWhere(['status' => 1])
            ->count('DISTINCT subject_id');

        // Kelas yang di ajar (sebagai pengajar di jadwal)
        $classCount = TeachingSchedule::findQuery()
            ->where(['teacher_id' => $teacherId])
            ->andWhere(['status' => 1])
            ->count('DISTINCT classroom_id');

        // Kelas yang dia jadi wali kelasnya
        $homeroomCount = Classroom::findQuery()
            ->where(['teacher_id' => $teacherId])
            ->andWhere(['status' => 1])
            ->count();

        return [
            'studentCount'  => $studentCount,
            'subjectCount'  => $subjectCount,
            'classCount'    => $classCount,
            'homeroomCount' => $homeroomCount,
        ];
    }
}
