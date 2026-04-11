<?php

namespace App\Controllers;

use App\Controllers\Base\ClassroomMemberController as BaseController;

class ClassroomMemberController extends BaseController
{
    /**
     * Get all classroommembers with pagination
     * GET /classroom-member
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'student:id,name,nis,nisn,jenis_kelamin,status']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(1000, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search');
        $classId = $this->request->query('class_id');

        $conditions = [];
        if ($classId) {
            $conditions['class_id'] = $classId;
        }

        if ($search) {
            $search = substr($search, 0, 255);
            $result = $this->model->searchPaginate($search, $page, $perPage);
            // Re-apply class filter if searched
            if ($classId && isset($result['data'])) {
                $result['data'] = array_values(array_filter($result['data'], function ($item) use ($classId) {
                    return $item['class_id'] == $classId;
                }));
            }
        } else {
            $result = $this->model->paginate($page, $perPage, $conditions, 'student_id ASC');
        }

        return $this->json([
            'success' => true,
            'item' => \App\Resources\ClassroomMemberResource::collection($result)
        ]);
    }

    /**
     * Get students not assigned to any class in a semester
     * GET /classroom-member/unassigned
     */
    public function getUnassignedStudents()
    {
        $semesterId = $this->request->query('semester_id');
        if (!$semesterId) {
            throw new \Exception('Semester ID is required', 400);
        }

        // Use NOT EXISTS for better performance and to avoid issues with NULL values in NOT IN
        $subquery = \App\Models\ClassroomMember::findQuery()
            ->select('1')
            ->innerJoin('classroom', 'classroom_member.class_id = classroom.id')
            ->where('classroom_member.student_id = student.id')
            ->andWhere(['classroom.semester_id' => $semesterId]);

        $students = \App\Models\Student::findQuery()
            ->select(['student.id', 'student.name', 'student.nis', 'student.nisn'])
            ->where('(student.is_active = 1 OR student.status = 1)')
            ->andWhere("NOT EXISTS ({$subquery->buildSql()})", $subquery->getParams())
            ->orderBy('student.name ASC')
            ->all();

        // Debug logging
        file_put_contents(__DIR__ . '/../../../debug_unassigned.log', "Semester ID: $semesterId | Count: " . count($students) . "\n", FILE_APPEND);

        return $this->json(['success' => true, 'item' => $students]);
    }

    /**
     * Sync classroom members (Batch)
     * POST /classroom-member/sync
     */
    public function syncMembers()
    {
        $validated = $this->validate([
            'class_id' => 'required|integer',
            'student_ids' => 'present|array'
        ]);

        $classId = $validated['class_id'];
        $studentIds = $validated['student_ids'];

        $db = $this->model->getDb();
        $db->beginTransaction();

        try {
            // 1. Remove existing members
            $this->model::findQuery()
                ->where(['class_id' => $classId])
                ->delete();

            // 2. Insert new members
            if (!empty($studentIds)) {
                $rows = array_map(fn($sid) => [
                    'class_id' => $classId,
                    'student_id' => $sid
                ], $studentIds);
                $this->model->batchInsert($rows);
            }

            $db->commit();
            return $this->json(['success' => true, 'message' => 'Classroom members synced successfully']);
        } catch (\Exception $e) {
            $db->rollBack();
            $this->databaseError('Failed to sync members', $e);
        }
    }
}
