<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AscMapping;
use App\Resources\AscMappingResource;

class AscMappingController extends Controller
{
    protected AscMapping $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AscMapping();
    }
    
    /**
     * Get all ascmappings with pagination
     * GET /ascmappings
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

        return AscMappingResource::collection($result);
    }
    
    /**
     * Get all ascmappings without pagination
     * GET /ascmappings/all
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
             return AscMappingResource::collection($this->model->search($search, $orderBy));
        }
        return AscMappingResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single ascmapping
     * GET /ascmappings/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $ascmapping = $this->model->find($id);
        
        if (!$ascmapping) {
            throw new \Exception('AscMapping not found', 404);
        }
        
        return AscMappingResource::make($ascmapping);
    }
    
    /**
     * Create new ascmapping
     * POST /ascmappings
     */
    public function store()
    {
        $validated = $this->validate([
            'entity_type' => 'required|string|max:50',
            'asc_id' => 'required|string|max:100',
            'asc_name' => 'string|max:255',
            'asc_short' => 'string|max:100',
            'local_id' => 'required|integer',
            'local_table' => 'required|string|max:50',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $ascmapping = $this->model->find($id);
            return $this->created(AscMappingResource::make($ascmapping));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create ascmapping', $e);
        }
    }
    
    /**
     * Update ascmapping
     * PUT /ascmappings/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $ascmapping = $this->model->find($id);
        
        if (!$ascmapping) {
            throw new \Exception('AscMapping not found', 404);
        }
        
        $validated = $this->validate([
            'entity_type' => 'required|string|max:50',
            'asc_id' => 'required|string|max:100',
            'asc_name' => 'string|max:255',
            'asc_short' => 'string|max:100',
            'local_id' => 'required|integer',
            'local_table' => 'required|string|max:50',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return AscMappingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update ascmapping', $e);
        }
    }
    
    /**
     * Delete ascmapping
     * DELETE /ascmappings/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $ascmapping = $this->model->find($id);
        
        if (!$ascmapping) {
            throw new \Exception('AscMapping not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete ascmapping', $e);
        }
    }
}