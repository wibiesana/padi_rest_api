<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\StaffUploadResult;
use App\Resources\StaffUploadResultResource;

class StaffUploadResultController extends Controller
{
    protected StaffUploadResult $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new StaffUploadResult();
    }
    
    /**
     * Get all staffuploadresults with pagination
     * GET /staffuploadresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['staff:id,name', 'staffUpload:id,name']);

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

        return StaffUploadResultResource::collection($result);
    }
    
    /**
     * Get all staffuploadresults without pagination
     * GET /staffuploadresults/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['staff:id,name', 'staffUpload:id,name']);

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
             return StaffUploadResultResource::collection($this->model->search($search, $orderBy));
        }
        return StaffUploadResultResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single staffuploadresult
     * GET /staffuploadresults/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['staff:id,name', 'staffUpload:id,name']);

        $staffuploadresult = $this->model->find($id);
        
        if (!$staffuploadresult) {
            throw new \Exception('StaffUploadResult not found', 404);
        }
        
        return StaffUploadResultResource::make($staffuploadresult);
    }
    
    /**
     * Create new staffuploadresult
     * POST /staffuploadresults
     */
    public function store()
    {
        $validated = $this->validate([
            'staff_upload_id' => 'required|integer',
            'staff_id' => 'integer',
            'status' => 'integer',
            'upload_file' => 'string|max:255',
            'description' => 'string|max:255'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['staff:id,name', 'staffUpload:id,name']);

            $staffuploadresult = $this->model->find($id);
            return $this->created(StaffUploadResultResource::make($staffuploadresult));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create staffuploadresult', $e);
        }
    }
    
    /**
     * Update staffuploadresult
     * PUT /staffuploadresults/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $staffuploadresult = $this->model->find($id);
        
        if (!$staffuploadresult) {
            throw new \Exception('StaffUploadResult not found', 404);
        }
        
        $validated = $this->validate([
            'staff_upload_id' => 'required|integer',
            'staff_id' => 'integer',
            'status' => 'integer',
            'upload_file' => 'string|max:255',
            'description' => 'string|max:255'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['staff:id,name', 'staffUpload:id,name']);

            return StaffUploadResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update staffuploadresult', $e);
        }
    }
    
    /**
     * Delete staffuploadresult
     * DELETE /staffuploadresults/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $staffuploadresult = $this->model->find($id);
        
        if (!$staffuploadresult) {
            throw new \Exception('StaffUploadResult not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete staffuploadresult', $e);
        }
    }
}