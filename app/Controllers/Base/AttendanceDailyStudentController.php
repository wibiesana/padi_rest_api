<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AttendanceDailyStudent;
use App\Resources\AttendanceDailyStudentResource;

class AttendanceDailyStudentController extends Controller
{
    protected AttendanceDailyStudent $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AttendanceDailyStudent();
    }
    
    /**
     * Get all attendancedailystudents with pagination
     * GET /attendancedailystudents
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['student:id,name']);

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

        return AttendanceDailyStudentResource::collection($result);
    }
    
    /**
     * Get all attendancedailystudents without pagination
     * GET /attendancedailystudents/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['student:id,name']);

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
             return AttendanceDailyStudentResource::collection($this->model->search($search, $orderBy));
        }
        return AttendanceDailyStudentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single attendancedailystudent
     * GET /attendancedailystudents/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['student:id,name']);

        $attendancedailystudent = $this->model->find($id);
        
        if (!$attendancedailystudent) {
            throw new \Exception('AttendanceDailyStudent not found', 404);
        }
        
        return AttendanceDailyStudentResource::make($attendancedailystudent);
    }
    
    /**
     * Create new attendancedailystudent
     * POST /attendancedailystudents
     */
    public function store()
    {
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'attendance_date' => 'required',
            'status' => 'required|integer',
            'note' => 'string|max:255',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['student:id,name']);

            $attendancedailystudent = $this->model->find($id);
            return $this->created(AttendanceDailyStudentResource::make($attendancedailystudent));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create attendancedailystudent', $e);
        }
    }
    
    /**
     * Update attendancedailystudent
     * PUT /attendancedailystudents/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $attendancedailystudent = $this->model->find($id);
        
        if (!$attendancedailystudent) {
            throw new \Exception('AttendanceDailyStudent not found', 404);
        }
        
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'attendance_date' => 'required',
            'status' => 'required|integer',
            'note' => 'string|max:255',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['student:id,name']);

            return AttendanceDailyStudentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update attendancedailystudent', $e);
        }
    }
    
    /**
     * Delete attendancedailystudent
     * DELETE /attendancedailystudents/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $attendancedailystudent = $this->model->find($id);
        
        if (!$attendancedailystudent) {
            throw new \Exception('AttendanceDailyStudent not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete attendancedailystudent', $e);
        }
    }
}