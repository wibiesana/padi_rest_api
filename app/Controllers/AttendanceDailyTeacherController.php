<?php

namespace App\Controllers;

use App\Controllers\Base\AttendanceDailyTeacherController as BaseController;
use App\Resources\AttendanceDailyTeacherResource;
use Wibiesana\Padi\Core\Database;

class AttendanceDailyTeacherController extends BaseController
{
    /**
     * Get all attendance daily teachers with filters
     * GET /attendance-daily-teacher
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));

        $filters = [
            'date' => $this->request->query('date'),
            'teacher_id' => $this->request->query('teacher_id'),
            'status' => $this->request->query('status'),
            'search' => $this->request->query('search')
        ];

        if (!empty($filters['date']) || !empty($filters['teacher_id']) || !empty($filters['search'])) {
            $result = $this->model->filterPaginate($filters, $page, $perPage);
            return AttendanceDailyTeacherResource::collection($result);
        }

        // Default to filterPaginate even without filters to ensure DESC sort
        $result = $this->model->filterPaginate($filters, $page, $perPage);
        return AttendanceDailyTeacherResource::collection($result);
    }

    /**
     * Save batch attendance for teachers
     * POST /attendance-daily-teacher/batch
     */
    public function batchStore()
    {
        $payload = $this->request->all();
        $items = $payload['items'] ?? [];
        $date = $payload['attendance_date'] ?? date('Y-m-d');

        if (empty($items)) {
            return $this->response->json(['message' => 'No items provided'], 400);
        }

        try {
            return Database::transaction(function () use ($items, $date) {
                foreach ($items as $item) {
                    $teacherId = $item['teacher_id'];
                    $status = $item['status'];
                    $note = $item['note'] ?? '';
                    // teacher check in/out logic can be added here if payload has it

                    $existing = $this->model->findQuery()->where([
                        'teacher_id' => $teacherId,
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
                            'teacher_id' => $teacherId,
                            'attendance_date' => $date,
                            'status' => $status,
                            'note' => $note
                        ]);
                    }
                }
                return ['success' => true, 'message' => 'Batch teacher attendance saved successfully'];
            });
        } catch (\Exception $e) {
            return $this->response->json(['message' => 'Failed to save batch attendance: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store single teacher attendance (Upsert)
     * POST /attendance-daily-teacher
     */
    public function store()
    {
        $data = $this->request->all();

        // Validate
        if (empty($data['teacher_id']) || empty($data['attendance_date'])) {
            return $this->response->json(['message' => 'Teacher ID and Attendance Date are required'], 400);
        }

        // Check existing
        $existing = $this->model->findQuery()->where([
            'teacher_id' => $data['teacher_id'],
            'attendance_date' => $data['attendance_date']
        ])->one();

        if ($existing) {
            $updateData = [
                'status' => $data['status'] ?? $existing['status'],
                'note' => $data['note'] ?? $existing['note'],
                'check_in_time' => $data['check_in_time'] ?? $existing['check_in_time'],
                'check_out_time' => $data['check_out_time'] ?? $existing['check_out_time'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->model->update($existing['id'], $updateData);

            // Fetch updated
            $updated = $this->model->findQuery()->where(['id' => $existing['id']])->one();
            if ($updated) {
                $temp = [$updated];
                $this->model->loadTeacher($temp);
                $updated = $temp[0];
            }
            return new AttendanceDailyTeacherResource($updated);
        } else {
            // Create
            $id = $this->model->create([
                'teacher_id' => $data['teacher_id'],
                'attendance_date' => $data['attendance_date'],
                'status' => $data['status'] ?? 1,
                'note' => $data['note'] ?? '',
                'check_in_time' => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
            ]);

            $newRecord = $this->model->findQuery()->where(['id' => $id])->one();
            if ($newRecord) {
                $temp = [$newRecord];
                $this->model->loadTeacher($temp);
                $newRecord = $temp[0];
            }
            return new AttendanceDailyTeacherResource($newRecord);
        }
    }
}
