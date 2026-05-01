<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Billing;
use App\Resources\BillingResource;

class BillingController extends Controller
{
    protected Billing $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Billing();
    }
    
    /**
     * Get all billings with pagination
     * GET /billings
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'student:id,name', 'updatedBy:id,username']);

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

        return BillingResource::collection($result);
    }
    
    /**
     * Get all billings without pagination
     * GET /billings/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'student:id,name', 'updatedBy:id,username']);

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
             return BillingResource::collection($this->model->search($search, $orderBy));
        }
        return BillingResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single billing
     * GET /billings/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'student:id,name', 'updatedBy:id,username']);

        $billing = $this->model->find($id);
        
        if (!$billing) {
            throw new \Exception('Billing not found', 404);
        }
        
        return BillingResource::make($billing);
    }
    
    /**
     * Create new billing
     * POST /billings
     */
    public function store()
    {
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'billing_type' => 'required|string|max:50',
            'amount' => 'required|numeric',
            'due_date' => 'required',
            'status' => 'integer',
            'description' => 'string',
            'semester_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'student:id,name', 'updatedBy:id,username']);

            $billing = $this->model->find($id);
            return $this->created(BillingResource::make($billing));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create billing', $e);
        }
    }
    
    /**
     * Update billing
     * PUT /billings/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $billing = $this->model->find($id);
        
        if (!$billing) {
            throw new \Exception('Billing not found', 404);
        }
        
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'billing_type' => 'required|string|max:50',
            'amount' => 'required|numeric',
            'due_date' => 'required',
            'status' => 'integer',
            'description' => 'string',
            'semester_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'semester:id,name', 'student:id,name', 'updatedBy:id,username']);

            return BillingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update billing', $e);
        }
    }
    
    /**
     * Delete billing
     * DELETE /billings/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $billing = $this->model->find($id);
        
        if (!$billing) {
            throw new \Exception('Billing not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete billing', $e);
        }
    }
}