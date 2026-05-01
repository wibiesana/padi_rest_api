<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\StatusType;
use App\Resources\StatusTypeResource;

class StatusTypeController extends Controller
{
    protected StatusType $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new StatusType();
    }
    
    /**
     * Get all statustypes with pagination
     * GET /statustypes
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

        return StatusTypeResource::collection($result);
    }
    
    /**
     * Get all statustypes without pagination
     * GET /statustypes/all
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
             return StatusTypeResource::collection($this->model->search($search, $orderBy));
        }
        return StatusTypeResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single statustype
     * GET /statustypes/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $statustype = $this->model->find($id);
        
        if (!$statustype) {
            throw new \Exception('StatusType not found', 404);
        }
        
        return StatusTypeResource::make($statustype);
    }
    
    /**
     * Create new statustype
     * POST /statustypes
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50|unique:status_type,name',
            'description' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $statustype = $this->model->find($id);
            return $this->created(StatusTypeResource::make($statustype));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create statustype', $e);
        }
    }
    
    /**
     * Update statustype
     * PUT /statustypes/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $statustype = $this->model->find($id);
        
        if (!$statustype) {
            throw new \Exception('StatusType not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50|unique:status_type,name,' . $id,
            'description' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return StatusTypeResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update statustype', $e);
        }
    }
    
    /**
     * Delete statustype
     * DELETE /statustypes/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $statustype = $this->model->find($id);
        
        if (!$statustype) {
            throw new \Exception('StatusType not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete statustype', $e);
        }
    }
}