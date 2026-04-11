<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Setting;
use App\Resources\SettingResource;

class SettingController extends Controller
{
    protected Setting $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Setting();
    }
    
    /**
     * Get all settings with pagination
     * GET /settings
     */
    public function index()
    {
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

        return SettingResource::collection($result);
    }
    
    /**
     * Get all settings without pagination
     * GET /settings/all
     */
    public function all()
    {
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
             return SettingResource::collection($this->model->search($search, $orderBy));
        }
        return SettingResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single setting
     * GET /settings/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $setting = $this->model->find($id);
        
        if (!$setting) {
            throw new \Exception('Setting not found', 404);
        }
        
        return SettingResource::make($setting);
    }
    
    /**
     * Create new setting
     * POST /settings
     */
    public function store()
    {
        $validated = $this->validate([
            'setting' => 'string',
            'status' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
            $setting = $this->model->find($id);
            return $this->created(SettingResource::make($setting));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create setting', $e);
        }
    }
    
    /**
     * Update setting
     * PUT /settings/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $setting = $this->model->find($id);
        
        if (!$setting) {
            throw new \Exception('Setting not found', 404);
        }
        
        $validated = $this->validate([
            'setting' => 'string',
            'status' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
            return SettingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update setting', $e);
        }
    }
    
    /**
     * Delete setting
     * DELETE /settings/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $setting = $this->model->find($id);
        
        if (!$setting) {
            throw new \Exception('Setting not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete setting', $e);
        }
    }
}