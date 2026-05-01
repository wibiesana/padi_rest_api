<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Asset;
use App\Resources\AssetResource;

class AssetController extends Controller
{
    protected Asset $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Asset();
    }
    
    /**
     * Get all assets with pagination
     * GET /assets
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

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

        return AssetResource::collection($result);
    }
    
    /**
     * Get all assets without pagination
     * GET /assets/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

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
             return AssetResource::collection($this->model->search($search, $orderBy));
        }
        return AssetResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single asset
     * GET /assets/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $asset = $this->model->find($id);
        
        if (!$asset) {
            throw new \Exception('Asset not found', 404);
        }
        
        return AssetResource::make($asset);
    }
    
    /**
     * Create new asset
     * POST /assets
     */
    public function store()
    {
        $validated = $this->validate([
            'asset_code' => 'required|string|max:50|unique:asset,asset_code',
            'name' => 'required|string|max:255',
            'category' => 'string|max:100',
            'description' => 'string',
            'purchase_price' => 'numeric',
            'condition' => 'integer',
            'location' => 'string|max:255',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'is_borrowable' => 'integer',
            'photo' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $asset = $this->model->find($id);
            return $this->created(AssetResource::make($asset));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create asset', $e);
        }
    }
    
    /**
     * Update asset
     * PUT /assets/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $asset = $this->model->find($id);
        
        if (!$asset) {
            throw new \Exception('Asset not found', 404);
        }
        
        $validated = $this->validate([
            'asset_code' => 'required|string|max:50|unique:asset,asset_code,' . $id,
            'name' => 'required|string|max:255',
            'category' => 'string|max:100',
            'description' => 'string',
            'purchase_price' => 'numeric',
            'condition' => 'integer',
            'location' => 'string|max:255',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'is_borrowable' => 'integer',
            'photo' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return AssetResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update asset', $e);
        }
    }
    
    /**
     * Delete asset
     * DELETE /assets/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $asset = $this->model->find($id);
        
        if (!$asset) {
            throw new \Exception('Asset not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete asset', $e);
        }
    }
}