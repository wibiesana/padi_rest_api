<?php

namespace App\Controllers;

use App\Controllers\Base\AssignmentResultController as BaseController;
use Wibiesana\Padi\Core\Auth;

class AssignmentResultController extends BaseController
{
    /**
     * Get assignment results for the current logged-in student
     */
    public function myUpload()
    {
        $user = $this->request->user;
        if (!$user) {
            throw new \Exception('Not authenticated', 401);
        }

        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

        // Get active semester from query
        $semesterId = $this->request->query('semester_id');

        // Get results submitted by this user
        $resultsQuery = \App\Models\AssignmentResult::findQuery()
            ->where(['created_by' => $userId]);

        $results = $resultsQuery->all();

        // Eager load relations for the results
        if (!empty($results)) {
            $this->model->with(['assignment.subject', 'assignment.createdBy.teacher', 'createdBy.student', 'updatedBy']);
            $this->model->loadRelations($results);
        }

        // Data status for available assignments
        $dataStatus = [];

        // Find class for this student
        $cm = \App\Models\ClassroomMember::findQuery()->where(['student_id' => $userId])->one();

        if ($cm && !empty($cm['class_id'])) {
            $classId = $cm['class_id'];

            // Get all assignments associated with this class
            $assignmentsQuery = \App\Models\Assignment::findQuery()
                ->select('assignment.*')
                ->innerJoin('assignment_class', 'assignment.id = assignment_class.assignment_id')
                ->where(['assignment_class.classroom_id' => $classId]);

            // Filter by semester if provided
            if ($semesterId) {
                $assignmentsQuery->where(['assignment.semester_id' => $semesterId]);
            }

            $assignments = $assignmentsQuery->all();

            // Filter out assignments that are already submitted
            $submittedAssignmentIds = array_column($results, 'assignment_id');
            foreach ($assignments as $assignment) {
                if (!in_array($assignment['id'], $submittedAssignmentIds)) {
                    $assignmentModel = new \App\Models\Assignment();
                    $assignmentModel->with(['createdBy.student', 'subject', 'semester']);
                    $assignmentArray = [$assignment];
                    $assignmentModel->loadRelations($assignmentArray);
                    $dataStatus[] = \App\Resources\AssignmentResource::make($assignmentArray[0]);
                }
            }
        }

        return [
            'success' => true,
            'data' => \App\Resources\AssignmentResultResource::collection($results),
            'dataStatus' => $dataStatus
        ];
    }

    /**
     * Get single assignment result with deep eager loading
     */
    public function show()
    {
        $id = $this->request->param('id');
        $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.student', 'updatedBy:id,username']);
        $result = $this->model->find($id);
        
        if (!$result) {
            throw new \Exception('Assignment result not found', 404);
        }

        return \App\Resources\AssignmentResultResource::make($result);
    }

