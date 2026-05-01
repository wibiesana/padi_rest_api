<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ArticleCommentLike;
use App\Resources\ArticleCommentLikeResource;

class ArticleCommentLikeController extends Controller
{
    protected ArticleCommentLike $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ArticleCommentLike();
    }
    
    /**
     * Get all articlecommentlikes with pagination
     * GET /articlecommentlikes
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['articleComent:id,id', 'user:id,username']);

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

        return ArticleCommentLikeResource::collection($result);
    }
    
    /**
     * Get all articlecommentlikes without pagination
     * GET /articlecommentlikes/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['articleComent:id,id', 'user:id,username']);

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
             return ArticleCommentLikeResource::collection($this->model->search($search, $orderBy));
        }
        return ArticleCommentLikeResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single articlecommentlike
     * GET /articlecommentlikes/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['articleComent:id,id', 'user:id,username']);

        $articlecommentlike = $this->model->find($id);
        
        if (!$articlecommentlike) {
            throw new \Exception('ArticleCommentLike not found', 404);
        }
        
        return ArticleCommentLikeResource::make($articlecommentlike);
    }
    
    /**
     * Create new articlecommentlike
     * POST /articlecommentlikes
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
        $this->model->with(['articleComent:id,id', 'user:id,username']);

            $articlecommentlike = $this->model->find($id);
            return $this->created(ArticleCommentLikeResource::make($articlecommentlike));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create articlecommentlike', $e);
        }
    }
    
    /**
     * Update articlecommentlike
     * PUT /articlecommentlikes/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $articlecommentlike = $this->model->find($id);
        
        if (!$articlecommentlike) {
            throw new \Exception('ArticleCommentLike not found', 404);
        }
        
        $validated = $this->validate([
            'status_like' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['articleComent:id,id', 'user:id,username']);

            return ArticleCommentLikeResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update articlecommentlike', $e);
        }
    }
    
    /**
     * Delete articlecommentlike
     * DELETE /articlecommentlikes/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $articlecommentlike = $this->model->find($id);
        
        if (!$articlecommentlike) {
            throw new \Exception('ArticleCommentLike not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete articlecommentlike', $e);
        }
    }
}