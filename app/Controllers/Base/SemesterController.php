<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Semester;
use App\Resources\SemesterResource;

class SemesterController extends Controller
{
    protected Semester $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Semester();
    }
    
    /**
     * Get all semesters with pagination
     * GET /semesters
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

        return SemesterResource::collection($result);
    }
    
    /**
     * Get all semesters without pagination
     * GET /semesters/all
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
             return SemesterResource::collection($this->model->search($search, $orderBy));
        }
        return SemesterResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single semester
     * GET /semesters/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $semester = $this->model->find($id);
        
        if (!$semester) {
            throw new \Exception('Semester not found', 404);
        }
        
        return SemesterResource::make($semester);
    }
    
    /**
     * Create new semester
     * POST /semesters
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $semester = $this->model->find($id);
            return $this->created(SemesterResource::make($semester));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create semester', $e);
        }
    }
    
    /**
     * Update semester
     * PUT /semesters/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $semester = $this->model->find($id);
        
        if (!$semester) {
            throw new \Exception('Semester not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return SemesterResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update semester', $e);
        }
    }
    
    /**
     * Delete semester
     * DELETE /semesters/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $semester = $this->model->find($id);
        
        if (!$semester) {
            throw new \Exception('Semester not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete semester', $e);
        }
    }
}