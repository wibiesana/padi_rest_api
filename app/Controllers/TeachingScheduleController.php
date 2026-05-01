<?php

namespace App\Controllers;

use App\Controllers\Base\TeachingScheduleController as BaseController;
use Wibiesana\Padi\Core\Auth;

class TeachingScheduleController extends BaseController
{
    /**
     * Override index to filter by teacher_id for teachers
     */
    public function index()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $roleId = is_array($user) ? ($user['role_id'] ?? $user['role'] ?? null) : ($user->role_id ?? $user->role ?? null);

        $conditions = [];
        if ($user) {
            if ($roleId === 'teacher' || (int)$roleId === 2) {
                $conditions['teacher_id'] = $userId;
            } elseif ($roleId === 'student' || (int)$roleId === 4) {
                // Find class of this student
                $member = \App\Models\ClassroomMember::findQuery()
                    ->where(['student_id', '=', $userId])
                    ->one();
                if ($member) {
                    $conditions['classroom_id'] = $member['class_id'];
                } else {
                    $conditions['classroom_id'] = 0; // Return empty
                }
            }
        }

        // Merge with request filters
        $qClassId = $this->request->query('classroom_id');
        if ($qClassId) $conditions['classroom_id'] = $qClassId;

        $qTeacherId = $this->request->query('teacher_id');
        if ($qTeacherId) $conditions['teacher_id'] = $qTeacherId;