    /**
     * Override store to handle physical file upload
     */
    public function store()
    {
        $user = $this->request->user;
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

        $validated = $this->validate([
            'assignment_id' => 'required|integer',
            'description' => 'string'
        ]);

        // Set user
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;
        $validated['status'] = 1; // Default status Submitted

        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $uploadDir = 'uploads/assignments/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                $validated['upload_file'] = $uploadDir . $fileName;
            }
        }

        try {
            $id = $this->model->create($validated);
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.student', 'updatedBy:id,username']);
            $assignmentResult = $this->model->find($id);
            return $this->created(\App\Resources\AssignmentResultResource::make($assignmentResult));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to submit assignment', $e);
        }
    }

    /**
     * Update assignment result
     */
    public function update()
    {
        $id = $this->request->param('id');

        $assignmentResult = $this->model->find($id);
        if (!$assignmentResult) {
            throw new \Exception('Assignment result not found', 404);
        }

        // Prevent update if already graded (status 2)
        if (($assignmentResult['status'] ?? 0) == 2) {
            throw new \Exception('Cannot edit an assignment that has already been graded', 403);
        }

        $user = $this->request->user ?? Auth::user();
        $userId = $user ? (is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null)) : null;

        $validated = $this->validate([
            'description' => 'string'
        ]);

        $validated['updated_by'] = $userId;

        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $uploadDir = 'uploads/assignments/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                // Delete old file if exists
                if (!empty($assignmentResult['upload_file']) && file_exists($assignmentResult['upload_file'])) {
                    @unlink($assignmentResult['upload_file']);
                }
                $validated['upload_file'] = $uploadDir . $fileName;
            }
        }

        try {
            $this->model->update($id, $validated);
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.student', 'updatedBy:id,username']);
            return \App\Resources\AssignmentResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assignment submission', $e);
        }
    }

    /**
     * Get all submissions for a specific assignment (For Teachers)
     * Shows all students from taught classes + assigned classes, merged with submission data.
     */
    public function submissionsByAssignment()
    {
        $id = $this->request->param('id');
        $classroomFilterId = $this->request->query('classroom_id');

        // 1. Get the assignment to verify it exists and get the creator/teacher
        $assignmentModel = new \App\Models\Assignment();
        $assignment = $assignmentModel->find($id);
        if (!$assignment) {
            throw new \Exception('Assignment not found', 404);
        }

        // 2. Get classes: from assignment_class OR classes that have active student results
        $safeId = (int)$id;
        
        // Query assigned classes
        $assignedClasses = \Wibiesana\Padi\Core\Query::find()
            ->select('classroom.*')
            ->from('classroom')
            ->innerJoin('assignment_class', 'classroom.id = assignment_class.classroom_id')
            ->where(['assignment_class.assignment_id' => $safeId])
            ->all();

        // Query classes that have students who already submitted (fallback/comprehensive)
        $submissionClasses = \Wibiesana\Padi\Core\Query::find()
            ->select('DISTINCT classroom.*')
            ->from('classroom')
            ->innerJoin('classroom_member', 'classroom.id = classroom_member.class_id')
            ->innerJoin('assignment_result', 'classroom_member.student_id = assignment_result.created_by')
            ->where(['assignment_result.assignment_id' => $safeId])
            ->all();

        // Combine and unique by ID
        $combined = array_merge($assignedClasses, $submissionClasses);
        $uniqueClasses = [];
        $seenIds = [];
        foreach ($combined as $c) {
            if (!in_array($c['id'], $seenIds)) {
                $uniqueClasses[] = $c;
                $seenIds[] = $c['id'];
            }
        }
        $allClasses = array_values($uniqueClasses); // Reset keys for JSON array consistency

        // 3. Determine which classes to query students from
        $targetClassIds = $classroomFilterId
            ? [(int)$classroomFilterId]
            : array_column($allClasses, 'id');

        if (empty($targetClassIds)) {
            return [
                'success' => true,
                'data' => [],
                'classes' => $allClasses,
            ];
        }

        // 4. Get all students in target classes
        $students = \Wibiesana\Padi\Core\Query::find()
            ->select('student.*, classroom.name as class_name, classroom_member.class_id')
            ->from('student')
            ->innerJoin('classroom_member', 'student.id = classroom_member.student_id')
            ->innerJoin('classroom', 'classroom_member.class_id = classroom.id')
            ->where(['IN', 'classroom_member.class_id', $targetClassIds])
            ->orderBy('student.name ASC')
            ->all();

        // 5. Get existing submission results for this assignment
        $results = \Wibiesana\Padi\Core\Query::find()
            ->select('assignment_result.*')
            ->from('assignment_result')
            ->where(['assignment_result.assignment_id' => $safeId])
            ->all();


        if (!empty($results)) {
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.student', 'updatedBy:id,username']);
            $this->model->loadRelations($results);
        }

        // 6. Index results by created_by (student user id)
        $resultsByStudent = [];
        foreach ($results as $res) {
            $resultsByStudent[$res['created_by']] = $res;
        }

        // 7. Merge students with results
        $finalData = [];
        foreach ($students as $student) {
            $studentId = $student['id'];
            if (isset($resultsByStudent[$studentId])) {
                $item = \App\Resources\AssignmentResultResource::make($resultsByStudent[$studentId])->resolve();
                $item['class_name'] = $student['class_name'] ?? null;
                $finalData[] = $item;
            } else {
                $finalData[] = [
                    'id' => null,
                    'assignment_id' => $id,
                    'class_id' => $student['class_id'] ?? null,
                    'description' => null,
                    'upload_file' => null,
                    'score' => null,
                    'status' => 0,
                    'created_at' => '-',
                    'createdBy_name' => $student['name'],
                    'class_name' => $student['class_name'] ?? null,
                ];
            }
        }

        return [
            'success' => true,
            'data' => $finalData,
            'classes' => $allClasses,
        ];
    }

    /**
     * Process grading for a submission (For Teachers)
     */
    public function giveScore()
    {
        $id = $this->request->param('id');
        $assignmentResult = $this->model->find($id);
        
        if (!$assignmentResult) {
            throw new \Exception('Submission not found', 404);
        }

        $validated = $this->validate([
            'score' => 'required|numeric|min:0|max:100'
        ]);

        $user = $this->request->user;
        $userId = $user ? (is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null)) : null;

        $updateData = [
            'score' => $validated['score'],
            'status' => 2, // 2 = Graded
            'updated_by' => $userId
        ];

        try {
            $this->model->update($id, $updateData);
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.student', 'updatedBy:id,username']);
            return \App\Resources\AssignmentResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to save score', $e);
        }
    }
}
