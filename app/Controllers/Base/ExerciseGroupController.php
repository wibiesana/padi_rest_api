<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExerciseGroup;
use App\Resources\ExerciseGroupResource;

class ExerciseGroupController extends Controller
{
    protected ExerciseGroup $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExerciseGroup();
    }
    
    /**
     * Get all exercisegroups with pagination
     * GET /exercisegroups
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exercise:id,name']);

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

        return ExerciseGroupResource::collection($result);
    }
    
    /**
     * Get all exercisegroups without pagination
     * GET /exercisegroups/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exercise:id,name']);

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
             return ExerciseGroupResource::collection($this->model->search($search, $orderBy));
        }
        return ExerciseGroupResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single exercisegroup
     * GET /exercisegroups/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exercise:id,name']);

        $exercisegroup = $this->model->find($id);
        
        if (!$exercisegroup) {
            throw new \Exception('ExerciseGroup not found', 404);
        }
        
        return ExerciseGroupResource::make($exercisegroup);
    }
    
    /**
     * Create new exercisegroup
     * POST /exercisegroups
     */
    public function store()
    {
        $validated = $this->validate([

        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exercise:id,name']);

            $exercisegroup = $this->model->find($id);
            return $this->created(ExerciseGroupResource::make($exercisegroup));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create exercisegroup', $e);
        }
    }
    
    /**
     * Update exercisegroup
     * PUT /exercisegroups/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $exercisegroup = $this->model->find($id);
        
        if (!$exercisegroup) {
            throw new \Exception('ExerciseGroup not found', 404);
        }
        
        $validated = $this->validate([

        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exercise:id,name']);

            return ExerciseGroupResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update exercisegroup', $e);
        }
    }
    
    /**
     * Delete exercisegroup
     * DELETE /exercisegroups/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $exercisegroup = $this->model->find($id);
        
        if (!$exercisegroup) {
            throw new \Exception('ExerciseGroup not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete exercisegroup', $e);
        }
    }
}