        $qSemesterId = $this->request->query('semester_id');
        if ($qSemesterId) $conditions['semester_id'] = $qSemesterId;

        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));
        $search = $this->request->query('search');

        if ($search) {
            $result = $this->model->searchPaginate($search, $page, $perPage);
        } else {
            $result = $this->model->paginate($page, $perPage, $conditions);
        }

        return \App\Resources\TeachingScheduleResource::collection($result);
    }

    /**
     * Override all to filter by teacher_id for teachers
     */
    public function all()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $roleId = is_array($user) ? ($user['role_id'] ?? $user['role'] ?? null) : ($user->role_id ?? $user->role ?? null);

        $conditions = [];
        if ($user) {
            if ($roleId === 'teacher' || (int)$roleId === 2) {
                $conditions['teacher_id'] = $userId;
            } elseif ($roleId === 'student' || (int)$roleId === 4) {
                // Find class of this student
                $member = \App\Models\ClassroomMember::findQuery()
                    ->where(['student_id', '=', $userId])
                    ->one();
                if ($member) {
                    $conditions['classroom_id'] = $member['class_id'];
                } else {
                    $conditions['classroom_id'] = 0; // Return empty
                }
            }
        }

        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

        if (!empty($conditions)) {
            $data = $this->model->where($conditions);
            return \App\Resources\TeachingScheduleResource::collection($data);
        }
        return parent::all();
    }

    /**
     * Get teaching schedule for today for the logged in teacher
     * GET /teaching-schedule/today
     */
    public function getTodaySchedule()
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized', 401);
        }

        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);

        // Find associated profiles to determine filtering regardless of role name
        // In this framework, profile tables (teacher/student) typically share the same ID as the user
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();
        $student = \App\Models\Student::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();

        // Normalize checks
        $isTeacher = ($role === 'teacher' || (int)$role === 2 || $teacher);
        $isStudent = ($role === 'student' || (int)$role === 4 || $student);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        if (!$isTeacher && !$isStudent && !$isAdmin) {
            return $this->json(['success' => true, 'item' => ['schedules' => []]]);
        }

        $dayOfWeek = date('N');

        // Find active semester
        $semester = \App\Models\Semester::findQuery()->where(['status', '=', 1])->one();
        if (!$semester) {
            return $this->json(['success' => true, 'item' => ['schedules' => []]]);
        }

        $query = \App\Models\TeachingSchedule::findQuery()
            ->where(['day_of_week', '=', $dayOfWeek])
            ->andWhere(['semester_id', '=', $semester['id']])
            ->andWhere(['status', '=', 1]);

        // Filter priority: Teacher Profile > Student Profile > Admin (No Filter)
        if ($teacher) {
            $query->andWhere(['teacher_id', '=', $teacher['id']]);
        } else if ($student) {
            $member = \App\Models\ClassroomMember::findQuery()
                ->where(['student_id', '=', $student['id']])
                ->one();

            if (!$member) {
                return $this->json(['success' => true, 'item' => ['schedules' => []]]);
            }
            $query->andWhere(['classroom_id', '=', $member['class_id']]);
        }

        $schedules = $query->orderBy('start_time ASC')->all();

        // Eager load relationships using model
        $this->model->with(['classroom:id,name', 'subject:id,name', 'teacher:id,name']);
        $this->model->loadRelations($schedules);

        // Get periods from settings
        $setting = \App\Models\Setting::findQuery()->where(['status', '=', 1])->one();
        $periods = [];
        if ($setting) {
            $settingData = json_decode($setting['setting'], true);
            $periods = $settingData['daily_schedule'] ?? [];
        }

        return $this->json([
            'success' => true,
            'message' => 'Success',
            'item' => [
                'day' => (int)$dayOfWeek,
                'schedules' => \App\Resources\TeachingScheduleResource::collection($schedules),
                'periods' => $periods,
                'semester' => $semester['name']
            ]
        ]);
    }

    /**
     * Get timetable for a specific teacher
     * GET /teaching-schedule/get-timetable-by-teacher?teacher_id=x&semester_id=y
     */
    public function getTimetableByTeacher()
    {
        $teacherId = $this->request->query('teacher_id');
        $semesterId = $this->request->query('semester_id');

        if (!$teacherId || !$semesterId) {
            throw new \Exception('Teacher ID and Semester ID are required', 400);
        }

        $teacher = (new \App\Models\Teacher())->find((int)$teacherId);
        if (!$teacher) {
            throw new \Exception('Teacher not found', 404);
        }

        $schedules = \App\Models\TeachingSchedule::findQuery()
            ->where(['teacher_id', '=', (int)$teacherId])
            ->andWhere(['semester_id', '=', (int)$semesterId])
            ->all();

        // Eager load relations
        $this->model->with(['classroom:id,name', 'subject:id,name']);
        $this->model->loadRelations($schedules);

        // Summary
        $totalHours = 0;
        foreach ($schedules as $sch) {
            $totalHours += $sch['periods_per_week'] ?? 0;
        }

        $setting = \App\Models\Setting::findQuery()->where(['status', '=', 1])->one();
        $periods = [];
        if ($setting) {
            $settingData = json_decode($setting['setting'], true);
            $periods = $settingData['daily_schedule'] ?? [];
        }

        $uniqueClasses = array_unique(array_filter(array_map(fn($s) => $s['classroom_id'] ?? null, $schedules)));

        return [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'periods' => $periods,
            'summary' => [
                'total_hours_per_week' => $totalHours,
                'total_classes' => count($uniqueClasses)
            ]
        ];
    }

    /**
     * Get timetable for all teachers
     * GET /teaching-schedule/get-timetable-all-teachers?semester_id=y&search=name
     */
    public function getTimetableAllTeachers()
    {
        $semesterId = $this->request->query('semester_id');
        $search = $this->request->query('search');
        $teacherId = $this->request->query('teacher_id');

        if (!$semesterId) {
            throw new \Exception('Semester ID is required', 400);
        }

        // Get teachers (filtered by search or teacher_id)
        $teachersQuery = \App\Models\Teacher::findQuery()->where(['status', '=', 1]);
        if ($teacherId) {
            $teachersQuery->andWhere(['id', '=', (int)$teacherId]);
        } elseif ($search) {
            $teachersQuery->andWhere(['name', 'LIKE', "%$search%"]);
        }
        $teachers = $teachersQuery->all();

        // Get schedules for this semester
        $schedulesQuery = \App\Models\TeachingSchedule::findQuery()
            ->where(['semester_id', '=', (int)$semesterId])
            ->andWhere(['status', '=', 1]);

        $schedules = $schedulesQuery->all();

        // Eager load relations for schedules efficiently
        $this->model->with(['classroom:id,name', 'subject:id,name']);
        $this->model->loadRelations($schedules);

        // Group schedules by teacher for fast mapping
        $schedulesByTeacher = [];
        foreach ($schedules as $schedule) {
            $tId = $schedule['teacher_id'];
            if (!isset($schedulesByTeacher[$tId])) {
                $schedulesByTeacher[$tId] = [];
            }
            $schedulesByTeacher[$tId][] = $schedule;
        }

        $resultTeachers = [];
        foreach ($teachers as $teacher) {
            $tId = $teacher['id'];
            $teacherSchedules = $schedulesByTeacher[$tId] ?? [];

            // If no search, skip teachers without schedules
            if (!$search && empty($teacherSchedules)) {
                continue;
            }

            // Calculate total hours
            $totalHours = 0;
            foreach ($teacherSchedules as $sch) {
                $totalHours += $sch['periods_per_week'] ?? 0;
            }

            $resultTeachers[] = [
                'id' => $teacher['id'],
                'name' => $teacher['name'],
                'nip' => $teacher['nip'] ?? '-',
                'total_hours' => $totalHours,
                'schedules' => $teacherSchedules
            ];
        }

        // Get periods from settings
        $setting = \App\Models\Setting::findQuery()->where(['status', '=', 1])->one();
        $periods = [];
        if ($setting) {
            $settingData = json_decode($setting['setting'], true);
            $periods = $settingData['daily_schedule'] ?? [];
        }

        return [
            'teachers' => $resultTeachers,
            'periods' => $periods
        ];
    }

    /**
     * Get timetable for specific classroom
     * GET /teaching-schedule/get-timetable-by-classroom?classroom_id=x&semester_id=y
     */
    public function getTimetableByClassroom()
    {
        $classroomId = $this->request->query('classroom_id');
        $semesterId = $this->request->query('semester_id');

        if (!$classroomId || !$semesterId) {
            throw new \Exception('Classroom ID and Semester ID are required', 400);
        }

        $classroom = (new \App\Models\Classroom())->find((int)$classroomId);
        if (!$classroom) {
            throw new \Exception('Classroom not found', 404);
        }

        $schedules = \App\Models\TeachingSchedule::findQuery()
            ->where(['classroom_id', '=', (int)$classroomId])
            ->andWhere(['semester_id', '=', (int)$semesterId])
            ->andWhere(['status', '=', 1])
            ->all();

        // Eager load relations
        $this->model->with(['teacher:id,name', 'subject:id,name']);
        $this->model->loadRelations($schedules);

        $totalHours = 0;
        foreach ($schedules as $sch) {
            $totalHours += (float)($sch['periods_per_week'] ?? 0);
        }

        $setting = \App\Models\Setting::findQuery()->where(['status', '=', 1])->one();
        $periods = [];
        if ($setting) {
            $settingData = json_decode($setting['setting'], true);
            $periods = $settingData['daily_schedule'] ?? [];
        }

        return [
            'classroom' => $classroom,
            'schedules' => $schedules,
            'periods' => $periods,
            'summary' => [
                'total_hours_per_week' => $totalHours
            ]
        ];
    }

    /**
     * Get timetable for all classrooms
     * GET /teaching-schedule/get-timetable-all-classrooms?semester_id=y&search=name
     */
    public function getTimetableAllClassrooms()
    {
        $semesterId = $this->request->query('semester_id');
        $search = $this->request->query('search');
        $classroomId = $this->request->query('classroom_id');

        if (!$semesterId) {
            throw new \Exception('Semester ID is required', 400);
        }

        $classroomsQuery = \App\Models\Classroom::findQuery()->where(['status', '=', 1]);
        if ($classroomId) {
            $classroomsQuery->andWhere(['id', '=', (int)$classroomId]);
        } elseif ($search) {
            $classroomsQuery->andWhere(['name', 'LIKE', "%$search%"]);
        }
        $classrooms = $classroomsQuery->all();

        $schedules = \App\Models\TeachingSchedule::findQuery()
            ->where(['semester_id', '=', (int)$semesterId])
            ->andWhere(['status', '=', 1])
            ->all();

        // Eager load relations
        $this->model->with(['teacher:id,name', 'subject:id,name']);
        $this->model->loadRelations($schedules);

        $schedulesByClass = [];
        foreach ($schedules as $schedule) {
            $cIdStr = $schedule['classroom_id'] ?? $schedule['class_id'] ?? null;
            if ($cIdStr !== null && $cIdStr !== '') {
                $cId = (int)$cIdStr;
                if (!isset($schedulesByClass[$cId])) {
                    $schedulesByClass[$cId] = [];
                }
                $schedulesByClass[$cId][] = $schedule;
            }
        }

        $resultClassrooms = [];
        foreach ($classrooms as $classroom) {
            $cId = (int)$classroom['id'];
            $classSchedules = $schedulesByClass[$cId] ?? [];

            // If a specific class is requested or a search is active, show it even if empty
            // Otherwise, skip empty classrooms to keep the global list clean
            if (!$classroomId && !$search && empty($classSchedules)) {
                continue;
            }

            $totalHours = 0;
            foreach ($classSchedules as $sch) {
                $totalHours += (float)($sch['periods_per_week'] ?? 0);
            }

            $resultClassrooms[] = [
                'id' => $cId,
                'name' => $classroom['name'],
                'total_hours' => $totalHours,
                'schedules' => $classSchedules
            ];
        }

        $setting = \App\Models\Setting::findQuery()->where(['status', '=', 1])->one();
        $periods = [];
        if ($setting) {
            $settingData = json_decode($setting['setting'], true);
            $periods = $settingData['daily_schedule'] ?? [];
        }

        return [
            'classrooms' => $resultClassrooms,
            'periods' => $periods
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $validated = $this->validateStore();
        $this->checkScheduleConflict($validated);

        try {
            $id = $this->model->create($validated);
            $item = $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username'])->find($id);
            return $this->created(\App\Resources\TeachingScheduleResource::make($item ?? []));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create teaching schedule', $e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        $id = $this->request->param('id');
        $item = $this->model->find($id);

        if (!$item) {
            throw new \Exception('Teaching Schedule not found', 404);
        }

        $validated = $this->validateUpdate();
        $this->checkScheduleConflict($validated, (int)$id);

        try {
            $this->model->update($id, $validated);
            $item = $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username'])->find($id);
            return \App\Resources\TeachingScheduleResource::make($item ?? []);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update teaching schedule', $e);
        }
    }

    /**
     * Check for schedule conflicts via API
     */
    public function checkConflict()
    {
        $data = $this->request->all();
        $id = isset($data['id']) ? (int)$data['id'] : null;

        try {
            $this->checkScheduleConflict($data, $id);
            return $this->json(['success' => true, 'message' => 'No conflict found']);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
                'message_code' => $e->getCode() == 422 ? 'SCHEDULE_CONFLICT' : 'ERROR'
            ], 422);
        }
    }

    /**
     * Internal helper to check for schedule overlaps
     */
    protected function checkScheduleConflict(array $data, ?int $excludeId = null)
    {
        $teacherId = $data['teacher_id'] ?? null;
        $classId = $data['classroom_id'] ?? null;
        $dayOfWeek = $data['day_of_week'] ?? null;
        $semesterId = $data['semester_id'] ?? null;
        $startPeriod = $data['period_number'] ?? null;
        $endPeriod = $data['period_number_end'] ?? null;

        if (!$teacherId || !$classId || !$dayOfWeek || !$semesterId || !$startPeriod || !$endPeriod) {
            return; // Not enough data to check
        }

        // 1. Check Teacher Conflict
        $teacherConflict = \App\Models\TeachingSchedule::findQuery()
            ->where(['teacher_id', '=', $teacherId])
            ->andWhere(['day_of_week', '=', $dayOfWeek])
            ->andWhere(['semester_id', '=', $semesterId])
            ->andWhere(['status', '=', 1]);

        if ($excludeId) {
            $teacherConflict->andWhere(['id', '!=', $excludeId]);
        }

        $teacherSchedules = $teacherConflict->all();
        foreach ($teacherSchedules as $sch) {
            if ($this->isPeriodOverlap((int)$startPeriod, (int)$endPeriod, (int)$sch['period_number'], (int)$sch['period_number_end'])) {
                $errorData = [
                    'type' => 'teacher',
                    'day' => (int)$dayOfWeek,
                    'start_period' => $sch['period_number'],
                    'end_period' => $sch['period_number_end']
                ];
                throw new \Exception(json_encode($errorData), 422);
            }
        }

        // 2. Check Class Conflict
        $classConflict = \App\Models\TeachingSchedule::findQuery()
            ->where(['classroom_id', '=', $classId])
            ->andWhere(['day_of_week', '=', $dayOfWeek])
            ->andWhere(['semester_id', '=', $semesterId])
            ->andWhere(['status', '=', 1]);

        if ($excludeId) {
            $classConflict->andWhere(['id', '!=', $excludeId]);
        }

        $classSchedules = $classConflict->all();
        foreach ($classSchedules as $sch) {
            if ($this->isPeriodOverlap((int)$startPeriod, (int)$endPeriod, (int)$sch['period_number'], (int)$sch['period_number_end'])) {
                $errorData = [
                    'type' => 'classroom',
                    'day' => (int)$dayOfWeek,
                    'start_period' => $sch['period_number'],
                    'end_period' => $sch['period_number_end']
                ];
                throw new \Exception(json_encode($errorData), 422);
            }
        }
    }

    private function isPeriodOverlap(int $s1, int $e1, int $s2, int $e2): bool
    {
        return $s1 <= $e2 && $e1 >= $s2;
    }

    private function getDayName(int $day): string
    {
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
        return $days[$day] ?? 'Unknown Day';
    }

    protected function validateStore()
    {
        return $this->validate([
            'classroom_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'semester_id' => 'required|integer',
            'day_of_week' => 'required|integer',
            'period_number' => 'required|integer',
            'period_number_end' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'periods_per_week' => 'numeric',
            'status' => 'integer'
        ]);
    }

    protected function validateUpdate()
    {
        return $this->validate([
            'classroom_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'semester_id' => 'required|integer',
            'day_of_week' => 'required|integer',
            'period_number' => 'required|integer',
            'period_number_end' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'periods_per_week' => 'numeric',
            'status' => 'integer'
        ]);
    }

    /**
     * Store Imported Data
     * POST /teaching-schedule/import-store
     */
    public function importStore()
    {
        $data = $this->request->all();
        $records = $data['records'] ?? [];

        if (empty($records)) {
            throw new \Exception('No data to store', 400);
        }

        $successCount = 0;
        $errors = [];

        foreach ($records as $index => $record) {
            try {
                $this->model->create([
                    'teacher_id' => $record['teacher_id'],
                    'classroom_id' => $record['classroom_id'],
                    'subject_id' => $record['subject_id'],
                    'semester_id' => $record['semester_id'],
                    'day_of_week' => $record['day_of_week'],
                    'period_number' => $record['period_number'],
                    'period_number_end' => $record['period_number_end'],
                    'periods_per_week' => $record['periods_per_week'],
                    'start_time' => $record['start_time'],
                    'end_time' => $record['end_time'],
                    'status' => $record['status'] ?? 1,
                    // ASC IDs
                    'asc_lesson_id' => $record['asc_lesson_id'] ?? null,
                    'asc_teacher_id' => $record['asc_teacher_id'] ?? null,
                    'asc_class_id' => $record['asc_class_id'] ?? null,
                    'asc_subject_id' => $record['asc_subject_id'] ?? null,
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Record $index: " . $e->getMessage();
            }
        }

        return [
            'success_count' => $successCount,
            'errors' => $errors
        ];
    }
}
