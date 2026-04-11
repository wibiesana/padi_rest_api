<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AscImportLog;
use App\Resources\AscImportLogResource;

class AscImportLogController extends Controller
{
    protected AscImportLog $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AscImportLog();
    }
    
    /**
     * Get all ascimportlogs with pagination
     * GET /ascimportlogs
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name']);

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

        return AscImportLogResource::collection($result);
    }
    
    /**
     * Get all ascimportlogs without pagination
     * GET /ascimportlogs/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name']);

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
             return AscImportLogResource::collection($this->model->search($search, $orderBy));
        }
        return AscImportLogResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single ascimportlog
     * GET /ascimportlogs/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name']);

        $ascimportlog = $this->model->find($id);
        
        if (!$ascimportlog) {
            throw new \Exception('AscImportLog not found', 404);
        }
        
        return AscImportLogResource::make($ascimportlog);
    }
    
    /**
     * Create new ascimportlog
     * POST /ascimportlogs
     */
    public function store()
    {
        $validated = $this->validate([
            'import_date' => 'required',
            'semester_id' => 'integer',
            'file_name' => 'string|max:255',
            'total_lessons' => 'integer',
            'imported_lessons' => 'integer',
            'total_periods' => 'integer',
            'imported_periods' => 'integer',
            'status' => 'integer',
            'error_log' => 'string',
            'notes' => 'string',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name']);

            $ascimportlog = $this->model->find($id);
            return $this->created(AscImportLogResource::make($ascimportlog));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create ascimportlog', $e);
        }
    }
    
    /**
     * Update ascimportlog
     * PUT /ascimportlogs/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $ascimportlog = $this->model->find($id);
        
        if (!$ascimportlog) {
            throw new \Exception('AscImportLog not found', 404);
        }
        
        $validated = $this->validate([
            'import_date' => 'required',
            'semester_id' => 'integer',
            'file_name' => 'string|max:255',
            'total_lessons' => 'integer',
            'imported_lessons' => 'integer',
            'total_periods' => 'integer',
            'imported_periods' => 'integer',
            'status' => 'integer',
            'error_log' => 'string',
            'notes' => 'string',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name']);

            return AscImportLogResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update ascimportlog', $e);
        }
    }
    
    /**
     * Delete ascimportlog
     * DELETE /ascimportlogs/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $ascimportlog = $this->model->find($id);
        
        if (!$ascimportlog) {
            throw new \Exception('AscImportLog not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete ascimportlog', $e);
        }
    }
}