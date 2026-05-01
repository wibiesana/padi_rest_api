<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AttendanceStudent;
use App\Resources\AttendanceStudentResource;

class AttendanceStudentController extends Controller
{
    protected AttendanceStudent $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AttendanceStudent();
    }
    
    /**
     * Get all attendancestudents with pagination
     * GET /attendancestudents
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,name']);

        $page = max(1, (int)$this->request->query('page', 1)); // Min page 1
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 25))); // Max 100 per page
        $search = $this->request->query('search');
        
        // Handle Sorting
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = null;

        if ($sortBy) {
            $direction = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
            $orderBy = "{$sortBy} {$direction}";
        }

        if ($search) {
            // Limit search query length to prevent abuse
            $search = substr($search, 0, 255);
            $result = $this->model->searchPaginate($search, $page, $perPage, $orderBy);
        } else {
            $result = $this->model->paginate($page, $perPage, [], $orderBy);
        }

        return AttendanceStudentResource::collection($result);
    }
    
    /**
     * Get all attendancestudents without pagination
     * GET /attendancestudents/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,name']);

        $search = $this->request->query('search');

        // Handle Sorting
        $sortBy = $this->request->query('sort_by');
        $order = $this->request->query('order', 'asc');
        $orderBy = null;

        if ($sortBy) {
            $direction = strtolower($order) === 'desc' ? 'DESC' : 'ASC';
            $orderBy = "{$sortBy} {$direction}";
        }

        if ($search) {
             return AttendanceStudentResource::collection($this->model->search($search, $orderBy));
        }
        return AttendanceStudentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single attendancestudent
     * GET /attendancestudents/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,name']);

        $attendancestudent = $this->model->find($id);
        
        if (!$attendancestudent) {
            throw new \Exception('AttendanceStudent not found', 404);
        }
        
        return AttendanceStudentResource::make($attendancestudent);
    }
    
    /**
     * Create new attendancestudent
     * POST /attendancestudents
     */
    public function store()
    {
        $validated = $this->validate([
            'lesson_session_id' => 'required|integer',
            'student_id' => 'required|integer',
            'status' => 'integer',
            'note' => 'string|max:255',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,name']);

            $attendancestudent = $this->model->find($id);
            return $this->created(AttendanceStudentResource::make($attendancestudent));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create attendancestudent', $e);
        }
    }
    
    /**
     * Update attendancestudent
     * PUT /attendancestudents/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $attendancestudent = $this->model->find($id);
        
        if (!$attendancestudent) {
            throw new \Exception('AttendanceStudent not found', 404);
        }
        
        $validated = $this->validate([
            'lesson_session_id' => 'required|integer',
            'student_id' => 'required|integer',
            'status' => 'integer',
            'note' => 'string|max:255',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,name']);

            return AttendanceStudentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update attendancestudent', $e);
        }
    }
    
    /**
     * Delete attendancestudent
     * DELETE /attendancestudents/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $attendancestudent = $this->model->find($id);
        
        if (!$attendancestudent) {
            throw new \Exception('AttendanceStudent not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete attendancestudent', $e);
        }
    }
}