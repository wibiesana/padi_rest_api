<?php

namespace App\Controllers;

use App\Controllers\Base\ClassroomController as BaseController;
use App\Resources\ClassroomResource;
use Wibiesana\Padi\Core\Auth;


class ClassroomController extends BaseController
{
    /**
     * Override index to filter by teacher_id (homeroom) for teachers
     */
    public function index()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $page = max(1, (int)$this->request->query('page', 1));
            $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));

            // Eager load relations like in parent
            $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);

            // Return filtered results directly to avoid TypeMismatch on $this->model
            $result = $this->model->paginate($page, $perPage, ['teacher_id' => $userId]);
            return ClassroomResource::collection($result);
        }
        return parent::index();
    }


    /**
     * Override all to filter by teacher_id (homeroom) for teachers
     */
    public function all()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);

            // Return filtered results directly to avoid TypeMismatch on $this->model
            $result = $this->model->where(['teacher_id' => $userId]);
            return ClassroomResource::collection($result);
        }
        return parent::all();
    }


    /**
     * Override show to check authorization for homeroom
     */
    public function show()
    {
        $id = $this->request->param('id');
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        $classroom = $this->model->find($id);

        if (!$classroom) {
            throw new \Exception('Classroom not found', 404);
        }

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            if ($classroom['teacher_id'] != $userId) {
                throw new \Exception('Unauthorized access to homeroom dashboard', 403);
            }
        }

        return parent::show();
    }

    /**
     * Get all classes that the logged in teacher is associated with (as homeroom or teacher)
     */
    public function teacherClasses()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        if ($isAdmin) {
            // Admin can see all classes
            $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);
            $classes = $this->model->findQuery()
                ->where(['status' => 1])
                ->all();
            return ClassroomResource::collection($classes);
        }

        // For regular teachers, find their profile
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();
        $teacherId = $teacher ? $teacher['id'] : $userId;

        // Get classes from teaching schedule
        $classIdsFromSchedule = \App\Models\TeachingSchedule::findQuery()
            ->select('DISTINCT classroom_id')
            ->from('teaching_schedule')
            ->where(['teacher_id' => $teacherId])
            ->all();
        $classIds1 = array_column($classIdsFromSchedule, 'classroom_id');

        // Get classes where homeroom teacher
        $classIdsFromHomeroom = \App\Models\Classroom::findQuery()
            ->select('id')
            ->from('classroom')
            ->where(['teacher_id' => $teacherId])
            ->all();
        $classIds2 = array_column($classIdsFromHomeroom, 'id');

        $allClassIds = array_unique(array_filter(array_merge($classIds1, $classIds2)));

        if (empty($allClassIds)) {
            return ClassroomResource::collection([]);
        }

        $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);
        $classes = $this->model->findQuery()
            ->where(['id' => $allClassIds])
            ->andWhere(['status', '=', 1])
            ->all();

        // Load relations manually for findQuery results
        $this->model->loadRelations($classes);

        // Fetch student counts for these classes in bulk
        $counts = \App\Models\ClassroomMember::findQuery()
            ->select('class_id, COUNT(*) as total')
            ->where(['class_id' => $allClassIds])
            ->groupBy('class_id')
            ->all();

        $countMap = array_column($counts, 'total', 'class_id');

        foreach ($classes as &$class) {
            $class['students_count'] = (int)($countMap[$class['id']] ?? 0);
        }

        return ClassroomResource::collection($classes);
    }

    /**
     * Get unified summary matrix for a classroom (Students + Exams + Assignments)
     * Optimized to avoid redundant student data fetching
     */
    public function resultSummary()
    {
        $classId = $this->request->query('classroom_id');
        if (!$classId) {
            throw new \Exception('Classroom ID is required', 400);
        }

        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized', 401);
        }
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        // 1. Get associated teacher profile
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();
        $isHomeroom = false;

        $classroom = \App\Models\Classroom::findQuery()->where(['id', '=', $classId])->one();
        if ($classroom && $teacher && $classroom['teacher_id'] == $teacher['id']) {
            $isHomeroom = true;
        }

        // 2. Get students (Fetch once)
        $students = \Wibiesana\Padi\Core\Query::find()
            ->select('student.id, student.name')
            ->from('student')
            ->innerJoin('classroom_member', 'student.id = classroom_member.student_id')
            ->where(['classroom_member.class_id' => $classId])
            ->orderBy('student.name ASC')
            ->all();

        $studentIds = array_column($students, 'id');

        // 3. Get EXAMS & Matrix
        $exams = [];
        $examMatrix = [];
        if (!empty($studentIds)) {
            $examBaseQuery = \App\Models\Exam::findQuery()
                ->select('DISTINCT exam.id, exam.name, subject.name as subject_name')
                ->from('exam')
                ->leftJoin('subject', 'exam.subject_id = subject.id');

            if (!$isAdmin && !$isHomeroom) {
                $examBaseQuery->leftJoin('exam_examiners', 'exam.id = exam_examiners.exam_id');
                $orConditions = ['OR'];
                $orConditions[] = ['exam.created_by', '=', $userId];
                if ($teacher) {
                    $orConditions[] = ['exam_examiners.teacher_id', '=', $teacher['id']];
                }
                $examBaseQuery->andWhere($orConditions);
            }

            // Exams linked to class OR with results in class
            $examsExplicit = (clone $examBaseQuery)->innerJoin('exam_class', 'exam.id = exam_class.exam_id')
                ->andWhere(['exam_class.class_id' => $classId])->all();
            $examsWithResults = (clone $examBaseQuery)->innerJoin('exam_result', 'exam.id = exam_result.exam_id')
                ->innerJoin('classroom_member', 'exam_result.student_id = classroom_member.student_id')
                ->andWhere(['classroom_member.class_id' => $classId])->andWhere(['exam_result.status' => [4, 5]])->all();

            $mergedExams = array_merge($examsExplicit, $examsWithResults);
            $seenExams = [];
            foreach ($mergedExams as $e) {
                if (!isset($seenExams[$e['id']])) {
                    $exams[] = $e;
                    $seenExams[$e['id']] = true;
                }
            }

            $examIds = array_column($exams, 'id');
            if (!empty($examIds)) {
                $results = \App\Models\ExamResult::findQuery()
                    ->where(['exam_id' => $examIds])->andWhere(['student_id' => $studentIds])
                    ->andWhere(['status' => [4, 5]])->all();
                foreach ($results as $res) {
                    $examMatrix[$res['student_id']][$res['exam_id']] = (float)$res['total_result'];
                }
            }
        }

        // 4. Get ASSIGNMENTS & Matrix
        $assignments = [];
        $asgnMatrix = [];
        if (!empty($studentIds)) {
            $assignments = \Wibiesana\Padi\Core\Query::find()
                ->select('assignment.id, assignment.name, subject.name as subject_name')
                ->from('assignment')
                ->innerJoin('assignment_class', 'assignment.id = assignment_class.assignment_id')
                ->leftJoin('subject', 'assignment.subject_id = subject.id')
                ->where(['assignment_class.classroom_id' => $classId])->all();

            $asgnIds = array_column($assignments, 'id');
            if (!empty($asgnIds)) {
                $scores = \Wibiesana\Padi\Core\Query::find()
                    ->from('assignment_result')
                    ->where(['IN', 'assignment_id', $asgnIds])->andWhere(['IN', 'created_by', $studentIds])->all();
                foreach ($scores as $score) {
                    $asgnMatrix[$score['created_by']][$score['assignment_id']] = (float)$score['score'];
                }
            }
        }

        return [
            'students' => $students,
            'exams' => $exams,
            'assignments' => $assignments,
            'examMatrix' => $examMatrix,
            'assignmentMatrix' => $asgnMatrix
        ];
    }
}
