<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AssignmentClass;
use App\Resources\AssignmentClassResource;

class AssignmentClassController extends Controller
{
    protected AssignmentClass $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AssignmentClass();
    }
    
    /**
     * Get all assignmentclasss with pagination
     * GET /assignmentclasss
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'classroom:id,name']);

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

        return AssignmentClassResource::collection($result);
    }
    
    /**
     * Get all assignmentclasss without pagination
     * GET /assignmentclasss/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'classroom:id,name']);

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
             return AssignmentClassResource::collection($this->model->search($search, $orderBy));
        }
        return AssignmentClassResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single assignmentclass
     * GET /assignmentclasss/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'classroom:id,name']);

        $assignmentclass = $this->model->find($id);
        
        if (!$assignmentclass) {
            throw new \Exception('AssignmentClass not found', 404);
        }
        
        return AssignmentClassResource::make($assignmentclass);
    }
    
    /**
     * Create new assignmentclass
     * POST /assignmentclasss
     */
    public function store()
    {
        $validated = $this->validate([

        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'classroom:id,name']);

            $assignmentclass = $this->model->find($id);
            return $this->created(AssignmentClassResource::make($assignmentclass));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create assignmentclass', $e);
        }
    }
    
    /**
     * Update assignmentclass
     * PUT /assignmentclasss/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $assignmentclass = $this->model->find($id);
        
        if (!$assignmentclass) {
            throw new \Exception('AssignmentClass not found', 404);
        }
        
        $validated = $this->validate([

        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['assignment:id,name', 'classroom:id,name']);

            return AssignmentClassResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assignmentclass', $e);
        }
    }
    
    /**
     * Delete assignmentclass
     * DELETE /assignmentclasss/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $assignmentclass = $this->model->find($id);
        
        if (!$assignmentclass) {
            throw new \Exception('AssignmentClass not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete assignmentclass', $e);
        }
    }
}