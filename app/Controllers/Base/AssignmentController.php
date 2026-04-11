<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Assignment;
use App\Resources\AssignmentResource;

class AssignmentController extends Controller
{
    protected Assignment $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Assignment();
    }
    
    /**
     * Get all assignments with pagination
     * GET /assignments
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name']);

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

        return AssignmentResource::collection($result);
    }
    
    /**
     * Get all assignments without pagination
     * GET /assignments/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name']);

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
             return AssignmentResource::collection($this->model->search($search, $orderBy));
        }
        return AssignmentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single assignment
     * GET /assignments/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name']);

        $assignment = $this->model->find($id);
        
        if (!$assignment) {
            throw new \Exception('Assignment not found', 404);
        }
        
        return AssignmentResource::make($assignment);
    }
    
    /**
     * Create new assignment
     * POST /assignments
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'string',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'integer',
            'semester_id' => 'integer',
            'subject_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name']);

            $assignment = $this->model->find($id);
            return $this->created(AssignmentResource::make($assignment));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create assignment', $e);
        }
    }
    
    /**
     * Update assignment
     * PUT /assignments/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
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
            'subject_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username', 'semester:id,name']);

            return AssignmentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assignment', $e);
        }
    }
    
    /**
     * Delete assignment
     * DELETE /assignments/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $assignment = $this->model->find($id);
        
        if (!$assignment) {
            throw new \Exception('Assignment not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete assignment', $e);
        }
    }
}