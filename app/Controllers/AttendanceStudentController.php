<?php

namespace App\Controllers;

use App\Controllers\Base\AttendanceStudentController as BaseController;
use App\Resources\AttendanceStudentResource;
use Wibiesana\Padi\Core\Database;


class AttendanceStudentController extends BaseController
{
    /**
     * Get attendance by lesson session
     * GET /attendance-student/session/{id}
     */
    public function getBySession()
    {
        $sessionId = $this->request->param('id');
        $results = $this->model->findQuery()->where(['lesson_session_id' => $sessionId])->all();

        // Load student relations
        if (!empty($results)) {
            $this->model->loadRelations($results);
        }

        return AttendanceStudentResource::collection($results);
    }

    /**
     * Get attendance summary for a student
     * GET /attendance-student/summary?student_id={id}
     */
    public function getSummary()
    {
        $studentId = $this->request->query('student_id');

        if (!$studentId) {
            // If no student_id provided, try to get from logged in user if they are a student
            $user = \Wibiesana\Padi\Core\Auth::user();
            if ($user && $user->role === 'student') {
                // Assuming role 'student' or checking user_id -> student table link
                // For now, let's rely on explicit student_id or strict checking
                // We need to fetch student ID from user_id relation
                $student = \App\Models\Student::findQuery()->where(['user_id' => $user->user_id])->one();
                if ($student) {
                    $studentId = $student['id'];
                }
            }
        }

        if (!$studentId) {
            return $this->response->json(['message' => 'Student ID is required'], 400);
        }

        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        $date = $this->request->query('date'); // Fallback for single date

        if (!$startDate && $date) {
            $startDate = $date;
        }

        $summary = $this->model->getSummaryByStudent((int)$studentId, $startDate, $endDate);

        // Fetch anomalies
        $anomalies = [];
        if ($startDate) {
            $anomalies = $this->model->getAnomaliesByStudent((int)$studentId, $startDate, $endDate);
        }

        return $this->response->json([
            'data' => $summary,
            'anomalies' => $anomalies
        ]);
    }

    /**
     * Save batch attendance
     * POST /attendance-student/batch
     */
    public function batchStore()
    {
        $payload = $this->request->all();
        $items = $payload['items'] ?? [];
        $sessionId = $payload['lesson_session_id'] ?? null;

        if (!$sessionId) {
            return $this->response->json(['message' => 'Lesson Session ID is required'], 400);
        }

        if (empty($items)) {
            return $this->response->json(['message' => 'No items provided'], 400);
        }

        try {
            return Database::transaction(function () use ($items, $sessionId) {
                foreach ($items as $item) {
                    $studentId = $item['student_id'];
                    $status = $item['status'];
                    $note = $item['note'] ?? '';

                    $existing = $this->model->findQuery()->where([
                        'lesson_session_id' => $sessionId,
                        'student_id' => $studentId
                    ])->one();

                    if ($existing) {
                        $this->model->update($existing['id'], [
                            'status' => $status,
                            'note' => $note
                        ]);
                    } else {
                        $this->model->create([
                            'lesson_session_id' => $sessionId,
                            'student_id' => $studentId,
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
}
