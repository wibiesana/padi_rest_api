<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Classroom;
use App\Resources\ClassroomResource;

class ClassroomController extends Controller
{
    protected Classroom $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Classroom();
    }
    
    /**
     * Get all classrooms with pagination
     * GET /classrooms
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['department:id,name', 'teacher:id,name', 'semester:id,name', 'gradeLevel:id,name']);

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

        return ClassroomResource::collection($result);
    }
    
    /**
     * Get all classrooms without pagination
     * GET /classrooms/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['department:id,name', 'teacher:id,name', 'semester:id,name', 'gradeLevel:id,name']);

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
             return ClassroomResource::collection($this->model->search($search, $orderBy));
        }
        return ClassroomResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single classroom
     * GET /classrooms/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['department:id,name', 'teacher:id,name', 'semester:id,name', 'gradeLevel:id,name']);

        $classroom = $this->model->find($id);
        
        if (!$classroom) {
            throw new \Exception('Classroom not found', 404);
        }
        
        return ClassroomResource::make($classroom);
    }
    
    /**
     * Create new classroom
     * POST /classrooms
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'short_name' => 'string|max:50',
            'level' => 'integer',
            'teacher_id' => 'integer',
            'semester_id' => 'integer',
            'department_id' => 'integer',
            'grade_level_id' => 'integer',
            'status' => 'integer',
            'asc_id' => 'string|max:50',
            'asc_partner_id' => 'string|max:50',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['department:id,name', 'teacher:id,name', 'semester:id,name', 'gradeLevel:id,name']);

            $classroom = $this->model->find($id);
            return $this->created(ClassroomResource::make($classroom));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create classroom', $e);
        }
    }
    
    /**
     * Update classroom
     * PUT /classrooms/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $classroom = $this->model->find($id);
        
        if (!$classroom) {
            throw new \Exception('Classroom not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'short_name' => 'string|max:50',
            'level' => 'integer',
            'teacher_id' => 'integer',
            'semester_id' => 'integer',
            'department_id' => 'integer',
            'grade_level_id' => 'integer',
            'status' => 'integer',
            'asc_id' => 'string|max:50',
            'asc_partner_id' => 'string|max:50',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['department:id,name', 'teacher:id,name', 'semester:id,name', 'gradeLevel:id,name']);

            return ClassroomResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update classroom', $e);
        }
    }
    
    /**
     * Delete classroom
     * DELETE /classrooms/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $classroom = $this->model->find($id);
        
        if (!$classroom) {
            throw new \Exception('Classroom not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete classroom', $e);
        }
    }
}