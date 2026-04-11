<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Payment;
use App\Resources\PaymentResource;

class PaymentController extends Controller
{
    protected Payment $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Payment();
    }
    
    /**
     * Get all payments with pagination
     * GET /payments
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['billing:id,id', 'createdBy:id,username', 'receivedBy:id,username']);

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

        return PaymentResource::collection($result);
    }
    
    /**
     * Get all payments without pagination
     * GET /payments/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['billing:id,id', 'createdBy:id,username', 'receivedBy:id,username']);

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
             return PaymentResource::collection($this->model->search($search, $orderBy));
        }
        return PaymentResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single payment
     * GET /payments/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['billing:id,id', 'createdBy:id,username', 'receivedBy:id,username']);

        $payment = $this->model->find($id);
        
        if (!$payment) {
            throw new \Exception('Payment not found', 404);
        }
        
        return PaymentResource::make($payment);
    }
    
    /**
     * Create new payment
     * POST /payments
     */
    public function store()
    {
        $validated = $this->validate([
            'billing_id' => 'required|integer',
            'payment_date' => 'required',
            'amount' => 'required|numeric',
            'payment_method' => 'string|max:50',
            'reference_number' => 'string|max:100',
            'notes' => 'string',
            'received_by' => 'integer',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['billing:id,id', 'createdBy:id,username', 'receivedBy:id,username']);

            $payment = $this->model->find($id);
            return $this->created(PaymentResource::make($payment));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create payment', $e);
        }
    }
    
    /**
     * Update payment
     * PUT /payments/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $payment = $this->model->find($id);
        
        if (!$payment) {
            throw new \Exception('Payment not found', 404);
        }
        
        $validated = $this->validate([
            'billing_id' => 'required|integer',
            'payment_date' => 'required',
            'amount' => 'required|numeric',
            'payment_method' => 'string|max:50',
            'reference_number' => 'string|max:100',
            'notes' => 'string',
            'received_by' => 'integer',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['billing:id,id', 'createdBy:id,username', 'receivedBy:id,username']);

            return PaymentResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update payment', $e);
        }
    }
    
    /**
     * Delete payment
     * DELETE /payments/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $payment = $this->model->find($id);
        
        if (!$payment) {
            throw new \Exception('Payment not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete payment', $e);
        }
    }
}