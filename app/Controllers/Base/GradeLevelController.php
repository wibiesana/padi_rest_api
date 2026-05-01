<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\GradeLevel;
use App\Resources\GradeLevelResource;

class GradeLevelController extends Controller
{
    protected GradeLevel $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new GradeLevel();
    }
    
    /**
     * Get all gradelevels with pagination
     * GET /gradelevels
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

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

        return GradeLevelResource::collection($result);
    }
    
    /**
     * Get all gradelevels without pagination
     * GET /gradelevels/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

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
             return GradeLevelResource::collection($this->model->search($search, $orderBy));
        }
        return GradeLevelResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single gradelevel
     * GET /gradelevels/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $gradelevel = $this->model->find($id);
        
        if (!$gradelevel) {
            throw new \Exception('GradeLevel not found', 404);
        }
        
        return GradeLevelResource::make($gradelevel);
    }
    
    /**
     * Create new gradelevel
     * POST /gradelevels
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:20',
            'full_name' => 'string|max:50',
            'level_type' => 'required|string|max:20',
            'sequence' => 'required|integer',
            'description' => 'string|max:255',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $gradelevel = $this->model->find($id);
            return $this->created(GradeLevelResource::make($gradelevel));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create gradelevel', $e);
        }
    }
    
    /**
     * Update gradelevel
     * PUT /gradelevels/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $gradelevel = $this->model->find($id);
        
        if (!$gradelevel) {
            throw new \Exception('GradeLevel not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:20',
            'full_name' => 'string|max:50',
            'level_type' => 'required|string|max:20',
            'sequence' => 'required|integer',
            'description' => 'string|max:255',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return GradeLevelResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update gradelevel', $e);
        }
    }
    
    /**
     * Delete gradelevel
     * DELETE /gradelevels/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $gradelevel = $this->model->find($id);
        
        if (!$gradelevel) {
            throw new \Exception('GradeLevel not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete gradelevel', $e);
        }
    }
}