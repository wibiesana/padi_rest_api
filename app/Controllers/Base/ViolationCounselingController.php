<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ViolationCounseling;
use App\Resources\ViolationCounselingResource;

class ViolationCounselingController extends Controller
{
    protected ViolationCounseling $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ViolationCounseling();
    }
    
    /**
     * Get all violationcounselings with pagination
     * GET /violationcounselings
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'counselingSession:id,id', 'studentViolation:id,id']);

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

        return ViolationCounselingResource::collection($result);
    }
    
    /**
     * Get all violationcounselings without pagination
     * GET /violationcounselings/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'counselingSession:id,id', 'studentViolation:id,id']);

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
             return ViolationCounselingResource::collection($this->model->search($search, $orderBy));
        }
        return ViolationCounselingResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single violationcounseling
     * GET /violationcounselings/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'counselingSession:id,id', 'studentViolation:id,id']);

        $violationcounseling = $this->model->find($id);
        
        if (!$violationcounseling) {
            throw new \Exception('ViolationCounseling not found', 404);
        }
        
        return ViolationCounselingResource::make($violationcounseling);
    }
    
    /**
     * Create new violationcounseling
     * POST /violationcounselings
     */
    public function store()
    {
        $validated = $this->validate([
            'student_violation_id' => 'required|integer',
            'counseling_session_id' => 'integer',
            'action_taken' => 'string',
            'result' => 'string',
            'parent_notified' => 'integer',
            'created_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'counselingSession:id,id', 'studentViolation:id,id']);

            $violationcounseling = $this->model->find($id);
            return $this->created(ViolationCounselingResource::make($violationcounseling));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create violationcounseling', $e);
        }
    }
    
    /**
     * Update violationcounseling
     * PUT /violationcounselings/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $violationcounseling = $this->model->find($id);
        
        if (!$violationcounseling) {
            throw new \Exception('ViolationCounseling not found', 404);
        }
        
        $validated = $this->validate([
            'student_violation_id' => 'required|integer',
            'counseling_session_id' => 'integer',
            'action_taken' => 'string',
            'result' => 'string',
            'parent_notified' => 'integer',
            'created_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'counselingSession:id,id', 'studentViolation:id,id']);

            return ViolationCounselingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update violationcounseling', $e);
        }
    }
    
    /**
     * Delete violationcounseling
     * DELETE /violationcounselings/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $violationcounseling = $this->model->find($id);
        
        if (!$violationcounseling) {
            throw new \Exception('ViolationCounseling not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete violationcounseling', $e);
        }
    }
}