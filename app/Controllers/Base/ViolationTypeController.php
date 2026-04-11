<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ViolationType;
use App\Resources\ViolationTypeResource;

class ViolationTypeController extends Controller
{
    protected ViolationType $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ViolationType();
    }
    
    /**
     * Get all violationtypes with pagination
     * GET /violationtypes
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username']);

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

        return ViolationTypeResource::collection($result);
    }
    
    /**
     * Get all violationtypes without pagination
     * GET /violationtypes/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username']);

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
             return ViolationTypeResource::collection($this->model->search($search, $orderBy));
        }
        return ViolationTypeResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single violationtype
     * GET /violationtypes/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username']);

        $violationtype = $this->model->find($id);
        
        if (!$violationtype) {
            throw new \Exception('ViolationType not found', 404);
        }
        
        return ViolationTypeResource::make($violationtype);
    }
    
    /**
     * Create new violationtype
     * POST /violationtypes
     */
    public function store()
    {
        $validated = $this->validate([
            'code' => 'required|string|max:20|unique:violation_type,code',
            'name' => 'required|string|max:100',
            'score_penalty' => 'required|integer',
            'status' => 'integer',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username']);

            $violationtype = $this->model->find($id);
            return $this->created(ViolationTypeResource::make($violationtype));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create violationtype', $e);
        }
    }
    
    /**
     * Update violationtype
     * PUT /violationtypes/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $violationtype = $this->model->find($id);
        
        if (!$violationtype) {
            throw new \Exception('ViolationType not found', 404);
        }
        
        $validated = $this->validate([
            'code' => 'required|string|max:20|unique:violation_type,code,' . $id,
            'name' => 'required|string|max:100',
            'score_penalty' => 'required|integer',
            'status' => 'integer',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username']);

            return ViolationTypeResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update violationtype', $e);
        }
    }
    
    /**
     * Delete violationtype
     * DELETE /violationtypes/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $violationtype = $this->model->find($id);
        
        if (!$violationtype) {
            throw new \Exception('ViolationType not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete violationtype', $e);
        }
    }
}