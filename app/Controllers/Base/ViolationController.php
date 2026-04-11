<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Violation;
use App\Resources\ViolationResource;

class ViolationController extends Controller
{
    protected Violation $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Violation();
    }
    
    /**
     * Get all violations with pagination
     * GET /violations
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,username', 'violationType:id,name', 'createdBy:id,username']);

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

        return ViolationResource::collection($result);
    }
    
    /**
     * Get all violations without pagination
     * GET /violations/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,username', 'violationType:id,name', 'createdBy:id,username']);

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
             return ViolationResource::collection($this->model->search($search, $orderBy));
        }
        return ViolationResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single violation
     * GET /violations/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,username', 'violationType:id,name', 'createdBy:id,username']);

        $violation = $this->model->find($id);
        
        if (!$violation) {
            throw new \Exception('Violation not found', 404);
        }
        
        return ViolationResource::make($violation);
    }
    
    /**
     * Create new violation
     * POST /violations
     */
    public function store()
    {
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'lesson_session_id' => 'required|integer',
            'violation_type_id' => 'required|integer',
            'quantity' => 'integer',
            'total_penalty' => 'required|integer',
            'note' => 'string|max:255',
            'created_by' => 'required|integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,username', 'violationType:id,name', 'createdBy:id,username']);

            $violation = $this->model->find($id);
            return $this->created(ViolationResource::make($violation));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create violation', $e);
        }
    }
    
    /**
     * Update violation
     * PUT /violations/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $violation = $this->model->find($id);
        
        if (!$violation) {
            throw new \Exception('Violation not found', 404);
        }
        
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'lesson_session_id' => 'required|integer',
            'violation_type_id' => 'required|integer',
            'quantity' => 'integer',
            'total_penalty' => 'required|integer',
            'note' => 'string|max:255',
            'created_by' => 'required|integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['lessonSession:id,id', 'student:id,username', 'violationType:id,name', 'createdBy:id,username']);

            return ViolationResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update violation', $e);
        }
    }
    
    /**
     * Delete violation
     * DELETE /violations/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $violation = $this->model->find($id);
        
        if (!$violation) {
            throw new \Exception('Violation not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete violation', $e);
        }
    }
}