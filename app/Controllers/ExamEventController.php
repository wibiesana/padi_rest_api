<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use App\Models\ExamEvent;
use App\Resources\ExamEventResource;
use Wibiesana\Padi\Core\Request;

class ExamEventController extends Controller
{
    protected ExamEvent $model;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamEvent();
    }

    public function index()
    {
        $keyword = $this->request->query('search', '');
        $page = (int)$this->request->query('page', 1);
        $perPage = (int)$this->request->query('per-page', 10);

        $this->model->with(['schoolYear', 'semester']);

        if ($keyword) {
            $results = $this->model->searchPaginate($keyword, $page, $perPage);
        } else {
            $results = $this->model->paginate($page, $perPage);
        }

        // Fetch student counts and working counts efficiently
        $eventIds = array_column($results['data'], 'id');
        if (!empty($eventIds)) {
            // Total students (ever joined)
            $totalCounts = \App\Models\ExamResult::findQuery()
                ->select('exam.exam_event_id, COUNT(DISTINCT student_id) as total')
                ->innerJoin('exam', 'exam_result.exam_id = exam.id')
                ->where(['exam.exam_event_id' => $eventIds])
                ->groupBy('exam.exam_event_id')
                ->all();

            // Working students (currently active status >= 2)
            $workingCounts = \App\Models\ExamResult::findQuery()
                ->select('exam.exam_event_id, COUNT(DISTINCT student_id) as total')
                ->innerJoin('exam', 'exam_result.exam_id = exam.id')
                ->where(['exam.exam_event_id' => $eventIds])
                ->andWhere(['exam_result.status', '>=', 2])
                ->groupBy('exam.exam_event_id')
                ->all();

            $totalMap = [];
            foreach ($totalCounts as $c) $totalMap[$c['exam_event_id']] = $c['total'];

            $workingMap = [];
            foreach ($workingCounts as $c) $workingMap[$c['exam_event_id']] = $c['total'];

            foreach ($results['data'] as &$row) {
                if (is_object($row)) {
                    $row->setAttribute('total_students', $totalMap[$row->id] ?? 0);
                    $row->setAttribute('working_student_count', $workingMap[$row->id] ?? 0);
                } else {
                    $row['total_students'] = $totalMap[$row['id']] ?? 0;
                    $row['working_student_count'] = $workingMap[$row['id']] ?? 0;
                }
            }
        }

        return ExamEventResource::collection($results);
    }

    public function all()
    {
        $items = $this->model->where(['status' => 1]);

        $eventIds = array_column($items, 'id');
        if (!empty($eventIds)) {
            $counts = \App\Models\ExamResult::findQuery()
                ->select('exam.exam_event_id, COUNT(DISTINCT student_id) as total')
                ->innerJoin('exam', 'exam_result.exam_id = exam.id')
                ->where(['exam.exam_event_id' => $eventIds])
                ->groupBy('exam.exam_event_id')
                ->all();

            $countMap = [];
            foreach ($counts as $c) {
                $countMap[$c['exam_event_id']] = $c['total'];
            }

            foreach ($items as &$row) {
                if (is_object($row)) {
                    $row->setAttribute('total_students', $countMap[$row->id] ?? 0);
                } else {
                    $row['total_students'] = $countMap[$row['id']] ?? 0;
                }
            }
        }

        return ExamEventResource::collection($items);
    }

    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'school_year_id' => 'integer',
            'semester_id' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => 'string',
            'description' => 'string'
        ]);

        $id = $this->model->create($validated);
        $item = $this->model->find($id);
        return $this->created(ExamEventResource::make($item));
    }

    public function show()
    {
        $id = $this->request->param('id');
        $item = $this->model->with([
            'schoolYear',
            'semester',
            'exams',
            'exams.examresults',
            'exams.subject',
            'exams.classes',
            'exams.examiners',
            'exams.supervisorAssignments',
            'exams.supervisorAssignments.teacher',
            'exams.supervisorAssignments.classroom',
            'questionBanks',
            'questionBanks.teacher',
            'questionBanks.createdBy',
            'questionBanks.updatedBy'
        ])->find($id);

        if (!$item) {
            throw new \Exception('Exam Event not found', 404);
        }

        // Calculate total students across all exams in this event
        $totalStudents = \App\Models\ExamResult::findQuery()
            ->innerJoin('exam', 'exam_result.exam_id = exam.id')
            ->where(['exam.exam_event_id' => $id])
            ->count('DISTINCT student_id');

        // Calculate working students across all exams in this event
        $workingCountTotal = \App\Models\ExamResult::findQuery()
            ->innerJoin('exam', 'exam_result.exam_id = exam.id')
            ->where(['exam.exam_event_id' => $id])
            ->andWhere(['exam_result.status', '>=', 2])
            ->count('DISTINCT student_id');

        // Batch fetch working counts for EACH exam within this event to avoid N+1 in Resource
        $examCounts = \App\Models\ExamResult::findQuery()
            ->select('exam_id, COUNT(*) as total')
            ->where(['exam_result.status', '>=', 2])
            ->andWhere(['exam.exam_event_id' => $id])
            ->innerJoin('exam', 'exam_result.exam_id = exam.id')
            ->groupBy('exam_id')
            ->all();

        $examCountMap = [];
        foreach ($examCounts as $ec) $examCountMap[$ec['exam_id']] = $ec['total'];

        if (is_object($item)) {
            $item->setAttribute('total_students', $totalStudents);
            $item->setAttribute('working_student_count', $workingCountTotal);

            // Attach counts to child exam models if loaded
            if (isset($item->exams) && is_iterable($item->exams)) {
                foreach ($item->exams as $exam) {
                    $exam->setAttribute('working_student_count', $examCountMap[$exam->id] ?? 0);
                }
            }
        } else {
            $item['total_students'] = $totalStudents;
            $item['working_student_count'] = $workingCountTotal;

            if (isset($item['exams']) && is_iterable($item['exams'])) {
                foreach ($item['exams'] as &$exam) {
                    $exam['working_student_count'] = $examCountMap[$exam['id']] ?? 0;
                }
            }
        }

        return ExamEventResource::make($item);
    }

    public function update()
    {
        $id = $this->request->param('id');
        $item = $this->model->find($id);

        if (!$item) {
            throw new \Exception('Exam Event not found', 404);
        }

        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'school_year_id' => 'integer',
            'semester_id' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'status' => 'string',
            'description' => 'string'
        ]);

        $this->model->update($id, $validated);
        return ExamEventResource::make($this->model->find($id));
    }

    public function destroy()
    {
        $id = $this->request->param('id');
        $item = $this->model->find($id);

        if (!$item) {
            throw new \Exception('Exam Event not found', 404);
        }

        $this->model->delete($id);
        return $this->noContent();
    }

    /**
     * Get students and their exams for card printing
     * GET /exam-event/{id}/student-cards
     */
    public function getStudentCards()
    {
        $id = $this->request->param('id');
        $classId = $this->request->query('class_id');
        $event = $this->model->find($id);

        if (!$event) {
            throw new \Exception('Exam Event not found', 404);
        }

        // Get classrooms associated with exams in this event
        $classroomsQuery = \App\Models\Classroom::findQuery()
            ->select('DISTINCT classroom.*')
            ->innerJoin('exam_class', 'classroom.id = exam_class.class_id')
            ->innerJoin('exam', 'exam_class.exam_id = exam.id')
            ->where(['exam.exam_event_id' => $id]);

        if ($classId) {
            $classroomsQuery->andWhere(['classroom.id' => $classId]);
        }

        $classrooms = $classroomsQuery->all();

        $results = [];

        foreach ($classrooms as $room) {
            // Get students in this classroom
            $students = \App\Models\Student::findQuery()
                ->select('student.*')
                ->innerJoin('classroom_member', 'student.id = classroom_member.student_id')
                ->where(['classroom_member.class_id' => $room['id']])
                ->all();

            // Add photo_url and other helpers
            $studentModel = new \App\Models\Student();
            $studentModel->afterLoad($students);

            foreach ($students as $student) {
                // Get exams for this student in this event
                $examList = \App\Models\Exam::findQuery()
                    ->select('exam.*, subject.name as subject_name, classroom.name as room_name')
                    ->innerJoin('exam_class', 'exam.id = exam_class.exam_id')
                    ->innerJoin('classroom_member', 'exam_class.class_id = classroom_member.class_id')
                    ->leftJoin('subject', 'exam.subject_id = subject.id')
                    ->leftJoin('classroom', 'exam_class.class_id = classroom.id')
                    ->where(['classroom_member.student_id' => $student['id']])
                    ->andWhere(['exam.exam_event_id' => $id])
                    ->andWhere(['exam.status' => 1])
                    ->all();

                if (!empty($examList)) {
                    $results[] = [
                        'student' => $student,
                        'classroom' => $room,
                        'exams' => $examList
                    ];
                }
            }
        }

        return [
            'success' => true,
            'items' => $results
        ];
    }
    public function getMonitoring()
    {
        $id = $this->request->param('id');
        $classId = $this->request->query('class_id');
        $examId = $this->request->query('exam_id');
        $statusId = $this->request->query('status_id');

        $query = \App\Models\Exam::findQuery()
            ->select([
                'student.id as student_id',
                'student.name as student_name',
                'classroom.id as classroom_id',
                'classroom.name as classroom_name',
                'exam.id as exam_id',
                'exam.name as exam_name',
                'exam_result.id as result_id',
                'IFNULL(exam_result.status, 0) as exam_status_id',
                'IF(exam_result.status = 3, 1, 0) as is_locked',
                'exam_result.created_at as start_time',
                'exam_result.updated_at as last_activity',
                'exam_result.total_result as score',
                'exam_result.attemp as attempt',
            ])
            ->innerJoin('exam_class', 'exam.id = exam_class.exam_id')
            ->innerJoin('classroom_member', 'exam_class.class_id = classroom_member.class_id')
            ->innerJoin('student', 'classroom_member.student_id = student.id')
            ->innerJoin('classroom', 'classroom_member.class_id = classroom.id')
            ->leftJoin('exam_result', '(exam.id = exam_result.exam_id AND student.id = exam_result.student_id)')
            ->where(['exam.exam_event_id' => $id]);

        if ($classId) {
            $query->andWhere(['classroom.id' => $classId]);
        }

        if ($examId) {
            $query->andWhere(['exam.id' => $examId]);
        }

        if ($statusId !== null && $statusId !== '' && $statusId !== 'all') {
            if ($statusId == 0) {
                $query->andWhere('exam_result.id IS NULL');
            } else {
                $query->andWhere(['exam_result.status' => $statusId]);
            }
        } elseif ($statusId === 'all') {
            // Show everything, do nothing
        } else {
            $isLocked = $this->request->query('is_locked');
            if ($isLocked !== null && $isLocked !== '') {
                if ($isLocked == 1) {
                    $query->andWhere(['exam_result.status' => 3]);
                } else {
                    $query->andWhere('exam_result.status != 3');
                }
            } else {
                // Default: Working and Locked only
                $query->andWhere('exam_result.status IN (2, 3)');
            }
        }

        $items = $query->orderBy('classroom.name ASC, student.name ASC')->all();

        return [
            'success' => true,
            'items' => $items
        ];
    }

    public function unlockStudent()
    {
        $resultId = $this->request->param('resultId');
        return $this->updateResultStatus($resultId, 2, 'Student unlocked successfully');
    }

    public function lockStudent()
    {
        $resultId = $this->request->param('resultId');
        return $this->updateResultStatus($resultId, 3, 'Student locked successfully');
    }

    public function finishStudent()
    {
        $resultId = (int)$this->request->param('resultId');
        $examCtrl = new \App\Controllers\ExamController($this->request);
        $reflection = new \ReflectionClass($examCtrl);
        $method = $reflection->getMethod('performFinishCalculation');
        $method->invoke($examCtrl, $resultId);

        return [
            'success' => true,
            'message' => 'Student exam finished successfully'
        ];
    }

    public function addTime()
    {
        $resultId = (int)$this->request->param('resultId');
        $minutes = (int)$this->request->input('minutes', 0);

        if ($minutes <= 0) throw new \Exception('Invalid minutes', 400);

        $examResultModel = new \App\Models\ExamResult();
        $db = $examResultModel->getDb();
        $now = date('Y-m-d H:i:s');

        $sql = "UPDATE exam_result SET duration = duration + :minutes, updated_at = :now WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['minutes' => $minutes, 'now' => $now, 'id' => $resultId]);

        if ($stmt->rowCount() === 0) throw new \Exception('Exam result not found', 404);

        return [
            'success' => true,
            'message' => "Added $minutes minutes to student's exam"
        ];
    }

    public function batchAction()
    {
        $resultIds = $this->request->input('result_ids', []);
        $action = $this->request->input('action');
        $minutes = (int)$this->request->input('minutes', 0);

        if (empty($resultIds)) throw new \Exception('No students selected', 400);

        $examResultModel = new \App\Models\ExamResult();
        $db = $examResultModel->getDb();
        $now = date('Y-m-d H:i:s');
        $count = 0;

        switch ($action) {
            case 'lock':
                $ids = implode(',', array_map('intval', $resultIds));
                // Update Working students (2) with duration subtraction
                $sql2 = "UPDATE exam_result 
                         SET duration = GREATEST(0, duration - (TIMESTAMPDIFF(SECOND, start_working, NOW()) / 60)), 
                             status = 3, 
                             updated_at = :now 
                         WHERE id IN ($ids) AND status = 2 AND start_working IS NOT NULL";
                $stmt2 = $db->prepare($sql2);
                $stmt2->execute(['now' => $now]);
                $count = $stmt2->rowCount();

                // Update Waiting students (1) simply to Locked (3)
                $sql1 = "UPDATE exam_result 
                         SET status = 3, updated_at = :now 
                         WHERE id IN ($ids) AND status = 1";
                $stmt1 = $db->prepare($sql1);
                $stmt1->execute(['now' => $now]);
                $count += $stmt1->rowCount();
                break;

            case 'unlock':
                $ids = implode(',', array_map('intval', $resultIds));
                // Unlocking always resumes to Working (2) and resets start_working to NOW
                $sql = "UPDATE exam_result 
                        SET start_working = :now, 
                            status = 2, 
                            updated_at = :now2 
                        WHERE id IN ($ids) AND status = 3";
                $stmt = $db->prepare($sql);
                $stmt->execute(['now' => $now, 'now2' => $now]);
                $count = $stmt->rowCount();
                break;

            case 'add_time':
                if ($minutes <= 0) throw new \Exception('Invalid minutes', 400);
                $ids = implode(',', array_map('intval', $resultIds));
                $sql = "UPDATE exam_result SET duration = duration + :minutes, updated_at = :now WHERE id IN ($ids)";
                $stmt = $db->prepare($sql);
                $stmt->execute(['minutes' => $minutes, 'now' => $now]);
                $count = $stmt->rowCount();
                break;

            case 'finish':
                $examCtrl = new \App\Controllers\ExamController($this->request);
                foreach ($resultIds as $id) {
                    try {
                        $reflection = new \ReflectionClass($examCtrl);
                        $method = $reflection->getMethod('performFinishCalculation');
                        $method->invoke($examCtrl, (int)$id);
                        $count++;
                    } catch (\Exception $e) {
                    }
                }
                break;

            case 'resume':
                if ($minutes <= 0) throw new \Exception('Invalid minutes', 400);
                $curTime = time();
                foreach ($resultIds as $id) {
                    try {
                        $res = $examResultModel->find($id);
                        if ($res) {
                            $startTime = strtotime($res['start_working']);
                            $spentSeconds = $curTime - $startTime;
                            $newDuration = ceil($spentSeconds / 60) + $minutes;
                            $examResultModel->update($id, [
                                'status' => 2,
                                'duration' => $newDuration,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                            $count++;
                        }
                    } catch (\Exception $e) {
                    }
                }
                break;
        }

        return [
            'success' => true,
            'message' => "Action $action processed for $count students"
        ];
    }

    public function resumeStudent()
    {
        $resultId = (int)$this->request->param('resultId');
        $minutes = (int)$this->request->input('minutes', 0);

        if ($minutes <= 0) throw new \Exception('Invalid minutes', 400);

        $examResultModel = new \App\Models\ExamResult();
        $res = $examResultModel->find($resultId);

        if (!$res) throw new \Exception('Exam result not found', 404);

        $now = time();
        $startTime = strtotime($res['start_working']);
        $spentSeconds = $now - $startTime;

        // New duration = spent + requested extra
        $newDuration = ceil($spentSeconds / 60) + $minutes;

        $examResultModel->update($resultId, [
            'status' => 2,
            'duration' => $newDuration,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => "Student exam resumed with $minutes extra minutes"
        ];
    }

    private function updateResultStatus($resultId, $status, $message)
    {
        $examResultModel = new \App\Models\ExamResult();
        $db = $examResultModel->getDb();
        $now = date('Y-m-d H:i:s');

        if ($status == 3) { // Locking
            // Try to update as working student first
            $sql = "UPDATE exam_result 
                    SET duration = GREATEST(0, duration - (TIMESTAMPDIFF(SECOND, start_working, NOW()) / 60)), 
                        status = 3, 
                        updated_at = :now 
                    WHERE id = :id AND status = 2 AND start_working IS NOT NULL";
            $stmt = $db->prepare($sql);
            $stmt->execute(['now' => $now, 'id' => $resultId]);

            if ($stmt->rowCount() === 0) {
                // If not status 2, maybe it's status 1 (Waiting)
                $examResultModel->findQuery()
                    ->where(['id' => $resultId, 'status' => 1])
                    ->update(['status' => 3, 'updated_at' => $now]);
            }
        } elseif ($status == 2) { // Unlocking
            $sql = "UPDATE exam_result 
                    SET start_working = :now, 
                        status = 2, 
                        updated_at = :now2 
                    WHERE id = :id AND status = 3";
            $stmt = $db->prepare($sql);
            $stmt->execute(['now' => $now, 'now2' => $now, 'id' => $resultId]);
        } else {
            $examResultModel->findQuery()
                ->where(['id' => $resultId])
                ->update(['status' => $status, 'updated_at' => $now]);
        }

        return [
            'success' => true,
            'message' => $message
        ];
    }
}
