<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\StudentUpload;
use App\Resources\StudentUploadResource;

class StudentUploadController extends Controller
{
    protected StudentUpload $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new StudentUpload();
    }
    
    /**
     * Get all studentuploads with pagination
     * GET /studentuploads
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'semester:id,name']);

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

        return StudentUploadResource::collection($result);
    }
    
    /**
     * Get all studentuploads without pagination
     * GET /studentuploads/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'semester:id,name']);

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
             return StudentUploadResource::collection($this->model->search($search, $orderBy));
        }
        return StudentUploadResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single studentupload
     * GET /studentuploads/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'semester:id,name']);

        $studentupload = $this->model->find($id);
        
        if (!$studentupload) {
            throw new \Exception('StudentUpload not found', 404);
        }
        
        return StudentUploadResource::make($studentupload);
    }
    
    /**
     * Create new studentupload
     * POST /studentuploads
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'string|max:255',
            'status' => 'integer',
            'is_multiple' => 'integer',
            'semester_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'semester:id,name']);

            $studentupload = $this->model->find($id);
            return $this->created(StudentUploadResource::make($studentupload));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create studentupload', $e);
        }
    }
    
    /**
     * Update studentupload
     * PUT /studentuploads/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $studentupload = $this->model->find($id);
        
        if (!$studentupload) {
            throw new \Exception('StudentUpload not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'string|max:255',
            'status' => 'integer',
            'is_multiple' => 'integer',
            'semester_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'semester:id,name']);

            return StudentUploadResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update studentupload', $e);
        }
    }
    
    /**
     * Delete studentupload
     * DELETE /studentuploads/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $studentupload = $this->model->find($id);
        
        if (!$studentupload) {
            throw new \Exception('StudentUpload not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete studentupload', $e);
        }
    }
}