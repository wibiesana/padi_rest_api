<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ArticleLike;
use App\Resources\ArticleLikeResource;

class ArticleLikeController extends Controller
{
    protected ArticleLike $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ArticleLike();
    }
    
    /**
     * Get all articlelikes with pagination
     * GET /articlelikes
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['article:id,id', 'user:id,username']);

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

        return ArticleLikeResource::collection($result);
    }
    
    /**
     * Get all articlelikes without pagination
     * GET /articlelikes/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['article:id,id', 'user:id,username']);

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
             return ArticleLikeResource::collection($this->model->search($search, $orderBy));
        }
        return ArticleLikeResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single articlelike
     * GET /articlelikes/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['article:id,id', 'user:id,username']);

        $articlelike = $this->model->find($id);
        
        if (!$articlelike) {
            throw new \Exception('ArticleLike not found', 404);
        }
        
        return ArticleLikeResource::make($articlelike);
    }
    
    /**
     * Create new articlelike
     * POST /articlelikes
     */
    public function store()
    {
        $validated = $this->validate([
            'status_like' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['article:id,id', 'user:id,username']);

            $articlelike = $this->model->find($id);
            return $this->created(ArticleLikeResource::make($articlelike));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create articlelike', $e);
        }
    }
    
    /**
     * Update articlelike
     * PUT /articlelikes/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $articlelike = $this->model->find($id);
        
        if (!$articlelike) {
            throw new \Exception('ArticleLike not found', 404);
        }
        
        $validated = $this->validate([
            'status_like' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['article:id,id', 'user:id,username']);

            return ArticleLikeResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update articlelike', $e);
        }
    }
    
    /**
     * Delete articlelike
     * DELETE /articlelikes/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $articlelike = $this->model->find($id);
        
        if (!$articlelike) {
            throw new \Exception('ArticleLike not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete articlelike', $e);
        }
    }
}