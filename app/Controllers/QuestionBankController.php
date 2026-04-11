<?php

namespace App\Controllers;

use App\Controllers\Base\QuestionBankController as BaseController;

class QuestionBankController extends BaseController
{
    /**
     * Get all questionbanks with pagination, filtered by creator for teachers
     */
    /**
     * Get all questionbanks with pagination, filtered by creator for teachers
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
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : "question_bank.id DESC";

        $query = \Wibiesana\Padi\Core\Query::find()
            ->select("question_bank.*")
            ->from("question_bank")
            ->leftJoin('exam_events AS exam_events', 'question_bank.exam_event_id = exam_events.id')
            ->leftJoin('users AS users', 'question_bank.created_by = users.id')
            ->leftJoin('teacher AS teacher', 'question_bank.teacher_id = teacher.id')
            ->leftJoin('users AS users_updated_by', 'question_bank.updated_by = users_updated_by.id');

        // Apply filters (Identity/Role)
        if (!$isAdmin) {
            $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->one();
            $teacherId = $teacher ? $teacher['id'] : $userId;

            $query->andWhere([
                'OR',
                ['question_bank.created_by' => $userId],
                ['question_bank.teacher_id' => $teacherId]
            ]);
        }

        // Apply search keyword
        if (!empty($search)) {
            $keyword = "%{$search}%";
            $query->andWhere([
                'OR',
                ['LIKE', 'exam_events.name', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'question_bank.description', $keyword],
                ['LIKE', 'question_bank.status', $keyword]
            ]);
        }

        $query->orderBy($orderBy);
        $result = $query->paginate($perPage, $page);

        if (!empty($result['data'])) {
            $this->model->loadRelations($result['data']);
        }

        // Reformat result to match the expected format for Resource::collection
        $paginatedResult = [
            'data' => $result['data'],
            'meta' => [
                'total' => (int)$result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page']
            ]
        ];

        return \App\Resources\QuestionBankResource::collection($paginatedResult);
    }

    /**
     * Get all questionbanks without pagination, filtered by creator for teachers
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
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : "question_bank.id DESC";

        $query = \Wibiesana\Padi\Core\Query::find()
            ->select("question_bank.*")
            ->from("question_bank")
            ->leftJoin('exam_events AS exam_events', 'question_bank.exam_event_id = exam_events.id')
            ->leftJoin('users AS users', 'question_bank.created_by = users.id')
            ->leftJoin('teacher AS teacher', 'question_bank.teacher_id = teacher.id')
            ->leftJoin('users AS users_updated_by', 'question_bank.updated_by = users_updated_by.id');

        if (!$isAdmin) {
            $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->one();
            $teacherId = $teacher ? $teacher['id'] : $userId;

            $query->andWhere([
                'OR',
                ['question_bank.created_by' => $userId],
                ['question_bank.teacher_id' => $teacherId]
            ]);
        }

        if (!empty($search)) {
            $keyword = "%{$search}%";
            $query->andWhere([
                'OR',
                ['LIKE', 'exam_events.name', $keyword],
                ['LIKE', 'users.username', $keyword],
                ['LIKE', 'teacher.name', $keyword],
                ['LIKE', 'users_updated_by.username', $keyword],
                ['LIKE', 'question_bank.name', $keyword],
                ['LIKE', 'question_bank.description', $keyword],
                ['LIKE', 'question_bank.status', $keyword]
            ]);
        }

        $query->orderBy($orderBy);
        $result = $query->limit(100)->all();

        if (!empty($result)) {
            $this->model->loadRelations($result);
        }

        return \App\Resources\QuestionBankResource::collection($result);
    }

    /**
     * Create new questionbank with automatic creator assignment
     */
    public function store()
    {
        $user = \Wibiesana\Padi\Core\Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

        $validated = $this->validate([
            'exam_event_id' => 'integer',
            'name' => 'required|string|max:50',
            'description' => 'string|max:100',
            'status' => 'integer',
        ]);

        // Auto assign creator
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;

        // Find teacher profile if any and assign
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->one();
        if ($teacher) {
            $validated['teacher_id'] = $teacher['id'];
        }

        try {
            $id = $this->model->create($validated);
            $this->model->with(['examEvent:id,name', 'createdBy:id,username', 'teacher:id,name', 'updatedBy:id,username']);
            $questionbank = $this->model->find($id);
            return $this->created(\App\Resources\QuestionBankResource::make($questionbank));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create questionbank', $e);
        }
    }
}
