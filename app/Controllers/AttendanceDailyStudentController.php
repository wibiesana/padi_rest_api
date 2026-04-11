<?php

namespace App\Controllers;

use App\Controllers\Base\AttendanceDailyStudentController as BaseController;
use App\Resources\AttendanceDailyStudentResource;
use Wibiesana\Padi\Core\Database;

class AttendanceDailyStudentController extends BaseController
{
    /**
     * Get all attendance daily students with filters
     * GET /attendance-daily-student
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));

        $filters = [
            'date' => $this->request->query('date'),
            'start_date' => $this->request->query('start_date'),
            'end_date' => $this->request->query('end_date'),
            'class_id' => $this->request->query('class_id'),
            'student_id' => $this->request->query('student_id'),
            'status' => $this->request->query('status'),
            'search' => $this->request->query('search')
        ];

        if (!empty($filters['date']) || !empty($filters['class_id']) || !empty($filters['search'])) {
            $result = $this->model->filterPaginate($filters, $page, $perPage);
            return AttendanceDailyStudentResource::collection($result);
        }

        // Default to filterPaginate even without filters to ensure DESC sort
        $result = $this->model->filterPaginate($filters, $page, $perPage);
        return AttendanceDailyStudentResource::collection($result);
    }

    /**
     * Save batch attendance
     * POST /attendance-daily-students/batch
     */
    public function batchStore()
    {
        $payload = $this->request->all();
        $items = $payload['items'] ?? [];
        $date = $payload['attendance_date'] ?? date('Y-m-d');

        if (empty($items)) {
            return $this->response->json(['message' => 'No items provided'], 400);
        }

        $classId = $payload['class_id'] ?? null;
        if (!$classId) {
            return $this->response->json(['message' => 'Class ID is required'], 400);
        }

        // Permission Check
        $user = \Wibiesana\Padi\Core\Auth::user();
        if ($user && $user->role !== 'superadmin' && $user->role !== 'admin') {
            $classroom = \App\Models\Classroom::findQuery()->where([
                'id' => $classId,
                'teacher_id' => $user->user_id
            ])->one();

            if (!$classroom) {
                return $this->response->json(['message' => 'Unauthorized access to this class'], 403);
            }
        }

        try {
            return Database::transaction(function () use ($items, $date) {
                foreach ($items as $item) {
                    $studentId = $item['student_id'];
                    $status = $item['status'];
                    $note = $item['note'] ?? '';

                    $existing = $this->model->findQuery()->where([
                        'student_id' => $studentId,
                        'attendance_date' => $date
                    ])->one();

                    if ($existing) {
                        $this->model->update($existing['id'], [
                            'status' => $status,
                            'note' => $note,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        $this->model->create([
                            'student_id' => $studentId,
                            'attendance_date' => $date,
                            'status' => $status,
                            'note' => $note
                        ]);
                    }
                }
                return ['success' => true, 'message' => 'Batch attendance saved successfully'];
            });
        } catch (\Exception $e) {
            return $this->response->json(['message' => 'Failed to save batch attendance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a single attendance record
     * POST /attendance-daily-student
     */
    public function store()
    {
        $data = $this->request->all();

        // Validate required fields
        if (empty($data['student_id']) || empty($data['attendance_date'])) {
            return $this->response->json(['message' => 'Student ID and Attendance Date are required'], 400);
        }

        // Check for existing record
        $existing = $this->model->findQuery()->where([
            'student_id' => $data['student_id'],
            'attendance_date' => $data['attendance_date']
        ])->one();

        if ($existing) {
            // Update existing record
            $updateData = [
                'status' => $data['status'] ?? $existing['status'],
                'note' => $data['note'] ?? $existing['note'],
                // Add check_in/out if needed
                /*'check_in_time' => $data['check_in_time'] ?? $existing['check_in_time'],
                 'check_out_time' => $data['check_out_time'] ?? $existing['check_out_time'],*/
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->model->update($existing['id'], $updateData);

            // Fetch updated resource to return consistent structure
            $updated = $this->model->findQuery()->where(['id' => $existing['id']])->one();
            if ($updated) {
                $temp = [$updated];
                $this->model->loadStudent($temp);
                $updated = $temp[0];
            }
            return new AttendanceDailyStudentResource($updated);
        } else {
            // Create new record
            $id = $this->model->create([
                'student_id' => $data['student_id'],
                'attendance_date' => $data['attendance_date'],
                'status' => $data['status'] ?? 1,
                'note' => $data['note'] ?? ''
            ]);

            $newRecord = $this->model->findQuery()->where(['id' => $id])->one();
            if ($newRecord) {
                $temp = [$newRecord];
                $this->model->loadStudent($temp);
                $newRecord = $temp[0];
            }
            return new AttendanceDailyStudentResource($newRecord);
        }
    }
}
