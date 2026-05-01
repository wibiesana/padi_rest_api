<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\TeacherUploadResult;
use App\Resources\TeacherUploadResultResource;

class TeacherUploadResultController extends Controller
{
    protected TeacherUploadResult $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new TeacherUploadResult();
    }
    
    /**
     * Get all teacheruploadresults with pagination
     * GET /teacheruploadresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name', 'teacherUpload:id,name']);

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

        return TeacherUploadResultResource::collection($result);
    }
    
    /**
     * Get all teacheruploadresults without pagination
     * GET /teacheruploadresults/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name', 'teacherUpload:id,name']);

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
             return TeacherUploadResultResource::collection($this->model->search($search, $orderBy));
        }
        return TeacherUploadResultResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single teacheruploadresult
     * GET /teacheruploadresults/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name', 'teacherUpload:id,name']);

        $teacheruploadresult = $this->model->find($id);
        
        if (!$teacheruploadresult) {
            throw new \Exception('TeacherUploadResult not found', 404);
        }
        
        return TeacherUploadResultResource::make($teacheruploadresult);
    }
    
    /**
     * Create new teacheruploadresult
     * POST /teacheruploadresults
     */
    public function store()
    {
        $validated = $this->validate([
            'teacher_upload_id' => 'required|integer',
            'teacher_id' => 'integer',
            'status' => 'integer',
            'upload_file' => 'string|max:255',
            'description' => 'string|max:255'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name', 'teacherUpload:id,name']);

            $teacheruploadresult = $this->model->find($id);
            return $this->created(TeacherUploadResultResource::make($teacheruploadresult));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create teacheruploadresult', $e);
        }
    }
    
    /**
     * Update teacheruploadresult
     * PUT /teacheruploadresults/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $teacheruploadresult = $this->model->find($id);
        
        if (!$teacheruploadresult) {
            throw new \Exception('TeacherUploadResult not found', 404);
        }
        
        $validated = $this->validate([
            'teacher_upload_id' => 'required|integer',
            'teacher_id' => 'integer',
            'status' => 'integer',
            'upload_file' => 'string|max:255',
            'description' => 'string|max:255'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['teacher:id,name', 'teacherUpload:id,name']);

            return TeacherUploadResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update teacheruploadresult', $e);
        }
    }
    
    /**
     * Delete teacheruploadresult
     * DELETE /teacheruploadresults/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $teacheruploadresult = $this->model->find($id);
        
        if (!$teacheruploadresult) {
            throw new \Exception('TeacherUploadResult not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete teacheruploadresult', $e);
        }
    }
}