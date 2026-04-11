<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\UserParentStudent;
use App\Resources\UserParentStudentResource;

class UserParentStudentController extends Controller
{
    protected UserParentStudent $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new UserParentStudent();
    }
    
    /**
     * Get all userparentstudents with pagination
     * GET /userparentstudents
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['parentUser:id,username', 'studentUser:id,username']);

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

        return UserParentStudentResource::collection($result);
    }
    
    /**
     * Get all userparentstudents without pagination
     * GET /userparentstudents/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['parentUser:id,username', 'studentUser:id,username']);

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
             return UserParentStudentResource::collection($this->model->search($search, $orderBy));
        }
        return UserParentStudentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single userparentstudent
     * GET /userparentstudents/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['parentUser:id,username', 'studentUser:id,username']);

        $userparentstudent = $this->model->find($id);
        
        if (!$userparentstudent) {
            throw new \Exception('UserParentStudent not found', 404);
        }
        
        return UserParentStudentResource::make($userparentstudent);
    }
    
    /**
     * Create new userparentstudent
     * POST /userparentstudents
     */
    public function store()
    {
        $validated = $this->validate([
            'relation_type' => 'string|max:50',
            'is_primary' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['parentUser:id,username', 'studentUser:id,username']);

            $userparentstudent = $this->model->find($id);
            return $this->created(UserParentStudentResource::make($userparentstudent));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create userparentstudent', $e);
        }
    }
    
    /**
     * Update userparentstudent
     * PUT /userparentstudents/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $userparentstudent = $this->model->find($id);
        
        if (!$userparentstudent) {
            throw new \Exception('UserParentStudent not found', 404);
        }
        
        $validated = $this->validate([
            'relation_type' => 'string|max:50',
            'is_primary' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['parentUser:id,username', 'studentUser:id,username']);

            return UserParentStudentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update userparentstudent', $e);
        }
    }
    
    /**
     * Delete userparentstudent
     * DELETE /userparentstudents/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $userparentstudent = $this->model->find($id);
        
        if (!$userparentstudent) {
            throw new \Exception('UserParentStudent not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete userparentstudent', $e);
        }
    }
}