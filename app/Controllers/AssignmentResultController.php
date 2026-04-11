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
            $this->model->with(['assignment.subject', 'assignment.createdBy.teacher', 'createdBy.teacher', 'updatedBy']);
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
                    $assignmentModel->with(['createdBy.teacher', 'subject', 'semester']);
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
        $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.teacher', 'updatedBy:id,username']);
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
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.teacher', 'updatedBy:id,username']);
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
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.teacher', 'updatedBy:id,username']);
            return \App\Resources\AssignmentResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assignment submission', $e);
        }
    }

    /**
     * Get all submissions for a specific assignment (For Teachers)
     */
    public function submissionsByAssignment()
    {
        $id = $this->request->param('id');
        
        $resultsQuery = \App\Models\AssignmentResult::findQuery()
            ->where(['assignment_id' => $id]);

        $results = $resultsQuery->all();

        if (!empty($results)) {
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.teacher', 'updatedBy:id,username']);
            $this->model->loadRelations($results);
        }

        return [
            'success' => true,
            'data' => \App\Resources\AssignmentResultResource::collection($results)
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
            $this->model->with(['assignment.createdBy.teacher', 'assignment.subject', 'createdBy.teacher', 'updatedBy:id,username']);
            return \App\Resources\AssignmentResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to save score', $e);
        }
    }
}
