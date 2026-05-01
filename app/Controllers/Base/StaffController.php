<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Staff;
use App\Resources\StaffResource;

class StaffController extends Controller
{
    protected Staff $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Staff();
    }
    
    /**
     * Get all staffs with pagination
     * GET /staffs
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['id:id,username', 'createdBy:id,username', 'updatedBy:id,username']);

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

        return StaffResource::collection($result);
    }
    
    /**
     * Get all staffs without pagination
     * GET /staffs/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['id:id,username', 'createdBy:id,username', 'updatedBy:id,username']);

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
             return StaffResource::collection($this->model->search($search, $orderBy));
        }
        return StaffResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single staff
     * GET /staffs/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['id:id,username', 'createdBy:id,username', 'updatedBy:id,username']);

        $staff = $this->model->find($id);
        
        if (!$staff) {
            throw new \Exception('Staff not found', 404);
        }
        
        return StaffResource::make($staff);
    }
    
    /**
     * Create new staff
     * POST /staffs
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'string|max:100',
            'nuptk' => 'string|max:50',
            'nip' => 'string|max:50',
            'nik' => 'string|max:50',
            'gender' => 'string',
            'place_of_birth' => 'string|max:50',
            'job_status' => 'string|max:15',
            'religion' => 'string|max:50',
            'address' => 'string|max:300',
            'phone' => 'string|max:20',
            'email' => 'required|string|max:100|email',
            'photo' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['id:id,username', 'createdBy:id,username', 'updatedBy:id,username']);

            $staff = $this->model->find($id);
            return $this->created(StaffResource::make($staff));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create staff', $e);
        }
    }
    
    /**
     * Update staff
     * PUT /staffs/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $staff = $this->model->find($id);
        
        if (!$staff) {
            throw new \Exception('Staff not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'string|max:100',
            'nuptk' => 'string|max:50',
            'nip' => 'string|max:50',
            'nik' => 'string|max:50',
            'gender' => 'string',
            'place_of_birth' => 'string|max:50',
            'job_status' => 'string|max:15',
            'religion' => 'string|max:50',
            'address' => 'string|max:300',
            'phone' => 'string|max:20',
            'email' => 'required|string|max:100|email',
            'photo' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['id:id,username', 'createdBy:id,username', 'updatedBy:id,username']);

            return StaffResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update staff', $e);
        }
    }
    
    /**
     * Delete staff
     * DELETE /staffs/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $staff = $this->model->find($id);
        
        if (!$staff) {
            throw new \Exception('Staff not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete staff', $e);
        }
    }
}