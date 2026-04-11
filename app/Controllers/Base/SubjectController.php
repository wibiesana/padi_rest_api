<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Subject;
use App\Resources\SubjectResource;

class SubjectController extends Controller
{
    protected Subject $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Subject();
    }
    
    /**
     * Get all subjects with pagination
     * GET /subjects
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

        return SubjectResource::collection($result);
    }
    
    /**
     * Get all subjects without pagination
     * GET /subjects/all
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
             return SubjectResource::collection($this->model->search($search, $orderBy));
        }
        return SubjectResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single subject
     * GET /subjects/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $subject = $this->model->find($id);
        
        if (!$subject) {
            throw new \Exception('Subject not found', 404);
        }
        
        return SubjectResource::make($subject);
    }
    
    /**
     * Create new subject
     * POST /subjects
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'string|max:20',
            'asc_id' => 'string|max:50',
            'asc_partner_id' => 'string|max:50',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $subject = $this->model->find($id);
            return $this->created(SubjectResource::make($subject));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create subject', $e);
        }
    }
    
    /**
     * Update subject
     * PUT /subjects/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $subject = $this->model->find($id);
        
        if (!$subject) {
            throw new \Exception('Subject not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'string|max:20',
            'asc_id' => 'string|max:50',
            'asc_partner_id' => 'string|max:50',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return SubjectResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update subject', $e);
        }
    }
    
    /**
     * Delete subject
     * DELETE /subjects/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $subject = $this->model->find($id);
        
        if (!$subject) {
            throw new \Exception('Subject not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete subject', $e);
        }
    }
}