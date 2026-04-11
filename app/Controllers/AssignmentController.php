<?php

namespace App\Controllers;

use App\Controllers\Base\AssignmentController as BaseController;
use Wibiesana\Padi\Core\Auth;

class AssignmentController extends BaseController
{
    /**
     * Override index to filter by created_by and semester
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user ? (is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null)) : null;
        $role = $user ? (is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null)) : null;

        $conditions = [];
        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $conditions['created_by'] = $userId;
        }
        
        $semesterId = $this->request->query('semester_id');
        if ($semesterId) {
            $conditions['semester_id'] = $semesterId;
        }

        // Auto-generated eager loading
        $this->model->with(['createdBy.teacher', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name', 'my_class']);

        $page = max(1, (int)$this->request->query('page', 1));
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25)));
        $search = $this->request->query('search');
        
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : null;

        if ($search) {
            $result = $this->model->searchPaginate($search, $page, $perPage, $orderBy);
        } else {
            $result = $this->model->paginate($page, $perPage, $conditions, $orderBy);
        }

        return \App\Resources\AssignmentResource::collection($result);
    }

    /**
     * Override all to filter by created_by and semester
     */
    public function all()
    {
        $user = Auth::user();
        $userId = $user ? (is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null)) : null;
        $role = $user ? (is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null)) : null;

        $conditions = [];
        if ($user && ($role === 'teacher' || (int)$role === 2)) {
            $conditions['created_by'] = $userId;
        }

        $semesterId = $this->request->query('semester_id');
        if ($semesterId) {
            $conditions['semester_id'] = $semesterId;
        }

        // Auto-generated eager loading
        $this->model->with(['createdBy.teacher', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name', 'my_class']);

        $search = $this->request->query('search');
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = $sortBy ? "{$sortBy} " . (strtolower($order) === 'desc' ? 'DESC' : 'ASC') : null;

        if ($search) {
             return \App\Resources\AssignmentResource::collection($this->model->search($search, $orderBy));
        }
        return \App\Resources\AssignmentResource::collection($this->model->all($conditions, $orderBy));
    }

    /**
     * Override store to handle class_ids
     */
    public function store()
    {
        $user = Auth::user();
        $userId = $user ? (is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null)) : null;

        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'string',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'integer',
            'semester_id' => 'integer',
            'subject_id' => 'integer'
        ]);
        
        $validated['created_by'] = $userId;
        $validated['updated_by'] = $userId;

        $classIds = $this->request->input('class_ids', []);
        
        try {
            $id = $this->model->create($validated);
            
            if (!empty($classIds) && is_array($classIds)) {
                $assignmentClassModel = new \App\Models\AssignmentClass();
                $pivotData = array_map(fn($classId) => [
                    'assignment_id' => $id,
                    'classroom_id' => (int)$classId
                ], $classIds);
                $assignmentClassModel->batchInsert($pivotData);
            }
            
            $this->model->with(['createdBy.teacher', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name', 'my_class']);
            $assignment = $this->model->find($id);
            return $this->created(\App\Resources\AssignmentResource::make($assignment));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create assignment', $e);
        }
    }

    /**
     * Override update to handle class_ids
     */
    public function update()
    {
        $id = $this->request->param('id');
        $user = Auth::user();
        $userId = $user ? (is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null)) : null;

        $assignment = $this->model->find($id);
        if (!$assignment) {
            throw new \Exception('Assignment not found', 404);
        }

        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'string',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'integer',
            'semester_id' => 'integer',
            'subject_id' => 'integer'
        ]);
        
        $validated['updated_by'] = $userId;
        $classIds = $this->request->input('class_ids');
        
        try {
            $this->model->update($id, $validated);
            
            if ($classIds !== null && is_array($classIds)) {
                $assignmentClassModel = new \App\Models\AssignmentClass();
                $assignmentClassModel->findQuery()->where(['assignment_id' => $id])->delete();
                
                $pivotData = array_map(fn($classId) => [
                    'assignment_id' => $id,
                    'classroom_id' => (int)$classId
                ], $classIds);
                $assignmentClassModel->batchInsert($pivotData);
            }
            
            $this->model->with(['createdBy.teacher', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name', 'my_class']);
            return \App\Resources\AssignmentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assignment', $e);
        }
    }

    /**
     * Show assignment detail
     */
    public function show()
    {
        $id = $this->request->param('id');
        $this->model->with(['createdBy.teacher', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name', 'my_class']);
        $assignment = $this->model->find($id);
        if (!$assignment) {
            throw new \Exception('Assignment not found', 404);
        }
        return \App\Resources\AssignmentResource::make($assignment);
    }
}
