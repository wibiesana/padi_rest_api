<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Department;
use App\Resources\DepartmentResource;

class DepartmentController extends Controller
{
    protected Department $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Department();
    }
    
    /**
     * Get all departments with pagination
     * GET /departments
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'teacher:id,name', 'updatedBy:id,username']);

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

        return DepartmentResource::collection($result);
    }
    
    /**
     * Get all departments without pagination
     * GET /departments/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'teacher:id,name', 'updatedBy:id,username']);

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
             return DepartmentResource::collection($this->model->search($search, $orderBy));
        }
        return DepartmentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single department
     * GET /departments/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'teacher:id,name', 'updatedBy:id,username']);

        $department = $this->model->find($id);
        
        if (!$department) {
            throw new \Exception('Department not found', 404);
        }
        
        return DepartmentResource::make($department);
    }
    
    /**
     * Create new department
     * POST /departments
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'description' => 'string|max:255',
            'teacher_id' => 'integer',
            'semester_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'status' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'teacher:id,name', 'updatedBy:id,username']);

            $department = $this->model->find($id);
            return $this->created(DepartmentResource::make($department));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create department', $e);
        }
    }
    
    /**
     * Update department
     * PUT /departments/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $department = $this->model->find($id);
        
        if (!$department) {
            throw new \Exception('Department not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'description' => 'string|max:255',
            'teacher_id' => 'integer',
            'semester_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'status' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'teacher:id,name', 'updatedBy:id,username']);

            return DepartmentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update department', $e);
        }
    }
    
    /**
     * Delete department
     * DELETE /departments/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $department = $this->model->find($id);
        
        if (!$department) {
            throw new \Exception('Department not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete department', $e);
        }
    }
}