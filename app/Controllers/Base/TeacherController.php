<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Teacher;
use App\Resources\TeacherResource;

class TeacherController extends Controller
{
    protected Teacher $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Teacher();
    }
    
    /**
     * Get all teachers with pagination
     * GET /teachers
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

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

        return TeacherResource::collection($result);
    }
    
    /**
     * Get all teachers without pagination
     * GET /teachers/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

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
             return TeacherResource::collection($this->model->search($search, $orderBy));
        }
        return TeacherResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single teacher
     * GET /teachers/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

        $teacher = $this->model->find($id);
        
        if (!$teacher) {
            throw new \Exception('Teacher not found', 404);
        }
        
        return TeacherResource::make($teacher);
    }
    
    /**
     * Create new teacher
     * POST /teachers
     */
    public function store()
    {
        $validated = $this->validate([
            'data_sekolah_id' => 'integer',
            'name' => 'string|max:100',
            'short_name' => 'string|max:50',
            'nuptk' => 'string|max:50',
            'nip' => 'string|max:50',
            'nik' => 'string|max:50',
            'gender' => 'string',
            'place_of_birth' => 'string|max:50',
            'job_status' => 'string|max:20',
            'religion' => 'string|max:50',
            'address' => 'string|max:300',
            'no_hp' => 'string|max:20',
            'email' => 'required|string|max:100|email',
            'photo' => 'string|max:255',
            'status' => 'integer',
            'asc_id' => 'string|max:50',
            'asc_partner_id' => 'string|max:50',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

            $teacher = $this->model->find($id);
            return $this->created(TeacherResource::make($teacher));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create teacher', $e);
        }
    }
    
    /**
     * Update teacher
     * PUT /teachers/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $teacher = $this->model->find($id);
        
        if (!$teacher) {
            throw new \Exception('Teacher not found', 404);
        }
        
        $validated = $this->validate([
            'data_sekolah_id' => 'integer',
            'name' => 'string|max:100',
            'short_name' => 'string|max:50',
            'nuptk' => 'string|max:50',
            'nip' => 'string|max:50',
            'nik' => 'string|max:50',
            'gender' => 'string',
            'place_of_birth' => 'string|max:50',
            'job_status' => 'string|max:20',
            'religion' => 'string|max:50',
            'address' => 'string|max:300',
            'no_hp' => 'string|max:20',
            'email' => 'required|string|max:100|email',
            'photo' => 'string|max:255',
            'status' => 'integer',
            'asc_id' => 'string|max:50',
            'asc_partner_id' => 'string|max:50',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

            return TeacherResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update teacher', $e);
        }
    }
    
    /**
     * Delete teacher
     * DELETE /teachers/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $teacher = $this->model->find($id);
        
        if (!$teacher) {
            throw new \Exception('Teacher not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete teacher', $e);
        }
    }
}