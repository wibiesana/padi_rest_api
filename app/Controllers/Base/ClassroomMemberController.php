<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ClassroomMember;
use App\Resources\ClassroomMemberResource;

class ClassroomMemberController extends Controller
{
    protected ClassroomMember $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ClassroomMember();
    }
    
    /**
     * Get all classroommembers with pagination
     * GET /classroommembers
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'student:id,name']);

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

        return ClassroomMemberResource::collection($result);
    }
    
    /**
     * Get all classroommembers without pagination
     * GET /classroommembers/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'student:id,name']);

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
             return ClassroomMemberResource::collection($this->model->search($search, $orderBy));
        }
        return ClassroomMemberResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single classroommember
     * GET /classroommembers/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'student:id,name']);

        $classroommember = $this->model->find($id);
        
        if (!$classroommember) {
            throw new \Exception('ClassroomMember not found', 404);
        }
        
        return ClassroomMemberResource::make($classroommember);
    }
    
    /**
     * Create new classroommember
     * POST /classroommembers
     */
    public function store()
    {
        $validated = $this->validate([

        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'student:id,name']);

            $classroommember = $this->model->find($id);
            return $this->created(ClassroomMemberResource::make($classroommember));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create classroommember', $e);
        }
    }
    
    /**
     * Update classroommember
     * PUT /classroommembers/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $classroommember = $this->model->find($id);
        
        if (!$classroommember) {
            throw new \Exception('ClassroomMember not found', 404);
        }
        
        $validated = $this->validate([

        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'student:id,name']);

            return ClassroomMemberResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update classroommember', $e);
        }
    }
    
    /**
     * Delete classroommember
     * DELETE /classroommembers/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $classroommember = $this->model->find($id);
        
        if (!$classroommember) {
            throw new \Exception('ClassroomMember not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete classroommember', $e);
        }
    }
}