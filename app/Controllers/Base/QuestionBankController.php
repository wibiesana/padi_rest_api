<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\QuestionBank;
use App\Resources\QuestionBankResource;

class QuestionBankController extends Controller
{
    protected QuestionBank $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new QuestionBank();
    }
    
    /**
     * Get all questionbanks with pagination
     * GET /questionbanks
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['examEvent:id,name', 'createdBy:id,username', 'teacher:id,name', 'updatedBy:id,username']);

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

        return QuestionBankResource::collection($result);
    }
    
    /**
     * Get all questionbanks without pagination
     * GET /questionbanks/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['examEvent:id,name', 'createdBy:id,username', 'teacher:id,name', 'updatedBy:id,username']);

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
             return QuestionBankResource::collection($this->model->search($search, $orderBy));
        }
        return QuestionBankResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single questionbank
     * GET /questionbanks/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['examEvent:id,name', 'createdBy:id,username', 'teacher:id,name', 'updatedBy:id,username']);

        $questionbank = $this->model->find($id);
        
        if (!$questionbank) {
            throw new \Exception('QuestionBank not found', 404);
        }
        
        return QuestionBankResource::make($questionbank);
    }
    
    /**
     * Create new questionbank
     * POST /questionbanks
     */
    public function store()
    {
        $validated = $this->validate([
            'exam_event_id' => 'integer',
            'name' => 'required|string|max:50',
            'description' => 'string|max:100',
            'status' => 'integer',
            'teacher_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['examEvent:id,name', 'createdBy:id,username', 'teacher:id,name', 'updatedBy:id,username']);

            $questionbank = $this->model->find($id);
            return $this->created(QuestionBankResource::make($questionbank));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create questionbank', $e);
        }
    }
    
    /**
     * Update questionbank
     * PUT /questionbanks/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $questionbank = $this->model->find($id);
        
        if (!$questionbank) {
            throw new \Exception('QuestionBank not found', 404);
        }
        
        $validated = $this->validate([
            'exam_event_id' => 'integer',
            'name' => 'required|string|max:50',
            'description' => 'string|max:100',
            'status' => 'integer',
            'teacher_id' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['examEvent:id,name', 'createdBy:id,username', 'teacher:id,name', 'updatedBy:id,username']);

            return QuestionBankResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update questionbank', $e);
        }
    }
    
    /**
     * Delete questionbank
     * DELETE /questionbanks/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $questionbank = $this->model->find($id);
        
        if (!$questionbank) {
            throw new \Exception('QuestionBank not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete questionbank', $e);
        }
    }
}