<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Migration;
use App\Resources\MigrationResource;

class MigrationController extends Controller
{
    protected Migration $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Migration();
    }
    
    /**
     * Get all migrations with pagination
     * GET /migrations
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

        return MigrationResource::collection($result);
    }
    
    /**
     * Get all migrations without pagination
     * GET /migrations/all
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
             return MigrationResource::collection($this->model->search($search, $orderBy));
        }
        return MigrationResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single migration
     * GET /migrations/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $migration = $this->model->find($id);
        
        if (!$migration) {
            throw new \Exception('Migration not found', 404);
        }
        
        return MigrationResource::make($migration);
    }
    
    /**
     * Create new migration
     * POST /migrations
     */
    public function store()
    {
        $validated = $this->validate([
            'apply_time' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
            $migration = $this->model->find($id);
            return $this->created(MigrationResource::make($migration));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create migration', $e);
        }
    }
    
    /**
     * Update migration
     * PUT /migrations/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $migration = $this->model->find($id);
        
        if (!$migration) {
            throw new \Exception('Migration not found', 404);
        }
        
        $validated = $this->validate([
            'apply_time' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
            return MigrationResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update migration', $e);
        }
    }
    
    /**
     * Delete migration
     * DELETE /migrations/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $migration = $this->model->find($id);
        
        if (!$migration) {
            throw new \Exception('Migration not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete migration', $e);
        }
    }
}