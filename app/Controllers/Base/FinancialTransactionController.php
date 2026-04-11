<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\FinancialTransaction;
use App\Resources\FinancialTransactionResource;

class FinancialTransactionController extends Controller
{
    protected FinancialTransaction $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new FinancialTransaction();
    }
    
    /**
     * Get all financialtransactions with pagination
     * GET /financialtransactions
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

        return FinancialTransactionResource::collection($result);
    }
    
    /**
     * Get all financialtransactions without pagination
     * GET /financialtransactions/all
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
             return FinancialTransactionResource::collection($this->model->search($search, $orderBy));
        }
        return FinancialTransactionResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single financialtransaction
     * GET /financialtransactions/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $financialtransaction = $this->model->find($id);
        
        if (!$financialtransaction) {
            throw new \Exception('FinancialTransaction not found', 404);
        }
        
        return FinancialTransactionResource::make($financialtransaction);
    }
    
    /**
     * Create new financialtransaction
     * POST /financialtransactions
     */
    public function store()
    {
        $validated = $this->validate([
            'transaction_date' => 'required',
            'transaction_type' => 'required|integer',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric',
            'description' => 'string',
            'reference_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $financialtransaction = $this->model->find($id);
            return $this->created(FinancialTransactionResource::make($financialtransaction));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create financialtransaction', $e);
        }
    }
    
    /**
     * Update financialtransaction
     * PUT /financialtransactions/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $financialtransaction = $this->model->find($id);
        
        if (!$financialtransaction) {
            throw new \Exception('FinancialTransaction not found', 404);
        }
        
        $validated = $this->validate([
            'transaction_date' => 'required',
            'transaction_type' => 'required|integer',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric',
            'description' => 'string',
            'reference_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return FinancialTransactionResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update financialtransaction', $e);
        }
    }
    
    /**
     * Delete financialtransaction
     * DELETE /financialtransactions/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $financialtransaction = $this->model->find($id);
        
        if (!$financialtransaction) {
            throw new \Exception('FinancialTransaction not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete financialtransaction', $e);
        }
    }
}