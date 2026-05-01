<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\StudentUploadResult;
use App\Resources\StudentUploadResultResource;

class StudentUploadResultController extends Controller
{
    protected StudentUploadResult $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new StudentUploadResult();
    }
    
    /**
     * Get all studentuploadresults with pagination
     * GET /studentuploadresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['student:id,name', 'studentUpload:id,name']);

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

        return StudentUploadResultResource::collection($result);
    }
    
    /**
     * Get all studentuploadresults without pagination
     * GET /studentuploadresults/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['student:id,name', 'studentUpload:id,name']);

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
             return StudentUploadResultResource::collection($this->model->search($search, $orderBy));
        }
        return StudentUploadResultResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single studentuploadresult
     * GET /studentuploadresults/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['student:id,name', 'studentUpload:id,name']);

        $studentuploadresult = $this->model->find($id);
        
        if (!$studentuploadresult) {
            throw new \Exception('StudentUploadResult not found', 404);
        }
        
        return StudentUploadResultResource::make($studentuploadresult);
    }
    
    /**
     * Create new studentuploadresult
     * POST /studentuploadresults
     */
    public function store()
    {
        $validated = $this->validate([
            'student_upload_id' => 'required|integer',
            'student_id' => 'integer',
            'status' => 'integer',
            'upload_file' => 'string|max:255',
            'description' => 'string|max:255'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['student:id,name', 'studentUpload:id,name']);

            $studentuploadresult = $this->model->find($id);
            return $this->created(StudentUploadResultResource::make($studentuploadresult));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create studentuploadresult', $e);
        }
    }
    
    /**
     * Update studentuploadresult
     * PUT /studentuploadresults/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $studentuploadresult = $this->model->find($id);
        
        if (!$studentuploadresult) {
            throw new \Exception('StudentUploadResult not found', 404);
        }
        
        $validated = $this->validate([
            'student_upload_id' => 'required|integer',
            'student_id' => 'integer',
            'status' => 'integer',
            'upload_file' => 'string|max:255',
            'description' => 'string|max:255'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['student:id,name', 'studentUpload:id,name']);

            return StudentUploadResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update studentuploadresult', $e);
        }
    }
    
    /**
     * Delete studentuploadresult
     * DELETE /studentuploadresults/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $studentuploadresult = $this->model->find($id);
        
        if (!$studentuploadresult) {
            throw new \Exception('StudentUploadResult not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete studentuploadresult', $e);
        }
    }
}