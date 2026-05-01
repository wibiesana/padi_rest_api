<?php

namespace App\Controllers;

use App\Controllers\Base\LessonSessionController as BaseController;

use App\Resources\LessonSessionResource;
use Wibiesana\Padi\Core\Auth;

class LessonSessionController extends BaseController
{
    /**
     * Get all lessonsessions with pagination and optional filter
     * GET /lessonsessions
     */
    public function index()
    {
        $user = Auth::user();
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule.classroom', 'teachingSchedule.subject', 'teacher:id,name']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search', '');

        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        $filters = [];
        if ($this->request->query('session_date')) {
            $filters['session_date'] = $this->request->query('session_date');
        }
        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $filters['teacher_id'] = $userId;
        } elseif ($this->request->query('teacher_id')) {
            $filters['teacher_id'] = (int)$this->request->query('teacher_id');
        }
        if ($this->request->query('status')) {
            $filters['status'] = $this->request->query('status');
        }

        $result = $this->model->searchPaginate($search, $page, $perPage, null, $filters);

        return LessonSessionResource::collection($result);
    }

    /**
     * Get single lessonsession
     * GET /lessonsessions/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule.classroom', 'teachingSchedule.subject', 'teacher:id,name']);

        $lessonsession = $this->model->find($id);

        if (!$lessonsession) {
            throw new \Exception('LessonSession not found', 404);
        }

        $lessonsession = $this->loadItemRelations($lessonsession);
        return LessonSessionResource::make($lessonsession);
    }

    protected function loadItemRelations($lessonsession)
    {
        if (empty($lessonsession)) return $lessonsession;

        // Manually load nested relations for the teachingSchedule
        if (!empty($lessonsession['teaching_schedule_id'])) {
            $tsModel = new \App\Models\TeachingSchedule();
            $ts = $tsModel->find($lessonsession['teaching_schedule_id']);
            if ($ts) {
                $tsSet = [$ts];
                $tsModel->with(['subject', 'classroom'])->loadRelations($tsSet);
                $lessonsession['teachingSchedule'] = $tsSet[0];
            }
        }
        return $lessonsession;
    }

    /**
     * Create or update lessonsession (Upsert)
     * POST /lessonsessions
     */
    public function store()
    {
        $validated = $this->validate([
            'teaching_schedule_id' => 'required|integer',
            'session_date' => 'required',
            'teacher_id' => 'required|integer',
            'material' => 'string',
            'note' => 'string|max:255',
            'status' => 'string|max:20',
            'allow_self_attendance' => 'integer',
        ]);

        if (!empty($validated['allow_self_attendance'])) {
            $validated['qr_token'] = $this->generateQrToken();
        }

        // Check if exists for upsert
        $existing = $this->model->where([
            'teaching_schedule_id' => $validated['teaching_schedule_id'],
            'session_date' => $validated['session_date']
        ]);
        $existing = !empty($existing) ? $existing[0] : null;

        try {
            if ($existing) {
                $id = $existing['id'];
                $this->model->update($id, $validated);
            } else {
                $id = $this->model->create($validated);
            }

            // Auto-generated eager loading
            $this->model->with(['teachingSchedule.classroom', 'teachingSchedule.subject', 'teacher:id,name']);
            $lessonsession = $this->model->find($id);
            $lessonsession = $this->loadItemRelations($lessonsession);

            return $existing
                ? LessonSessionResource::make($lessonsession)
                : $this->created(LessonSessionResource::make($lessonsession));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to save lessonsession', $e);
        }
    }

    /**
     * Update lessonsession
     * PUT /lessonsessions/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $lessonsession = $this->model->find($id);

        if (!$lessonsession) {
            throw new \Exception('LessonSession not found', 404);
        }

        $validated = $this->validate([
            'teaching_schedule_id' => 'required|integer',
            'session_date' => 'required',
            'teacher_id' => 'required|integer',
            'material' => 'string',
            'note' => 'string|max:255',
            'status' => 'string|max:20',
            'allow_self_attendance' => 'integer',
        ]);

        if (!empty($validated['allow_self_attendance']) && empty($lessonsession['qr_token'])) {
            $validated['qr_token'] = $this->generateQrToken();
        }

        try {
            $this->model->update($id, $validated);

            // Auto-generated eager loading
            $this->model->with(['teachingSchedule.classroom', 'teachingSchedule.subject', 'teacher:id,name']);
            $lessonsession = $this->model->find($id);
            $lessonsession = $this->loadItemRelations($lessonsession);
            return LessonSessionResource::make($lessonsession);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update lessonsession', $e);
        }
    }

    public function teachingSchedule()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search', '');

        // Support both 'session_date' and 'date' for better frontend compatibility
        $date = $_GET['session_date'] ?? $_GET['date'] ?? $this->request->param('session_date') ?? $this->request->param('date');
        $reqTeacherId = $_GET['teacher_id'] ?? $this->request->param('teacher_id');
        $status = $_GET['status'] ?? $this->request->query('status');

        if (empty($date) || $date === 'undefined') {
            return $this->response->json([
                'success' => false,
                'message' => 'Session date is required for schedule lookup.',
                'item' => ['data' => []]
            ], 400);
        }

        // Determine final teacher_id based on profile and role
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();
        $finalTeacherId = null;

        if ($teacher) {
            $finalTeacherId = $teacher['id'];
        } else if ($reqTeacherId) {
            $finalTeacherId = (int)$reqTeacherId;
        }

        $result = $this->model->getScheduleForDate(
            $date,
            $finalTeacherId,
            $page,
            $perPage,
            $search,
            ['status' => $status]
        );

        return $this->response->json([
            'success' => true,
            'message' => 'Success',
            'item' => $result
        ]);
    }

    protected function generateQrToken(): string
    {
        return bin2hex(random_bytes(16)) . '_' . time();
    }
}
