<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ActivityLog;
use App\Resources\ActivityLogResource;

class ActivityLogController extends Controller
{
    protected ActivityLog $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ActivityLog();
    }
    
    /**
     * Get all activitylogs with pagination
     * GET /activitylogs
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['user:id,username']);

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

        return ActivityLogResource::collection($result);
    }
    
    /**
     * Get all activitylogs without pagination
     * GET /activitylogs/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['user:id,username']);

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
             return ActivityLogResource::collection($this->model->search($search, $orderBy));
        }
        return ActivityLogResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single activitylog
     * GET /activitylogs/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['user:id,username']);

        $activitylog = $this->model->find($id);
        
        if (!$activitylog) {
            throw new \Exception('ActivityLog not found', 404);
        }
        
        return ActivityLogResource::make($activitylog);
    }
    
    /**
     * Create new activitylog
     * POST /activitylogs
     */
    public function store()
    {
        $validated = $this->validate([
            'user_id' => 'integer',
            'action' => 'required|string|max:255',
            'module' => 'string|max:100',
            'record_id' => 'integer',
            'description' => 'string',
            'ip_address' => 'string|max:45',
            'user_agent' => 'string'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['user:id,username']);

            $activitylog = $this->model->find($id);
            return $this->created(ActivityLogResource::make($activitylog));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create activitylog', $e);
        }
    }
    
    /**
     * Update activitylog
     * PUT /activitylogs/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $activitylog = $this->model->find($id);
        
        if (!$activitylog) {
            throw new \Exception('ActivityLog not found', 404);
        }
        
        $validated = $this->validate([
            'user_id' => 'integer',
            'action' => 'required|string|max:255',
            'module' => 'string|max:100',
            'record_id' => 'integer',
            'description' => 'string',
            'ip_address' => 'string|max:45',
            'user_agent' => 'string'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['user:id,username']);

            return ActivityLogResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update activitylog', $e);
        }
    }
    
    /**
     * Delete activitylog
     * DELETE /activitylogs/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $activitylog = $this->model->find($id);
        
        if (!$activitylog) {
            throw new \Exception('ActivityLog not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete activitylog', $e);
        }
    }
}