<?php

namespace App\Controllers;

use App\Controllers\Base\ClassroomController as BaseController;
use App\Resources\ClassroomResource;
use Wibiesana\Padi\Core\Auth;


class ClassroomController extends BaseController
{
    /**
     * Override index to filter by teacher_id (homeroom) for teachers
     */
    public function index()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $page = max(1, (int)$this->request->query('page', 1));
            $perPage = min(100, max(1, (int)$this->request->query('per-page', 10)));

            // Eager load relations like in parent
            $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);

            // Return filtered results directly to avoid TypeMismatch on $this->model
            $result = $this->model->paginate($page, $perPage, ['teacher_id' => $userId]);
            return ClassroomResource::collection($result);
        }
        return parent::index();
    }


    /**
     * Override all to filter by teacher_id (homeroom) for teachers
     */
    public function all()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);

            // Return filtered results directly to avoid TypeMismatch on $this->model
            $result = $this->model->where(['teacher_id' => $userId]);
            return ClassroomResource::collection($result);
        }
        return parent::all();
    }


    /**
     * Override show to check authorization for homeroom
     */
    public function show()
    {
        $id = $this->request->param('id');
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        $classroom = $this->model->find($id);

        if (!$classroom) {
            throw new \Exception('Classroom not found', 404);
        }

        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            if ($classroom['teacher_id'] != $userId) {
                throw new \Exception('Unauthorized access to homeroom dashboard', 403);
            }
        }

        return parent::show();
    }

    /**
     * Get all classes that the logged in teacher is associated with (as homeroom or teacher)
     */
    public function teacherClasses()
    {
        $user = Auth::user();
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? $user['role_id'] ?? null) : ($user->role ?? $user->role_id ?? null);
        $isAdmin = in_array($role, ['admin', 'superadmin'], true) || (int)$role === 1;

        if ($isAdmin) {
            // Admin can see all classes
            $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);
            $classes = $this->model->findQuery()
                ->where(['status' => 1])
                ->all();
            return ClassroomResource::collection($classes);
        }

        // For regular teachers, find their profile
        $teacher = \App\Models\Teacher::findQuery()->where(['id', '=', $userId])->andWhere(['status', '=', 1])->one();
        $teacherId = $teacher ? $teacher['id'] : $userId;

        // Get classes from teaching schedule
        $classIdsFromSchedule = \App\Models\TeachingSchedule::findQuery()
            ->select('DISTINCT classroom_id')
            ->from('teaching_schedule')
            ->where(['teacher_id' => $teacherId])
            ->all();
        $classIds1 = array_column($classIdsFromSchedule, 'classroom_id');

        // Get classes where homeroom teacher
        $classIdsFromHomeroom = \App\Models\Classroom::findQuery()
            ->select('id')
            ->from('classroom')
            ->where(['teacher_id' => $teacherId])
            ->all();
        $classIds2 = array_column($classIdsFromHomeroom, 'id');

        $allClassIds = array_unique(array_filter(array_merge($classIds1, $classIds2)));

        if (empty($allClassIds)) {
            return ClassroomResource::collection([]);
        }

        $this->model->with(['department', 'teacher', 'semester', 'gradeLevel']);
        $classes = $this->model->findQuery()
            ->where(['id' => $allClassIds])
            ->andWhere(['status', '=', 1])
            ->all();

        // Load relations manually for findQuery results
        $this->model->loadRelations($classes);

        // Fetch student counts for these classes in bulk
        $counts = \App\Models\ClassroomMember::findQuery()
            ->select('class_id, COUNT(*) as total')
            ->where(['class_id' => $allClassIds])
            ->groupBy('class_id')
            ->all();

        $countMap = array_column($counts, 'total', 'class_id');

        foreach ($classes as &$class) {
            $class['students_count'] = (int)($countMap[$class['id']] ?? 0);
        }

        return ClassroomResource::collection($classes);
    }
}
