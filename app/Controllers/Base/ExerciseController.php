<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Exercise;
use App\Resources\ExerciseResource;

class ExerciseController extends Controller
{
    protected Exercise $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Exercise();
    }
    
    /**
     * Get all exercises with pagination
     * GET /exercises
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'updatedBy:id,username']);

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

        return ExerciseResource::collection($result);
    }
    
    /**
     * Get all exercises without pagination
     * GET /exercises/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'updatedBy:id,username']);

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
             return ExerciseResource::collection($this->model->search($search, $orderBy));
        }
        return ExerciseResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single exercise
     * GET /exercises/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'updatedBy:id,username']);

        $exercise = $this->model->find($id);
        
        if (!$exercise) {
            throw new \Exception('Exercise not found', 404);
        }
        
        return ExerciseResource::make($exercise);
    }
    
    /**
     * Create new exercise
     * POST /exercises
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'slug' => 'string|max:150',
            'description' => 'string|max:400',
            'show_result' => 'integer',
            'percentage_mc_value' => 'integer',
            'percentage_essay_value' => 'integer',
            'is_use_token' => 'integer',
            'token' => 'string|max:6',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'question_bank_id' => 'required|integer',
            'is_for_group' => 'integer',
            'semester_id' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'updatedBy:id,username']);

            $exercise = $this->model->find($id);
            return $this->created(ExerciseResource::make($exercise));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create exercise', $e);
        }
    }
    
    /**
     * Update exercise
     * PUT /exercises/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $exercise = $this->model->find($id);
        
        if (!$exercise) {
            throw new \Exception('Exercise not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'slug' => 'string|max:150',
            'description' => 'string|max:400',
            'show_result' => 'integer',
            'percentage_mc_value' => 'integer',
            'percentage_essay_value' => 'integer',
            'is_use_token' => 'integer',
            'token' => 'string|max:6',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'question_bank_id' => 'required|integer',
            'is_for_group' => 'integer',
            'semester_id' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'updatedBy:id,username']);

            return ExerciseResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update exercise', $e);
        }
    }
    
    /**
     * Delete exercise
     * DELETE /exercises/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $exercise = $this->model->find($id);
        
        if (!$exercise) {
            throw new \Exception('Exercise not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete exercise', $e);
        }
    }
}