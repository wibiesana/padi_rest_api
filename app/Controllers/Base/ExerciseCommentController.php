<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExerciseComment;
use App\Resources\ExerciseCommentResource;

class ExerciseCommentController extends Controller
{
    protected ExerciseComment $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExerciseComment();
    }
    
    /**
     * Get all exercisecomments with pagination
     * GET /exercisecomments
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'exercise:id,name', 'updatedBy:id,username']);

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

        return ExerciseCommentResource::collection($result);
    }
    
    /**
     * Get all exercisecomments without pagination
     * GET /exercisecomments/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'exercise:id,name', 'updatedBy:id,username']);

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
             return ExerciseCommentResource::collection($this->model->search($search, $orderBy));
        }
        return ExerciseCommentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single exercisecomment
     * GET /exercisecomments/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'exercise:id,name', 'updatedBy:id,username']);

        $exercisecomment = $this->model->find($id);
        
        if (!$exercisecomment) {
            throw new \Exception('ExerciseComment not found', 404);
        }
        
        return ExerciseCommentResource::make($exercisecomment);
    }
    
    /**
     * Create new exercisecomment
     * POST /exercisecomments
     */
    public function store()
    {
        $validated = $this->validate([
            'comment' => 'required|string',
            'exercise_id' => 'required|integer',
            'like' => 'integer',
            'dislike' => 'integer',
            'rating' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'exercise:id,name', 'updatedBy:id,username']);

            $exercisecomment = $this->model->find($id);
            return $this->created(ExerciseCommentResource::make($exercisecomment));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create exercisecomment', $e);
        }
    }
    
    /**
     * Update exercisecomment
     * PUT /exercisecomments/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $exercisecomment = $this->model->find($id);
        
        if (!$exercisecomment) {
            throw new \Exception('ExerciseComment not found', 404);
        }
        
        $validated = $this->validate([
            'comment' => 'required|string',
            'exercise_id' => 'required|integer',
            'like' => 'integer',
            'dislike' => 'integer',
            'rating' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'exercise:id,name', 'updatedBy:id,username']);

            return ExerciseCommentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update exercisecomment', $e);
        }
    }
    
    /**
     * Delete exercisecomment
     * DELETE /exercisecomments/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $exercisecomment = $this->model->find($id);
        
        if (!$exercisecomment) {
            throw new \Exception('ExerciseComment not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete exercisecomment', $e);
        }
    }
}