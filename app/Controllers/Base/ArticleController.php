<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Article;
use App\Resources\ArticleResource;

class ArticleController extends Controller
{
    protected Article $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Article();
    }
    
    /**
     * Get all articles with pagination
     * GET /articles
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username']);

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

        return ArticleResource::collection($result);
    }
    
    /**
     * Get all articles without pagination
     * GET /articles/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username']);

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
             return ArticleResource::collection($this->model->search($search, $orderBy));
        }
        return ArticleResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single article
     * GET /articles/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username']);

        $article = $this->model->find($id);
        
        if (!$article) {
            throw new \Exception('Article not found', 404);
        }
        
        return ArticleResource::make($article);
    }
    
    /**
     * Create new article
     * POST /articles
     */
    public function store()
    {
        $validated = $this->validate([
            'article' => 'required|string|max:255',
            'slug' => 'string|max:400',
            'article_preview' => 'required|string',
            'article_body' => 'required|string',
            'subject_id' => 'integer',
            'like_count' => 'integer',
            'dislike_count' => 'integer',
            'view_count' => 'integer',
            'rating_count' => 'integer',
            'comment_count' => 'integer',
            'tag' => 'string',
            'pin' => 'integer',
            'publish' => 'integer',
            'lock' => 'integer',
            'storage_type' => 'string|max:20',
            'storage_path' => 'string|max:500',
            'file_size' => 'integer',
            'file_type' => 'string|max:100',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username']);

            $article = $this->model->find($id);
            return $this->created(ArticleResource::make($article));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create article', $e);
        }
    }
    
    /**
     * Update article
     * PUT /articles/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $article = $this->model->find($id);
        
        if (!$article) {
            throw new \Exception('Article not found', 404);
        }
        
        $validated = $this->validate([
            'article' => 'required|string|max:255',
            'slug' => 'string|max:400',
            'article_preview' => 'required|string',
            'article_body' => 'required|string',
            'subject_id' => 'integer',
            'like_count' => 'integer',
            'dislike_count' => 'integer',
            'view_count' => 'integer',
            'rating_count' => 'integer',
            'comment_count' => 'integer',
            'tag' => 'string',
            'pin' => 'integer',
            'publish' => 'integer',
            'lock' => 'integer',
            'storage_type' => 'string|max:20',
            'storage_path' => 'string|max:500',
            'file_size' => 'integer',
            'file_type' => 'string|max:100',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'subject:id,name', 'updatedBy:id,username']);

            return ArticleResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update article', $e);
        }
    }
    
    /**
     * Delete article
     * DELETE /articles/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $article = $this->model->find($id);
        
        if (!$article) {
            throw new \Exception('Article not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete article', $e);
        }
    }
}