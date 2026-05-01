<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Student;
use App\Resources\StudentResource;

class StudentController extends Controller
{
    protected Student $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Student();
    }
    
    /**
     * Get all students with pagination
     * GET /students
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

        return StudentResource::collection($result);
    }
    
    /**
     * Get all students without pagination
     * GET /students/all
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
             return StudentResource::collection($this->model->search($search, $orderBy));
        }
        return StudentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single student
     * GET /students/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

        $student = $this->model->find($id);
        
        if (!$student) {
            throw new \Exception('Student not found', 404);
        }
        
        return StudentResource::make($student);
    }
    
    /**
     * Create new student
     * POST /students
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'nis' => 'required|string|max:25',
            'nisn' => 'string|max:25',
            'jenis_kelamin' => 'string',
            'tempat_lahir' => 'string|max:50',
            'agama' => 'string|max:50',
            'status' => 'string|max:25',
            'anak_ke' => 'integer',
            'alamat' => 'string|max:300',
            'rt' => 'string|max:3',
            'rw' => 'string|max:3',
            'desa_kelurahan' => 'string|max:50',
            'kecamatan' => 'string|max:50',
            'kode_pos' => 'string|max:10',
            'no_telp' => 'string|max:25',
            'email' => 'string|max:50|email',
            'father_name' => 'string|max:50',
            'mother_name' => 'string|max:50',
            'father_occupation' => 'string|max:50',
            'mother_occupation' => 'string|max:50',
            'guardian_name' => 'string|max:50',
            'guardian_address' => 'string|max:50',
            'guardian_phone' => 'string|max:25',
            'guardian_occupation' => 'string|max:25',
            'photo' => 'string|max:100',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

            $student = $this->model->find($id);
            return $this->created(StudentResource::make($student));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create student', $e);
        }
    }
    
    /**
     * Update student
     * PUT /students/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $student = $this->model->find($id);
        
        if (!$student) {
            throw new \Exception('Student not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'nis' => 'required|string|max:25',
            'nisn' => 'string|max:25',
            'jenis_kelamin' => 'string',
            'tempat_lahir' => 'string|max:50',
            'agama' => 'string|max:50',
            'status' => 'string|max:25',
            'anak_ke' => 'integer',
            'alamat' => 'string|max:300',
            'rt' => 'string|max:3',
            'rw' => 'string|max:3',
            'desa_kelurahan' => 'string|max:50',
            'kecamatan' => 'string|max:50',
            'kode_pos' => 'string|max:10',
            'no_telp' => 'string|max:25',
            'email' => 'string|max:50|email',
            'father_name' => 'string|max:50',
            'mother_name' => 'string|max:50',
            'father_occupation' => 'string|max:50',
            'mother_occupation' => 'string|max:50',
            'guardian_name' => 'string|max:50',
            'guardian_address' => 'string|max:50',
            'guardian_phone' => 'string|max:25',
            'guardian_occupation' => 'string|max:25',
            'photo' => 'string|max:100',
            'is_active' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'id:id,username']);

            return StudentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update student', $e);
        }
    }
    
    /**
     * Delete student
     * DELETE /students/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $student = $this->model->find($id);
        
        if (!$student) {
            throw new \Exception('Student not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete student', $e);
        }
    }
}