<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AssignmentResult;
use App\Resources\AssignmentResultResource;

class AssignmentResultController extends Controller
{
    protected AssignmentResult $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AssignmentResult();
    }
    
    /**
     * Get all assignmentresults with pagination
     * GET /assignmentresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'createdBy:id,username', 'updatedBy:id,username']);

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

        return AssignmentResultResource::collection($result);
    }
    
    /**
     * Get all assignmentresults without pagination
     * GET /assignmentresults/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'createdBy:id,username', 'updatedBy:id,username']);

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
             return AssignmentResultResource::collection($this->model->search($search, $orderBy));
        }
        return AssignmentResultResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single assignmentresult
     * GET /assignmentresults/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'createdBy:id,username', 'updatedBy:id,username']);

        $assignmentresult = $this->model->find($id);
        
        if (!$assignmentresult) {
            throw new \Exception('AssignmentResult not found', 404);
        }
        
        return AssignmentResultResource::make($assignmentresult);
    }
    
    /**
     * Create new assignmentresult
     * POST /assignmentresults
     */
    public function store()
    {
        $validated = $this->validate([
            'assignment_id' => 'required|integer',
            'description' => 'string',
            'upload_file' => 'string|max:255',
            'score' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'createdBy:id,username', 'updatedBy:id,username']);

            $assignmentresult = $this->model->find($id);
            return $this->created(AssignmentResultResource::make($assignmentresult));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create assignmentresult', $e);
        }
    }
    
    /**
     * Update assignmentresult
     * PUT /assignmentresults/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $assignmentresult = $this->model->find($id);
        
        if (!$assignmentresult) {
            throw new \Exception('AssignmentResult not found', 404);
        }
        
        $validated = $this->validate([
            'assignment_id' => 'required|integer',
            'description' => 'string',
            'upload_file' => 'string|max:255',
            'score' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'createdBy:id,username', 'updatedBy:id,username']);

            return AssignmentResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assignmentresult', $e);
        }
    }
    
    /**
     * Delete assignmentresult
     * DELETE /assignmentresults/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $assignmentresult = $this->model->find($id);
        
        if (!$assignmentresult) {
            throw new \Exception('AssignmentResult not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete assignmentresult', $e);
        }
    }
}