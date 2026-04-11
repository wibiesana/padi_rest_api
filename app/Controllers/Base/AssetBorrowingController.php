<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\AssetBorrowing;
use App\Resources\AssetBorrowingResource;

class AssetBorrowingController extends Controller
{
    protected AssetBorrowing $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new AssetBorrowing();
    }
    
    /**
     * Get all assetborrowings with pagination
     * GET /assetborrowings
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['approvedBy:id,username', 'asset:id,name', 'user:id,username']);

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

        return AssetBorrowingResource::collection($result);
    }
    
    /**
     * Get all assetborrowings without pagination
     * GET /assetborrowings/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['approvedBy:id,username', 'asset:id,name', 'user:id,username']);

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
             return AssetBorrowingResource::collection($this->model->search($search, $orderBy));
        }
        return AssetBorrowingResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single assetborrowing
     * GET /assetborrowings/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['approvedBy:id,username', 'asset:id,name', 'user:id,username']);

        $assetborrowing = $this->model->find($id);
        
        if (!$assetborrowing) {
            throw new \Exception('AssetBorrowing not found', 404);
        }
        
        return AssetBorrowingResource::make($assetborrowing);
    }
    
    /**
     * Create new assetborrowing
     * POST /assetborrowings
     */
    public function store()
    {
        $validated = $this->validate([
            'asset_id' => 'required|integer',
            'user_id' => 'required|integer',
            'borrow_date' => 'required',
            'expected_return_date' => 'required',
            'quantity' => 'integer',
            'purpose' => 'string',
            'status' => 'integer',
            'return_condition' => 'integer',
            'notes' => 'string',
            'approved_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['approvedBy:id,username', 'asset:id,name', 'user:id,username']);

            $assetborrowing = $this->model->find($id);
            return $this->created(AssetBorrowingResource::make($assetborrowing));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create assetborrowing', $e);
        }
    }
    
    /**
     * Update assetborrowing
     * PUT /assetborrowings/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $assetborrowing = $this->model->find($id);
        
        if (!$assetborrowing) {
            throw new \Exception('AssetBorrowing not found', 404);
        }
        
        $validated = $this->validate([
            'asset_id' => 'required|integer',
            'user_id' => 'required|integer',
            'borrow_date' => 'required',
            'expected_return_date' => 'required',
            'quantity' => 'integer',
            'purpose' => 'string',
            'status' => 'integer',
            'return_condition' => 'integer',
            'notes' => 'string',
            'approved_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['approvedBy:id,username', 'asset:id,name', 'user:id,username']);

            return AssetBorrowingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update assetborrowing', $e);
        }
    }
    
    /**
     * Delete assetborrowing
     * DELETE /assetborrowings/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $assetborrowing = $this->model->find($id);
        
        if (!$assetborrowing) {
            throw new \Exception('AssetBorrowing not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete assetborrowing', $e);
        }
    }
}