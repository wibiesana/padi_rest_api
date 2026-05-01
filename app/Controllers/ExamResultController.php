<?php

namespace App\Controllers;

use App\Controllers\Base\ExamResultController as BaseController;

class ExamResultController extends BaseController
{
    /**
     * Get all exam results with pagination, filtered by teacher association
     */
    public function index()
    {
        $user = \Wibiesana\Padi\Core\Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search');
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : "exam_result.id DESC";

        $query = \Wibiesana\Padi\Core\Query::find()
            ->select("exam_result.*")
            ->from("exam_result")
            ->leftJoin('exam', 'exam_result.exam_id = exam.id')
            ->leftJoin('student', 'exam_result.student_id = student.id')
            ->leftJoin('subject', 'exam.subject_id = subject.id')
            ->leftJoin('exam_examiners', 'exam.id = exam_examiners.exam_id');

        // Apply filters (Identity/Role)
        if (!$isAdmin) {
            $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->one();
            $teacherId = $teacher ? $teacher['id'] : $userId;

            // Teacher can see results for exams they created or assigned to
            $query->andWhere([
                'OR',
                ['exam.created_by' => $userId],
                ['exam_examiners.teacher_id' => $teacherId]
            ]);
        }

        // Apply search keyword
        if (!empty($search)) {
            $keyword = "%{$search}%";
            $query->andWhere([
                'OR',
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'student.name', $keyword],
                ['LIKE', 'subject.name', $keyword]
            ]);
        }

        $query->orderBy($orderBy);
        $query->groupBy('exam_result.id'); // Avoid duplicates
        $result = $query->paginate($perPage, $page);

        if (!empty($result['data'])) {
            $this->model->loadRelations($result['data']);
        }

        $paginatedResult = [
            'data' => $result['data'],
            'meta' => [
                'total' => (int)$result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page']
            ]
        ];

        return \App\Resources\ExamResultResource::collection($paginatedResult);
    }

    /**
     * Get all exam results without pagination
     */
    public function all()
    {
        $user = \Wibiesana\Padi\Core\Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        $search = $this->request->query('search');
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : "exam_result.id DESC";

        $query = \Wibiesana\Padi\Core\Query::find()
            ->select("exam_result.*")
            ->from("exam_result")
            ->leftJoin('exam', 'exam_result.exam_id = exam.id')
            ->leftJoin('student', 'exam_result.student_id = student.id')
            ->leftJoin('exam_examiners', 'exam.id = exam_examiners.exam_id');

        if (!$isAdmin) {
            $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->one();
            $teacherId = $teacher ? $teacher['id'] : $userId;

            $query->andWhere([
                'OR',
                ['exam.created_by' => $userId],
                ['exam_examiners.teacher_id' => $teacherId]
            ]);
        }

        if (!empty($search)) {
            $keyword = "%{$search}%";
            $query->andWhere([
                'OR',
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'student.name', $keyword]
            ]);
        }

        $query->orderBy($orderBy);
        $query->groupBy('exam_result.id');
        $result = $query->limit(100)->all();

        if (!empty($result)) {
            $this->model->loadRelations($result);
        }

        return \App\Resources\ExamResultResource::collection($result);
    }
    /**
     * Get exam results for current logged in student
     */
    public function myResults()
    {
        $userId = \Wibiesana\Padi\Core\Auth::userId();
        if (!$userId) {
            throw new \Exception('Unauthorized', 401);
        }

        // Results with status 4 (waiting grading) or 5 (done)
        $this->model->with(['exam', 'exam.subject']);
        $results = $this->model->findQuery()
            ->where(['student_id' => $userId])
            ->andWhere(['status' => [4, 5]])
            ->orderBy('updated_at DESC')
            ->all();

        if (!empty($results)) {
            $this->model->loadRelations($results);
        }

        return \App\Resources\ExamResultResource::collection($results);
    }

    /**
     * Get summary of exam results for a classroom
     */
    public function teacherClassSummary()
    {
        $classId = $this->request->query('classroom_id');
        if (!$classId) {
            throw new \Exception('Classroom ID is required', 400);
        }

        $user = \Wibiesana\Padi\Core\Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized', 401);
        }
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        // Find associated teacher profile
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();
        $isHomeroom = false;

        $classroom = \App\Models\Classroom::findQuery()->where(['id', '=', $classId])->one();
        if ($classroom && $teacher && $classroom['teacher_id'] == $teacher['id']) {
            $isHomeroom = true;
        }

        // Get all students in this class
        $students = \App\Models\Student::findQuery()
            ->select('student.id, student.name')
            ->from('student')
            ->leftJoin('classroom_member', 'student.id = classroom_member.student_id')
            ->where(['classroom_member.class_id' => $classId])
            ->all();

        // Build Exam Query
        $examBaseQuery = \App\Models\Exam::findQuery()
            ->select('DISTINCT exam.id, exam.name, subject.name as subject_name')
            ->from('exam')
            ->leftJoin('subject', 'exam.subject_id = subject.id');

        // If not Admin or Homeroom, restrict exams
        if (!$isAdmin && !$isHomeroom) {
            // Restrict by Creator or Examiner
            $examBaseQuery->leftJoin('exam_examiners', 'exam.id = exam_examiners.exam_id');

            $orConditions = ['OR'];
            $orConditions[] = ['exam.created_by', '=', $userId];
            if ($teacher) {
                $orConditions[] = ['exam_examiners.teacher_id', '=', $teacher['id']];
            }

            $examBaseQuery->andWhere($orConditions);
        }

        // 1. Get exams explicitly linked to this class
        $examsExplicitQuery = clone $examBaseQuery;
        $examsExplicit = $examsExplicitQuery
            ->innerJoin('exam_class', 'exam.id = exam_class.exam_id')
            ->andWhere(['exam_class.class_id' => $classId])
            ->all();

        // 2. Get exams that have results for students in this class
        $examsWithResultsQuery = clone $examBaseQuery;
        $examsWithResults = $examsWithResultsQuery
            ->innerJoin('exam_result', 'exam.id = exam_result.exam_id')
            ->innerJoin('classroom_member', 'exam_result.student_id = classroom_member.student_id')
            ->andWhere(['classroom_member.class_id' => $classId])
            ->andWhere(['exam_result.status' => [4, 5]])
            ->all();

        // Merge and unique by ID
        $allExams = array_merge($examsExplicit, $examsWithResults);
        $uniqueExams = [];
        $seen = [];
        foreach ($allExams as $e) {
            if (!isset($seen[$e['id']])) {
                $uniqueExams[] = $e;
                $seen[$e['id']] = true;
            }
        }
        $exams = $uniqueExams;

        $examIds = array_column($exams, 'id');
        $studentIds = array_column($students, 'id');

        if (empty($examIds) || empty($studentIds)) {
            return [
                'students' => $students,
                'exams' => $exams,
                'matrix' => []
            ];
        }

        // Get results
        $results = \App\Models\ExamResult::findQuery()
            ->where(['exam_id' => $examIds])
            ->andWhere(['student_id' => $studentIds])
            ->andWhere(['status' => [4, 5]])
            ->orderBy('updated_at ASC')
            ->all();

        $matrix = [];
        foreach ($results as $res) {
            $matrix[$res['student_id']][$res['exam_id']] = (float)$res['total_result'];
        }

        return [
            'students' => $students,
            'exams' => $exams,
            'matrix' => $matrix
        ];
    }
}
