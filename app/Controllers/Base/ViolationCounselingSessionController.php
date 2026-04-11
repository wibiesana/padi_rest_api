<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ViolationCounselingSession;
use App\Resources\ViolationCounselingSessionResource;

class ViolationCounselingSessionController extends Controller
{
    protected ViolationCounselingSession $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ViolationCounselingSession();
    }
    
    /**
     * Get all violationcounselingsessions with pagination
     * GET /violationcounselingsessions
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['counselor:id,username', 'createdBy:id,username', 'student:id,name', 'updatedBy:id,username']);

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

        return ViolationCounselingSessionResource::collection($result);
    }
    
    /**
     * Get all violationcounselingsessions without pagination
     * GET /violationcounselingsessions/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['counselor:id,username', 'createdBy:id,username', 'student:id,name', 'updatedBy:id,username']);

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
             return ViolationCounselingSessionResource::collection($this->model->search($search, $orderBy));
        }
        return ViolationCounselingSessionResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single violationcounselingsession
     * GET /violationcounselingsessions/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['counselor:id,username', 'createdBy:id,username', 'student:id,name', 'updatedBy:id,username']);

        $violationcounselingsession = $this->model->find($id);
        
        if (!$violationcounselingsession) {
            throw new \Exception('ViolationCounselingSession not found', 404);
        }
        
        return ViolationCounselingSessionResource::make($violationcounselingsession);
    }
    
    /**
     * Create new violationcounselingsession
     * POST /violationcounselingsessions
     */
    public function store()
    {
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'counselor_id' => 'required|integer',
            'session_date' => 'required',
            'session_type' => 'string|max:50',
            'topic' => 'required|string|max:255',
            'notes' => 'string',
            'follow_up_required' => 'integer',
            'status' => 'integer',
            'is_confidential' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['counselor:id,username', 'createdBy:id,username', 'student:id,name', 'updatedBy:id,username']);

            $violationcounselingsession = $this->model->find($id);
            return $this->created(ViolationCounselingSessionResource::make($violationcounselingsession));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create violationcounselingsession', $e);
        }
    }
    
    /**
     * Update violationcounselingsession
     * PUT /violationcounselingsessions/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $violationcounselingsession = $this->model->find($id);
        
        if (!$violationcounselingsession) {
            throw new \Exception('ViolationCounselingSession not found', 404);
        }
        
        $validated = $this->validate([
            'student_id' => 'required|integer',
            'counselor_id' => 'required|integer',
            'session_date' => 'required',
            'session_type' => 'string|max:50',
            'topic' => 'required|string|max:255',
            'notes' => 'string',
            'follow_up_required' => 'integer',
            'status' => 'integer',
            'is_confidential' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['counselor:id,username', 'createdBy:id,username', 'student:id,name', 'updatedBy:id,username']);

            return ViolationCounselingSessionResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update violationcounselingsession', $e);
        }
    }
    
    /**
     * Delete violationcounselingsession
     * DELETE /violationcounselingsessions/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $violationcounselingsession = $this->model->find($id);
        
        if (!$violationcounselingsession) {
            throw new \Exception('ViolationCounselingSession not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete violationcounselingsession', $e);
        }
    }
}