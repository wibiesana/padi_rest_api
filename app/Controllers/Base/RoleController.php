<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Role;
use App\Resources\RoleResource;

class RoleController extends Controller
{
    protected Role $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Role();
    }
    
    /**
     * Get all roles with pagination
     * GET /roles
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

        return RoleResource::collection($result);
    }
    
    /**
     * Get all roles without pagination
     * GET /roles/all
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
             return RoleResource::collection($this->model->search($search, $orderBy));
        }
        return RoleResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single role
     * GET /roles/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $role = $this->model->find($id);
        
        if (!$role) {
            throw new \Exception('Role not found', 404);
        }
        
        return RoleResource::make($role);
    }
    
    /**
     * Create new role
     * POST /roles
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50|unique:role,name',
            'description' => 'string|max:255',
            'permissions' => 'string',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $role = $this->model->find($id);
            return $this->created(RoleResource::make($role));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create role', $e);
        }
    }
    
    /**
     * Update role
     * PUT /roles/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $role = $this->model->find($id);
        
        if (!$role) {
            throw new \Exception('Role not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50|unique:role,name,' . $id,
            'description' => 'string|max:255',
            'permissions' => 'string',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return RoleResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update role', $e);
        }
    }
    
    /**
     * Delete role
     * DELETE /roles/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $role = $this->model->find($id);
        
        if (!$role) {
            throw new \Exception('Role not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete role', $e);
        }
    }
}