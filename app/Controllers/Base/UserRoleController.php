<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\UserRole;
use App\Resources\UserRoleResource;

class UserRoleController extends Controller
{
    protected UserRole $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new UserRole();
    }
    
    /**
     * Get all userroles with pagination
     * GET /userroles
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['role:id,name', 'user:id,username']);

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

        return UserRoleResource::collection($result);
    }
    
    /**
     * Get all userroles without pagination
     * GET /userroles/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['role:id,name', 'user:id,username']);

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
             return UserRoleResource::collection($this->model->search($search, $orderBy));
        }
        return UserRoleResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single userrole
     * GET /userroles/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['role:id,name', 'user:id,username']);

        $userrole = $this->model->find($id);
        
        if (!$userrole) {
            throw new \Exception('UserRole not found', 404);
        }
        
        return UserRoleResource::make($userrole);
    }
    
    /**
     * Create new userrole
     * POST /userroles
     */
    public function store()
    {
        $validated = $this->validate([
            'status' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['role:id,name', 'user:id,username']);

            $userrole = $this->model->find($id);
            return $this->created(UserRoleResource::make($userrole));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create userrole', $e);
        }
    }
    
    /**
     * Update userrole
     * PUT /userroles/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $userrole = $this->model->find($id);
        
        if (!$userrole) {
            throw new \Exception('UserRole not found', 404);
        }
        
        $validated = $this->validate([
            'status' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['role:id,name', 'user:id,username']);

            return UserRoleResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update userrole', $e);
        }
    }
    
    /**
     * Delete userrole
     * DELETE /userroles/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $userrole = $this->model->find($id);
        
        if (!$userrole) {
            throw new \Exception('UserRole not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete userrole', $e);
        }
    }
}