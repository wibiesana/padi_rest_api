<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Notification;
use App\Resources\NotificationResource;

class NotificationController extends Controller
{
    protected Notification $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Notification();
    }
    
    /**
     * Get all notifications with pagination
     * GET /notifications
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'user:id,username']);

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

        return NotificationResource::collection($result);
    }
    
    /**
     * Get all notifications without pagination
     * GET /notifications/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'user:id,username']);

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
             return NotificationResource::collection($this->model->search($search, $orderBy));
        }
        return NotificationResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single notification
     * GET /notifications/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'user:id,username']);

        $notification = $this->model->find($id);
        
        if (!$notification) {
            throw new \Exception('Notification not found', 404);
        }
        
        return NotificationResource::make($notification);
    }
    
    /**
     * Create new notification
     * POST /notifications
     */
    public function store()
    {
        $validated = $this->validate([
            'user_id' => 'integer',
            'target_type' => 'string|max:20',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|max:50',
            'related_id' => 'integer',
            'related_type' => 'string|max:50',
            'is_read' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'status' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'user:id,username']);

            $notification = $this->model->find($id);
            return $this->created(NotificationResource::make($notification));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create notification', $e);
        }
    }
    
    /**
     * Update notification
     * PUT /notifications/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $notification = $this->model->find($id);
        
        if (!$notification) {
            throw new \Exception('Notification not found', 404);
        }
        
        $validated = $this->validate([
            'user_id' => 'integer',
            'target_type' => 'string|max:20',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|max:50',
            'related_id' => 'integer',
            'related_type' => 'string|max:50',
            'is_read' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'status' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username', 'user:id,username']);

            return NotificationResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update notification', $e);
        }
    }
    
    /**
     * Delete notification
     * DELETE /notifications/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $notification = $this->model->find($id);
        
        if (!$notification) {
            throw new \Exception('Notification not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete notification', $e);
        }
    }
}