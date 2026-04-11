<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExamExaminer;
use App\Resources\ExamExaminerResource;

class ExamExaminerController extends Controller
{
    protected ExamExaminer $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamExaminer();
    }
    
    /**
     * Get all examexaminers with pagination
     * GET /examexaminers
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'teacher:id,name']);

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

        return ExamExaminerResource::collection($result);
    }
    
    /**
     * Get all examexaminers without pagination
     * GET /examexaminers/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'teacher:id,name']);

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
             return ExamExaminerResource::collection($this->model->search($search, $orderBy));
        }
        return ExamExaminerResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single examexaminer
     * GET /examexaminers/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'teacher:id,name']);

        $examexaminer = $this->model->find($id);
        
        if (!$examexaminer) {
            throw new \Exception('ExamExaminer not found', 404);
        }
        
        return ExamExaminerResource::make($examexaminer);
    }
    
    /**
     * Create new examexaminer
     * POST /examexaminers
     */
    public function store()
    {
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'teacher_id' => 'required|integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'teacher:id,name']);

            $examexaminer = $this->model->find($id);
            return $this->created(ExamExaminerResource::make($examexaminer));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create examexaminer', $e);
        }
    }
    
    /**
     * Update examexaminer
     * PUT /examexaminers/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $examexaminer = $this->model->find($id);
        
        if (!$examexaminer) {
            throw new \Exception('ExamExaminer not found', 404);
        }
        
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'teacher_id' => 'required|integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'teacher:id,name']);

            return ExamExaminerResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update examexaminer', $e);
        }
    }
    
    /**
     * Delete examexaminer
     * DELETE /examexaminers/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $examexaminer = $this->model->find($id);
        
        if (!$examexaminer) {
            throw new \Exception('ExamExaminer not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete examexaminer', $e);
        }
    }
}