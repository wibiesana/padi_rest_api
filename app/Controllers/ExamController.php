<?php

namespace App\Controllers;

use App\Controllers\Base\ExamController as BaseController;
use App\Resources\ExamResource;
use Wibiesana\Padi\Core\Auth;
use Wibiesana\Padi\Core\Database;
use Wibiesana\Padi\Core\Query;

class ExamController extends BaseController
{
    /**
     * Get all exams with pagination, filtered by creator/examiner for teachers
     */
    public function index()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search');
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : "exam.id DESC";

        $query = Query::find()
            ->select("exam.*")
            ->from("exam")
            ->leftJoin('exam_events AS exam_events', 'exam.exam_event_id = exam_events.id')
            ->leftJoin('question_bank AS question_bank', 'exam.question_bank_id = question_bank.id')
            ->leftJoin('subject AS subject', 'exam.subject_id = subject.id')
            ->leftJoin('users AS users', 'exam.created_by = users.id')
            ->leftJoin('exam_examiners', 'exam.id = exam_examiners.exam_id');

        // Apply filters (Identity/Role)
        if (!$isAdmin) {
            $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->one();
            $teacherId = $teacher ? $teacher['id'] : $userId;

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
                ['LIKE', 'exam_events.name', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'subject.name', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'exam.token', $keyword]
            ]);
        }

        $query->orderBy($orderBy);
        $query->groupBy('exam.id'); // Avoid duplicates due to examiner join
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

        return ExamResource::collection($paginatedResult);
    }

    /**
     * Get all exams without pagination, filtered by creator/examiner for teachers
     */
    public function all()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        $search = $this->request->query('search');
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : "exam.id DESC";

        $query = Query::find()
            ->select("exam.*")
            ->from("exam")
            ->leftJoin('exam_events AS exam_events', 'exam.exam_event_id = exam_events.id')
            ->leftJoin('question_bank AS question_bank', 'exam.question_bank_id = question_bank.id')
            ->leftJoin('subject AS subject', 'exam.subject_id = subject.id')
            ->leftJoin('users AS users', 'exam.created_by = users.id')
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
                ['LIKE', 'exam_events.name', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'subject.name', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'exam.name', $keyword],
                ['LIKE', 'exam.token', $keyword]
            ]);
        }

        $query->orderBy($orderBy);
        $query->groupBy('exam.id');
        $result = $query->limit(100)->all();

        if (!empty($result)) {
            $this->model->loadRelations($result);
        }

        return ExamResource::collection($result);
    }

    /**
     * Get single exam with all relations needed for form
     * GET /exams/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $this->model->with([
            'createdBy:id,username',
            'questionBank:id,name',
            'subject:id,name',
            'updatedBy:id,username',
            'classes:id,name',
            'supervisors:id,name',
            'examiners:id,name',
            'supervisorAssignments.teacher',
            'supervisorAssignments.classroom',
            'examresults', // Load the collection
            'examresults.student' // Load the student for each result
        ]);

        $exam = $this->model->find($id);

        if ($exam) {
            $workingCount = \App\Models\ExamResult::findQuery()
                ->where(['exam_id' => $id])
                ->andWhere(['status', '>=', 2])
                ->count();

            if (is_object($exam)) {
                $exam->setAttribute('working_student_count', $workingCount);
            } else {
                $exam['working_student_count'] = $workingCount;
            }
        }

        if (!$exam) {
            throw new \Exception('Exam not found', 404);
        }

        return ExamResource::make($exam);
    }

    /**
     * Create new exam with classes, supervisors, and examiners
     * POST /exams
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'exam_event_id' => 'integer',
            'question_bank_id' => 'required|integer',
            'subject_id' => 'integer',
            'semester_id' => 'integer',
            'status' => 'integer',
            'token' => 'string|max:6',
            'test_duration' => 'integer',
            'use_dynamic_token' => 'integer',
            'show_pg' => 'integer',
            'show_essay' => 'integer',
            'show_result' => 'integer',
            'percentage_mc_value' => 'integer',
            'percentage_essay_value' => 'integer',
            'is_random' => 'integer',
            'randomize_questions' => 'integer',
            'randomize_options' => 'integer',
            'lock_on_switch' => 'integer',
            'require_supervisor' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'class_ids' => 'array',
            'participant_ids' => 'array',
            'supervisor_data' => 'array', // Array of {teacher_id, classroom_id, description}
            'examiner_ids' => 'array'    // Array of teacher IDs
        ]);

        // Extract related data
        $classIds = $validated['class_ids'] ?? [];
        $participantIds = $validated['participant_ids'] ?? [];
        $supervisorData = $validated['supervisor_data'] ?? [];
        $examinerIds = $validated['examiner_ids'] ?? [];

        unset($validated['class_ids'], $validated['participant_ids'], $validated['supervisor_data'], $validated['examiner_ids']);

        $db = Database::connection();

        try {
            $db->beginTransaction();

            $examId = $this->model->create($validated);

            if (!empty($classIds) || !empty($participantIds)) {
                $this->insertExamClasses($examId, $classIds);
                $this->createInitialResults($examId, $classIds, (int)$validated['question_bank_id'], (int)($validated['test_duration'] ?? 60), $participantIds);
            }

            if (!empty($supervisorData)) {
                $this->insertExamSupervisors($examId, $supervisorData);
            }

            if (!empty($examinerIds)) {
                $this->insertExamExaminers($examId, $examinerIds);
            }

            $db->commit();

            $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'subject:id,name', 'updatedBy:id,username', 'classes:id,name', 'supervisors:id,name', 'examiners:id,name', 'supervisorAssignments.teacher', 'supervisorAssignments.classroom', 'examresults.student']);
            $exam = $this->model->find($examId);

            return $this->created(ExamResource::make($exam));
        } catch (\PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $this->databaseError('Failed to create exam', $e);
        }
    }

    /**
     * Update exam with relations
     */
    public function update()
    {
        $id = $this->request->param('id');
        $exam = $this->model->find($id);

        if (!$exam) {
            throw new \Exception('Exam not found', 404);
        }

        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'question_bank_id' => 'required|integer',
            'subject_id' => 'integer',
            'semester_id' => 'integer',
            'status' => 'integer',
            'token' => 'string|max:6',
            'test_duration' => 'integer',
            'use_dynamic_token' => 'integer',
            'show_pg' => 'integer',
            'show_essay' => 'integer',
            'show_result' => 'integer',
            'percentage_mc_value' => 'integer',
            'percentage_essay_value' => 'integer',
            'is_random' => 'integer',
            'randomize_questions' => 'integer',
            'randomize_options' => 'integer',
            'lock_on_switch' => 'integer',
            'require_supervisor' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'class_ids' => 'array',
            'participant_ids' => 'array',
            'supervisor_data' => 'array',
            'examiner_ids' => 'array'
        ]);

        $classIds = $validated['class_ids'] ?? [];
        $participantIds = $validated['participant_ids'] ?? [];
        $supervisorData = $validated['supervisor_data'] ?? [];
        $examinerIds = $validated['examiner_ids'] ?? [];

        unset($validated['class_ids'], $validated['participant_ids'], $validated['supervisor_data'], $validated['examiner_ids']);

        $db = Database::connection();

        try {
            $db->beginTransaction();

            $this->model->update($id, $validated);

            $rawPayload = $this->request->all();

            // Sync Classes & Participants only if present in request
            if (array_key_exists('class_ids', $rawPayload) || array_key_exists('participant_ids', $rawPayload)) {
                $this->deleteExamClasses($id);
                if (!empty($classIds)) {
                    $this->insertExamClasses($id, $classIds);
                }
                $this->createInitialResults((int)$id, $classIds, (int)$validated['question_bank_id'], (int)($validated['test_duration'] ?? 60), $participantIds);
            }

            // Sync Supervisors only if present in request
            if (array_key_exists('supervisor_data', $rawPayload)) {
                $this->deleteExamSupervisors($id);
                if (!empty($supervisorData)) {
                    $this->insertExamSupervisors($id, $supervisorData);
                }
            }

            // Sync Examiners only if present in request
            if (array_key_exists('examiner_ids', $rawPayload)) {
                $this->deleteExamExaminers($id);
                if (!empty($examinerIds)) {
                    $this->insertExamExaminers($id, $examinerIds);
                }
            }

            $db->commit();

            $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'subject:id,name', 'updatedBy:id,username', 'classes:id,name', 'supervisors:id,name', 'examiners:id,name', 'supervisorAssignments.teacher', 'supervisorAssignments.classroom', 'examresults.student']);

            return ExamResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $this->databaseError('Failed to update exam', $e);
        }
    }

    /**
     * Get data for student exam card
     */
    public function getStudentCardData()
    {
        $userId = $this->request->query('user_id');

        if (!$userId) {
            throw new \Exception('User ID is required', 400);
        }

        // 1. Get Student Profile
        $studentModel = new \App\Models\Student();
        $student = $studentModel->find($userId);

        if ($student) {
            $wrapped = [$student];
            $studentModel->afterLoad($wrapped);
            $student = $wrapped[0];
        }

        // 2. Get Exams for this student
        $exams = \App\Models\Exam::findQuery()
            ->select('exam.*, subject.name as subject_name, classroom.name as room_name')
            ->innerJoin('exam_class', 'exam.id = exam_class.exam_id')
            ->innerJoin('classroom_member', 'exam_class.class_id = classroom_member.class_id')
            ->leftJoin('subject', 'exam.subject_id = subject.id')
            ->leftJoin('classroom', 'exam_class.class_id = classroom.id')
            ->where(['classroom_member.student_id' => $userId])
            ->andWhere(['exam.status' => 1])
            ->all();

        return [
            'success' => true,
            'student' => $student,
            'exams' => $exams
        ];
    }

    private function insertExamClasses(int $examId, array $classIds): void
    {
        $rows = array_map(fn($id) => ['exam_id' => $examId, 'class_id' => $id], $classIds);
        (new \App\Models\ExamClass())->batchInsert($rows);
    }

    private function deleteExamClasses(int $examId): void
    {
        \App\Models\ExamClass::findQuery()
            ->where(['exam_id' => $examId])
            ->delete();
    }

    private function insertExamSupervisors(int $examId, array $data): void
    {
        $rows = [];
        foreach ($data as $item) {
            if (empty($item['teacher_id'])) continue;
            $rows[] = [
                'exam_id' => $examId,
                'teacher_id' => $item['teacher_id'],
                'classroom_id' => $item['classroom_id'] ?? null,
                'description' => $item['description'] ?? null
            ];
        }

        if (!empty($rows)) {
            (new \App\Models\ExamSupervisor())->batchInsert($rows);
        }
    }

    private function deleteExamSupervisors(int $examId): void
    {
        \App\Models\ExamSupervisor::findQuery()
            ->where(['exam_id' => $examId])
            ->delete();
    }

    private function insertExamExaminers(int $examId, array $ids): void
    {
        $rows = array_map(fn($id) => ['exam_id' => $examId, 'teacher_id' => $id], $ids);
        (new \App\Models\ExamExaminer())->batchInsert($rows);
    }

    private function deleteExamExaminers(int $examId): void
    {
        \App\Models\ExamExaminer::findQuery()
            ->where(['exam_id' => $examId])
            ->delete();
    }

    /**
     * Get exams assigned to current supervisor
     */
    public function getMyAppointments()
    {
        $userId = $this->request->query('user_id');

        if (!$userId) throw new \Exception('User ID required', 400);

        $results = \App\Models\Exam::findQuery()
            ->select('exam.*, classroom.name as room_name, exam_supervisors.description, exam_supervisors.classroom_id')
            ->innerJoin('exam_supervisors', 'exam.id = exam_supervisors.exam_id')
            ->leftJoin('classroom', 'exam_supervisors.classroom_id = classroom.id')
            ->where(['exam_supervisors.teacher_id' => $userId])
            ->orderBy('exam.start_date DESC')
            ->all();

        return [
            'success' => true,
            'item' => $results
        ];
    }

    /**
     * Get available exams for student today
     * GET /exam/available-today
     */
    // 
    public function getAvailableToday()
    {
        $user = Auth::user();
        $studentId = null;

        if ($user) {
            $userId = $user->user_id ?? $user->id ?? null;
            $role = $user->role ?? null;

            if ($userId && ($role === 'student' || (int)$role === 4)) {
                $student = \App\Models\Student::findQuery()->where(['id' => $userId])->one();
                if ($student) {
                    $studentId = $student['id'];
                }
            }
        }

        if (!$studentId) {
            return $this->response->json(['message' => 'Student record not found'], 404);
        }

        $now = date('Y-m-d H:i:s');
        $results = Query::find()->from('exam_result')
            ->select('exam.id, exam.name, exam.start_date, exam.end_date, exam.test_duration, subject.name as subject_name, exam_result.status as result_status')
            ->leftJoin('exam', 'exam_result.exam_id = exam.id')
            ->leftJoin('subject', 'exam.subject_id = subject.id')
            ->where(['exam_result.student_id' => $studentId])
            ->andWhere(['exam.start_date', '<=', $now])
            ->andWhere(['exam.end_date', '>=', $now])
            ->andWhere(['exam.status' => 1])
            ->andWhere(['exam_result.status' => [1, 2, 3]])
            ->all();

        return $results;
    }

    /**
     * Get exam detail combined with student's exam_result
     * GET /exam/student-detail/{id}
     */
    public function getStudentExamDetail()
    {
        $id = $this->request->param('id');
        $user = Auth::user();
        if (!$user) return $this->response->json(['message' => 'Unauthorized'], 401);

        $studentId = $user->user_id ?? $user->id;

        $examResult = \App\Models\ExamResult::findQuery()
            ->select('exam_result.id as exam_result_id, exam_result.status as result_status, exam.*, subject.name as subject_name')
            ->innerJoin('exam', 'exam_result.exam_id = exam.id')
            ->leftJoin('subject', 'exam.subject_id = subject.id')
            ->where(['exam.id' => $id, 'exam_result.student_id' => $studentId])
            ->one();

        if (!$examResult) {
            // Fallback for exams that don't have exam_result pre-generated
            $exam = $this->model->findQuery()
                ->select('exam.*, subject.name as subject_name')
                ->leftJoin('subject', 'exam.subject_id = subject.id')
                ->where(['exam.id' => $id])
                ->one();

            if (!$exam) {
                return $this->response->json(['message' => 'Exam not found'], 404);
            }
            $examResult = $exam;
            $examResult['result_status'] = 1; // Default waiting
        }

        return $this->response->json([
            'success' => true,
            'item' => $examResult
        ]);
    }

    /**
     * Start exam for student
     * POST /exam/start
     */
    public function startExam()
    {
        $user = Auth::user();
        if (!$user) return $this->response->json(['message' => 'Unauthorized'], 401);

        $studentId = $user->user_id ?? $user->id;
        $payload = $this->request->all();
        $examId = $payload['exam_id'] ?? null;
        $token = $payload['token'] ?? null;

        if (!$examId) throw new \Exception('Exam ID is required', 400);

        // 1. Get Exam (using findQuery to get including hidden fields like token)
        $exam = $this->model->findQuery()->where(['id' => $examId])->one();
        if (!$exam) throw new \Exception('Exam not found', 404);

        // 2. Validate Token
        $examToken = trim($exam['token'] ?? '');
        $providedToken = trim($token ?? '');
        if ($examToken !== '' && $examToken !== $providedToken) {
            return $this->response->json(['message' => 'Invalid exam token'], 403);
        }

        // 3. Get existing ExamResult and check status
        $resultModel = new \App\Models\ExamResult();
        $result = $resultModel->findQuery()
            ->where(['exam_id' => $examId, 'student_id' => $studentId])
            ->one();

        if ($result) {
            $rStatus = (int)($result['status'] ?? 1);
            if ($rStatus === 3) {
                return $this->response->json(['message' => 'Ujian sedang dikunci. Silakan hubungi admin/pengawas.'], 403);
            }
            if ($rStatus >= 4) {
                return $this->response->json(['message' => 'Ujian telah selesai dikerjakan.'], 403);
            }
        }

        // 4. Create or update ExamResult conditionally
        if (!$result) {
            $resultId = $resultModel->create([
                'exam_id' => $examId,
                'student_id' => $studentId,
                'status' => 2, // working
                'attemp' => 1,
                'start_working' => date('Y-m-d H:i:s'),
                'duration' => $exam['test_duration'] ?? 60
            ]);

            // Initialize Answers with optional randomization
            $questionModel = new \App\Models\Question();
            $questions = $questionModel->findQuery()
                ->where(['question_bank_id' => $exam['question_bank_id']])
                ->all();

            if (($exam['is_random'] ?? 0) == 1) {
                shuffle($questions);
            }

            $answerList = [];
            foreach ($questions as $q) {
                $answerList[] = ['q_id' => $q['id'], 'answer' => null];
            }
            $resultModel->update($resultId, ['answer_list' => json_encode($answerList)]);

            $existingAnswers = [];
        } else {
            $resultId = $result['id'];

            // Update status, increment attempt, and preserve time (Paused logic)
            $updateData = [
                'attemp' => ($result['attemp'] ?? 0) + 1,
                'start_working' => date('Y-m-d H:i:s')
            ];

            // If resuming from working status (e.g. page reload), subtract time spent so far from duration
            if ($result['status'] == 2 && !empty($result['start_working'])) {
                $spent = (time() - strtotime($result['start_working'])) / 60;
                $updateData['duration'] = max(0, ($result['duration'] ?? 60) - $spent);
            }

            // Move to Working status if waiting (1) or locked (3)
            if ($result['status'] == 1 || $result['status'] == 3) {
                $updateData['status'] = 2;
            }

            $resultModel->update($resultId, $updateData);

            // Populate existingAnswers from answer_list if available
            $existingAnswers = [];
            $answerList = json_decode($result['answer_list'] ?? '[]', true);
            $questionIds = [];
            foreach ($answerList as $ans) {
                $questionIds[] = $ans['q_id'];
                $existingAnswers[] = [
                    'question_id' => $ans['q_id'],
                    'answer' => $ans['answer']
                ];
            }

            // Fetch questions in the SPECIFIC order saved in answer_list
            $questions = [];
            if (!empty($questionIds)) {
                $questionModel = new \App\Models\Question();
                $allQuestions = $questionModel->findQuery()
                    ->where(['id' => $questionIds])
                    ->all();

                // Sort allQuestions based on the order of $questionIds
                $qMap = [];
                foreach ($allQuestions as $q) {
                    $qMap[$q['id']] = $q;
                }
                foreach ($questionIds as $qid) {
                    if (isset($qMap[$qid])) {
                        $questions[] = $qMap[$qid];
                    }
                }
            } else {
                // Fallback to default fetch if answer_list order is somehow missing
                $questionModel = new \App\Models\Question();
                $questions = $questionModel->findQuery()
                    ->where(['question_bank_id' => $exam['question_bank_id']])
                    ->all();
            }

            // Fallback to ExamResultAnswer table if answer_list was empty
            if (empty($existingAnswers)) {
                $answerModel = new \App\Models\ExamResultAnswer();
                $existingAnswers = $answerModel->findQuery()
                    ->where(['exam_result_id' => $resultId])
                    ->all();
            }
        }

        // 3. Re-fetch result to get the latest data (including duration and start_working)
        $result = $resultModel->findQuery()
            ->where(['id' => $resultId])
            ->one();

        // Calculate remaining seconds
        $now = time();
        $startTime = strtotime($result['start_working']);
        $durationMinutes = $result['duration'] ?? 60;
        $totalSecondsAllowed = $durationMinutes * 60;
        $spentSeconds = $now - $startTime;
        $remainingSeconds = max(0, $totalSecondsAllowed - $spentSeconds);

        // If time is up, auto finish it
        if ($remainingSeconds <= 0 && $result['status'] == 2) {
            $this->performFinishCalculation($resultId);
            return $this->response->json([
                'success' => false,
                'message' => 'Waktu pengerjaan telah habis. Ujian Anda telah diselesaikan secara otomatis.'
            ], 403);
        }

        return $this->response->json([
            'success' => true,
            'item' => [
                'exam_result_id' => $resultId,
                'questions' => $questions,
                'existing_answers' => $existingAnswers,
                'remaining_seconds' => $remainingSeconds,
                'test_duration' => $durationMinutes
            ]
        ]);
    }

    public function saveAnswer()
    {
        $payload = $this->request->all();
        $examResultId = $payload['exam_result_id'] ?? null;
        $questionId = $payload['question_id'] ?? null;
        $answer = $payload['answer'] ?? null;

        if (!$examResultId || !$questionId) throw new \Exception('Invalid payload', 400);

        $resultModel = new \App\Models\ExamResult();
        $result = $resultModel->find($examResultId);

        if (!$result) throw new \Exception('Result not found', 404);
        if ($result['status'] >= 3) {
            return $this->response->json(['success' => false, 'message' => 'Ujian telah dikunci atau selesai.'], 200);
        }

        // Check if time is up
        $now = time();
        $startTime = strtotime($result['start_working']);
        $durationMinutes = $result['duration'] ?? 60;
        $totalSecondsAllowed = $durationMinutes * 60;
        $spentSeconds = $now - $startTime;
        if ($spentSeconds >= $totalSecondsAllowed) {
            $this->performFinishCalculation($examResultId);
            return $this->response->json(['success' => false, 'message' => 'Waktu habis. Ujian diselesaikan otomatis.'], 200);
        }

        // 1. Update answer_list in exam_result
        $answerList = json_decode($result['answer_list'] ?? '[]', true);
        $found = false;
        foreach ($answerList as &$item) {
            if ($item['q_id'] == $questionId) {
                $item['answer'] = $answer;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $answerList[] = ['q_id' => $questionId, 'answer' => $answer];
        }
        $resultModel->update($examResultId, ['answer_list' => json_encode($answerList)]);

        // 2. Keep ExamResultAnswer sync for backward compatibility
        $answerModel = new \App\Models\ExamResultAnswer();
        $existing = $answerModel->findQuery()
            ->where(['exam_result_id' => $examResultId, 'question_id' => $questionId])
            ->one();

        if ($existing) {
            $answerModel->update($existing['id'], ['answer' => $answer]);
        } else {
            $answerModel->create([
                'exam_result_id' => $examResultId,
                'question_id' => $questionId,
                'answer' => $answer
            ]);
        }

        return $this->response->json(['success' => true]);
    }

    /**
     * Finish exam
     * POST /exam/finish
     */
    public function finishExam()
    {
        $payload = $this->request->all();
        $examResultId = $payload['exam_result_id'] ?? null;

        if (!$examResultId) throw new \Exception('Exam Result ID is required', 400);

        $this->performFinishCalculation($examResultId);

        $resultModel = new \App\Models\ExamResult();
        $result = $resultModel->find($examResultId);
        $examModel = new \App\Models\Exam();
        $exam = $examModel->find($result['exam_id']);

        $resData = ['success' => true];
        if (($exam['show_result'] ?? 0) == 1) {
            $resData['result'] = [
                'total_score' => $result['total_result'],
                'mc_score' => $result['mc_result'],
                'has_essay' => $result['contain_essay']
            ];
        }

        return $this->response->json($resData);
    }

    private function performFinishCalculation($examResultId)
    {
        $resultModel = new \App\Models\ExamResult();
        $result = $resultModel->find($examResultId);
        if (!$result) return;

        $examModel = new \App\Models\Exam();
        $exam = $examModel->find($result['exam_id']);
        if (!$exam) return;

        $questionModel = new \App\Models\Question();
        $questions = $questionModel->findQuery()
            ->where(['question_bank_id' => $exam['question_bank_id']])
            ->all();

        $answerList = json_decode($result['answer_list'] ?? '[]', true);
        $answerMap = [];
        foreach ($answerList as $ans) {
            $answerMap[$ans['q_id']] = $ans['answer'];
        }

        $mcTotal = 0;
        $mcCorrect = 0;
        $hasEssay = false;
        $mcScoreList = [];

        foreach ($questions as $q) {
            if ($q['type'] == 1) { // Multiple Choice
                $mcTotal++;
                $studentAnswer = $answerMap[$q['id']] ?? null;
                if ($studentAnswer !== null && trim($studentAnswer) === trim($q['answer'])) {
                    $mcCorrect++;
                    $mcScoreList[] = 1;
                } else {
                    $mcScoreList[] = 0;
                }
            } elseif ($q['type'] == 2) { // Essay
                $hasEssay = true;
            }
        }

        $status = $hasEssay ? 4 : 5;
        $mcScore = 0;
        $totalScore = 0;

        if ($mcTotal > 0) {
            if (!$hasEssay) {
                $mcScore = ($mcCorrect / $mcTotal) * 100;
                $totalScore = $mcScore;
            } else {
                $percentageMc = $exam['percentage_mc_value'] ?? 70;
                $mcScore = ($mcCorrect / $mcTotal) * $percentageMc;
                $totalScore = $mcScore; // Essay score starts at 0
            }
        }

        $resultModel->update($examResultId, [
            'status' => $status,
            'mc_result' => $mcScore,
            'total_result' => $totalScore,
            'contain_essay' => $hasEssay ? 1 : 0,
            'answer_score_list' => implode(',', $mcScoreList),
            'duration' => 0, // Time is spent
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Lock exam (e.g. on window/tab switch)
     * POST /exam/lock
     */
    public function lockExam()
    {
        $payload = $this->request->all();
        $examResultId = $payload['exam_result_id'] ?? null;

        if (!$examResultId) throw new \Exception('Exam Result ID is required', 400);

        $resultModel = new \App\Models\ExamResult();
        $result = $resultModel->find($examResultId);

        $updateData = ['status' => 3];

        // PAUSE LOGIC: If locking while working, save remaining time
        if ($result && $result['status'] == 2 && !empty($result['start_working'])) {
            $spent = (time() - strtotime($result['start_working'])) / 60;
            $updateData['duration'] = max(0, ($result['duration'] ?? 60) - $spent);
            $updateData['start_working'] = null; // Mark as paused
        }

        $resultModel->update($examResultId, $updateData);

        return $this->response->json(['success' => true, 'message' => 'Exam locked']);
    }
    private function createInitialResults(int $examId, array $classIds, int $qbId, int $duration, array $participantIds = []): void
    {
        // 1. Get Questions to detect essay and prepare initial answer list
        $questions = (new \App\Models\Question())->findQuery()
            ->where(['question_bank_id' => $qbId])
            ->all();

        $hasEssay = 0;
        $answerList = [];
        foreach ($questions as $q) {
            $answerList[] = [
                'q_id' => $q['id'],
                'answer' => null
            ];
            // Normalize type check: 2 or 'essay'
            if (isset($q['type']) && ($q['type'] == 2 || strtolower((string)$q['type']) === 'essay')) {
                $hasEssay = 1;
            }
        }
        $answerListJson = json_encode($answerList);

        // 2. Determine definitive student list
        // If participantIds is explicitly provided (user manual selection), use it.
        // Otherwise, fetch all students from the provided classes.
        $allStudentIds = $participantIds;
        if (empty($allStudentIds) && !empty($classIds)) {
            $allStudentIds = \App\Models\ClassroomMember::findQuery()
                ->select('DISTINCT student_id')
                ->where(['class_id' => $classIds])
                ->column();
        }

        $allStudentIds = array_unique(array_filter($allStudentIds));

        // 3. Sync Participant Results
        $resultModel = new \App\Models\ExamResult();

        // 3a. Remove only "Waiting" results (status 1) to allow resync/reset
        // This handles removing students who are no longer participants 
        // AND allows applying new settings (QB/Duration) to those who haven't started.
        $resultModel::findQuery()
            ->where(['exam_id' => $examId])
            ->andWhere(['status' => 1])
            ->delete();

        // 3b. Determine which participants to add
        // We MUST NOT overwrite students who have already started (status 2-5)
        $startedStudentIds = $resultModel::findQuery()
            ->select('student_id')
            ->where(['exam_id' => $examId])
            ->column();

        $newStudentIds = array_diff($allStudentIds, $startedStudentIds);
        if (empty($newStudentIds)) return;

        // 4. Batch Insert New Participants
        $rows = [];
        $now = date('Y-m-d H:i:s');
        foreach ($newStudentIds as $studentId) {
            $rows[] = [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'duration' => $duration,
                'status' => 1, // waiting
                'contain_essay' => $hasEssay,
                'answer_list' => $answerListJson,
                'attemp' => 0,
                'mc_result' => 0,
                'essay_result' => 0,
                'total_result' => 0,
                'answer_score_list' => '',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        if (!empty($rows)) {
            $resultModel->batchInsert($rows);
        }
    }
}
