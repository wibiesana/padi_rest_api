<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ArticleClass;
use App\Resources\ArticleClassResource;

class ArticleClassController extends Controller
{
    protected ArticleClass $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ArticleClass();
    }
    
    /**
     * Get all articleclasss with pagination
     * GET /articleclasss
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'article:id,id']);

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

        return ArticleClassResource::collection($result);
    }
    
    /**
     * Get all articleclasss without pagination
     * GET /articleclasss/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'article:id,id']);

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
             return ArticleClassResource::collection($this->model->search($search, $orderBy));
        }
        return ArticleClassResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single articleclass
     * GET /articleclasss/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'article:id,id']);

        $articleclass = $this->model->find($id);
        
        if (!$articleclass) {
            throw new \Exception('ArticleClass not found', 404);
        }
        
        return ArticleClassResource::make($articleclass);
    }
    
    /**
     * Create new articleclass
     * POST /articleclasss
     */
    public function store()
    {
        $validated = $this->validate([

        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'article:id,id']);

            $articleclass = $this->model->find($id);
            return $this->created(ArticleClassResource::make($articleclass));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create articleclass', $e);
        }
    }
    
    /**
     * Update articleclass
     * PUT /articleclasss/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $articleclass = $this->model->find($id);
        
        if (!$articleclass) {
            throw new \Exception('ArticleClass not found', 404);
        }
        
        $validated = $this->validate([

        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'article:id,id']);

            return ArticleClassResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update articleclass', $e);
        }
    }
    
    /**
     * Delete articleclass
     * DELETE /articleclasss/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $articleclass = $this->model->find($id);
        
        if (!$articleclass) {
            throw new \Exception('ArticleClass not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete articleclass', $e);
        }
    }
}