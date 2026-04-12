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

        // Validate Deadline
        $this->checkDeadline($validated['assignment_id']);

        // Set user
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;
        $validated['status'] = 1; // Default status Submitted

        // Handle multiple file uploads
        $uploadedFiles = [];

        // Check for 'files' (array) or 'file' (singular)
        $files = $_FILES['files'] ?? $_FILES['file'] ?? null;

        if ($files) {
            if (isset($files['name']) && is_array($files['name'])) {
                // Multiple files
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $fileName = time() . '_' . uniqid() . '.' . $extension;
                        $uploadDir = (defined('PADI_ROOT') ? PADI_ROOT : dirname(dirname(__DIR__))) . '/uploads/assignments/';
                        $dbPath = 'uploads/assignments/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0750, true);
                        if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $fileName)) {
                            $uploadedFiles[] = $dbPath . $fileName;
                        }
                    }
                }
            } elseif (isset($files['error']) && $files['error'] === UPLOAD_ERR_OK) {
                // Single file
                $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $uploadDir = (defined('PADI_ROOT') ? PADI_ROOT : dirname(dirname(__DIR__))) . '/uploads/assignments/';
                $dbPath = 'uploads/assignments/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0750, true);
                if (move_uploaded_file($files['tmp_name'], $uploadDir . $fileName)) {
                    $uploadedFiles[] = $dbPath . $fileName;
                }
            }
        }

        if (!empty($uploadedFiles)) {
            $validated['upload_file'] = (count($uploadedFiles) === 1 && !isset($_FILES['files']))
                ? $uploadedFiles[0]
                : json_encode($uploadedFiles);
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
        $id = $this->request->param('id') ?: $this->request->input('id');

        $assignmentResult = $this->model->find($id);
        if (!$assignmentResult) {
            $this->error('Assignment result not found with ID: ' . $id, 404);
            return;
        }

        // Validate Deadline
        if (!empty($assignmentResult['assignment_id'])) {
            $this->checkDeadline($assignmentResult['assignment_id']);
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

        // Handle multiple file uploads
        $newUploadedFiles = [];

        $files = $_FILES['files'] ?? $_FILES['file'] ?? null;
        if ($files) {
            if (isset($files['name']) && is_array($files['name'])) {
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $fileName = time() . '_' . uniqid() . '.' . $extension;
                        $uploadDir = (defined('PADI_ROOT') ? PADI_ROOT : dirname(dirname(__DIR__))) . '/uploads/assignments/';
                        $dbPath = 'uploads/assignments/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0750, true);
                        if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $fileName)) {
                            $newUploadedFiles[] = $dbPath . $fileName;
                        }
                    }
                }
            } elseif (isset($files['error']) && $files['error'] === UPLOAD_ERR_OK) {
                // Single file
                $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                $fileName = time() . '_' . uniqid() . '.' . $extension;
                $uploadDir = (defined('PADI_ROOT') ? PADI_ROOT : dirname(dirname(__DIR__))) . '/uploads/assignments/';
                $dbPath = 'uploads/assignments/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0750, true);
                if (move_uploaded_file($files['tmp_name'], $uploadDir . $fileName)) {
                    $newUploadedFiles[] = $dbPath . $fileName;
                }
            }
        }

        // Selective Deletion Logic
        $existingFilesInput = $this->request->input('existing_files');
        $filesToKeep = [];

        if ($existingFilesInput !== null) {
            // Case 1: Frontend sent a specific list of existing files to keep
            $filesToKeep = json_decode($existingFilesInput, true);
            if (!is_array($filesToKeep)) {
                $filesToKeep = !empty($existingFilesInput) ? explode(',', $existingFilesInput) : [];
            }

            // Clean up files that are NOT in the keep list
            $oldFilesStr = $assignmentResult['upload_file'] ?? '';
            $oldFiles = json_decode($oldFilesStr, true);
            if (!is_array($oldFiles)) $oldFiles = !empty($oldFilesStr) ? [$oldFilesStr] : [];

            foreach ($oldFiles as $oldFile) {
                if (!in_array($oldFile, $filesToKeep)) {
                    if (file_exists($oldFile)) @unlink($oldFile);
                }
            }
        } else {
            // Case 2: Standard behavior (compatibility)
            // If new files are uploaded, delete ALL old files (legacy behavior)
            // If no new files, keep old ones (no change)
            if (!empty($newUploadedFiles)) {
                if (!empty($assignmentResult['upload_file'])) {
                    $oldFiles = json_decode($assignmentResult['upload_file'], true);
                    if (!is_array($oldFiles)) $oldFiles = [$assignmentResult['upload_file']];
                    foreach ($oldFiles as $oldFile) {
                        if (file_exists($oldFile)) @unlink($oldFile);
                    }
                }
                $filesToKeep = [];
            } else {
                $oldFilesStr = $assignmentResult['upload_file'] ?? '';
                $filesToKeep = json_decode($oldFilesStr, true);
                if (!is_array($filesToKeep)) $filesToKeep = !empty($oldFilesStr) ? [$oldFilesStr] : [];
            }
        }

        // Final Merge
        $finalFileList = array_merge($filesToKeep, $newUploadedFiles);
        if (!empty($finalFileList)) {
            $validated['upload_file'] = count($finalFileList) === 1 ? $finalFileList[0] : json_encode($finalFileList);
        } else {
            $validated['upload_file'] = null;
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
        $skipData = $this->request->query('skip_data');

        $targetClassIds = ($classroomFilterId)
            ? [(int)$classroomFilterId]
            : ($skipData ? [] : array_column($allClasses, 'id'));

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
    /**
     * Get summary matrix for a specific classroom (For Teachers)
     * Returns: { students, assignments, matrix }
     */
    public function teacherClassSummary()
    {
        $classroomId = $this->request->query('classroom_id');
        if (!$classroomId) {
            throw new \Exception('Classroom ID is required', 400);
        }

        // 1. Get all students in the classroom
        $students = \Wibiesana\Padi\Core\Query::find()
            ->select('student.id, student.name')
            ->from('student')
            ->innerJoin('classroom_member', 'student.id = classroom_member.student_id')
            ->where(['classroom_member.class_id' => $classroomId])
            ->orderBy('student.name ASC')
            ->all();

        // 2. Get all assignments for this classroom
        $assignments = \Wibiesana\Padi\Core\Query::find()
            ->select('assignment.id, assignment.name, subject.name as subject_name')
            ->from('assignment')
            ->innerJoin('assignment_class', 'assignment.id = assignment_class.assignment_id')
            ->leftJoin('subject', 'assignment.subject_id = subject.id')
            ->where(['assignment_class.classroom_id' => $classroomId])
            ->all();

        // 3. Get all scores for these students and assignments
        $assignmentIds = array_column($assignments, 'id');
        $studentIds = array_column($students, 'id');

        $matrix = [];
        if (!empty($assignmentIds) && !empty($studentIds)) {
            $scores = \Wibiesana\Padi\Core\Query::find()
                ->from('assignment_result')
                ->where(['IN', 'assignment_id', $assignmentIds])
                ->andWhere(['IN', 'created_by', $studentIds])
                ->all();

            foreach ($scores as $score) {
                $matrix[$score['created_by']][$score['assignment_id']] = (float)$score['score'];
            }
        }

        return [
            'students' => $students,
            'assignments' => $assignments,
            'matrix' => $matrix
        ];
    }

    /**
     * Check if assignment deadline has passed for students
     */
    private function checkDeadline($assignmentId)
    {
        $user = $this->request->user ?? Auth::user();
        $roleId = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        // Student role (student or 4)
        if ($roleId === 'student' || (int)$roleId === 4) {
            $assignment = (new \App\Models\Assignment())->find($assignmentId);
            if ($assignment && !empty($assignment['end_date'])) {
                $deadline = strtotime($assignment['end_date']);
                if ($deadline > 0 && $deadline < time()) {
                    throw new \Exception('Maaf, waktu pengerjaan tugas ini sudah habis.', 403);
                }
            }
        }
    }
}
