<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AttendanceDailyTeacher;
use App\Resources\AttendanceDailyTeacherResource;

class AttendanceDailyTeacherController extends Controller
{
    protected AttendanceDailyTeacher $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AttendanceDailyTeacher();
    }
    
    /**
     * Get all attendancedailyteachers with pagination
     * GET /attendancedailyteachers
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name']);

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

        return AttendanceDailyTeacherResource::collection($result);
    }
    
    /**
     * Get all attendancedailyteachers without pagination
     * GET /attendancedailyteachers/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name']);

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
             return AttendanceDailyTeacherResource::collection($this->model->search($search, $orderBy));
        }
        return AttendanceDailyTeacherResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single attendancedailyteacher
     * GET /attendancedailyteachers/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name']);

        $attendancedailyteacher = $this->model->find($id);
        
        if (!$attendancedailyteacher) {
            throw new \Exception('AttendanceDailyTeacher not found', 404);
        }
        
        return AttendanceDailyTeacherResource::make($attendancedailyteacher);
    }
    
    /**
     * Create new attendancedailyteacher
     * POST /attendancedailyteachers
     */
    public function store()
    {
        $validated = $this->validate([
            'teacher_id' => 'required|integer',
            'attendance_date' => 'required',
            'status' => 'integer',
            'note' => 'string|max:255',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name']);

            $attendancedailyteacher = $this->model->find($id);
            return $this->created(AttendanceDailyTeacherResource::make($attendancedailyteacher));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create attendancedailyteacher', $e);
        }
    }
    
    /**
     * Update attendancedailyteacher
     * PUT /attendancedailyteachers/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $attendancedailyteacher = $this->model->find($id);
        
        if (!$attendancedailyteacher) {
            throw new \Exception('AttendanceDailyTeacher not found', 404);
        }
        
        $validated = $this->validate([
            'teacher_id' => 'required|integer',
            'attendance_date' => 'required',
            'status' => 'integer',
            'note' => 'string|max:255',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name']);

            return AttendanceDailyTeacherResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update attendancedailyteacher', $e);
        }
    }
    
    /**
     * Delete attendancedailyteacher
     * DELETE /attendancedailyteachers/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $attendancedailyteacher = $this->model->find($id);
        
        if (!$attendancedailyteacher) {
            throw new \Exception('AttendanceDailyTeacher not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete attendancedailyteacher', $e);
        }
    }
}