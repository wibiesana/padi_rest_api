<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ArticleComment;
use App\Resources\ArticleCommentResource;

class ArticleCommentController extends Controller
{
    protected ArticleComment $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ArticleComment();
    }
    
    /**
     * Get all articlecomments with pagination
     * GET /articlecomments
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['updatedBy:id,username', 'article:id,id', 'createdBy:id,username']);

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

        return ArticleCommentResource::collection($result);
    }
    
    /**
     * Get all articlecomments without pagination
     * GET /articlecomments/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['updatedBy:id,username', 'article:id,id', 'createdBy:id,username']);

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
             return ArticleCommentResource::collection($this->model->search($search, $orderBy));
        }
        return ArticleCommentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single articlecomment
     * GET /articlecomments/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['updatedBy:id,username', 'article:id,id', 'createdBy:id,username']);

        $articlecomment = $this->model->find($id);
        
        if (!$articlecomment) {
            throw new \Exception('ArticleComment not found', 404);
        }
        
        return ArticleCommentResource::make($articlecomment);
    }
    
    /**
     * Create new articlecomment
     * POST /articlecomments
     */
    public function store()
    {
        $validated = $this->validate([
            'comment' => 'required|string',
            'article_id' => 'required|integer',
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
        $this->model->with(['updatedBy:id,username', 'article:id,id', 'createdBy:id,username']);

            $articlecomment = $this->model->find($id);
            return $this->created(ArticleCommentResource::make($articlecomment));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create articlecomment', $e);
        }
    }
    
    /**
     * Update articlecomment
     * PUT /articlecomments/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $articlecomment = $this->model->find($id);
        
        if (!$articlecomment) {
            throw new \Exception('ArticleComment not found', 404);
        }
        
        $validated = $this->validate([
            'comment' => 'required|string',
            'article_id' => 'required|integer',
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
        $this->model->with(['updatedBy:id,username', 'article:id,id', 'createdBy:id,username']);

            return ArticleCommentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update articlecomment', $e);
        }
    }
    
    /**
     * Delete articlecomment
     * DELETE /articlecomments/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $articlecomment = $this->model->find($id);
        
        if (!$articlecomment) {
            throw new \Exception('ArticleComment not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete articlecomment', $e);
        }
    }
}