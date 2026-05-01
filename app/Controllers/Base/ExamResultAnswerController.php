<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExamResultAnswer;
use App\Resources\ExamResultAnswerResource;

class ExamResultAnswerController extends Controller
{
    protected ExamResultAnswer $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamResultAnswer();
    }
    
    /**
     * Get all examresultanswers with pagination
     * GET /examresultanswers
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['examResult:id,id', 'question:id,id']);

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

        return ExamResultAnswerResource::collection($result);
    }
    
    /**
     * Get all examresultanswers without pagination
     * GET /examresultanswers/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['examResult:id,id', 'question:id,id']);

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
             return ExamResultAnswerResource::collection($this->model->search($search, $orderBy));
        }
        return ExamResultAnswerResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single examresultanswer
     * GET /examresultanswers/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['examResult:id,id', 'question:id,id']);

        $examresultanswer = $this->model->find($id);
        
        if (!$examresultanswer) {
            throw new \Exception('ExamResultAnswer not found', 404);
        }
        
        return ExamResultAnswerResource::make($examresultanswer);
    }
    
    /**
     * Create new examresultanswer
     * POST /examresultanswers
     */
    public function store()
    {
        $validated = $this->validate([
            'exam_result_id' => 'required|integer',
            'question_id' => 'required|integer',
            'answer' => 'string',
            'score' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['examResult:id,id', 'question:id,id']);

            $examresultanswer = $this->model->find($id);
            return $this->created(ExamResultAnswerResource::make($examresultanswer));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create examresultanswer', $e);
        }
    }
    
    /**
     * Update examresultanswer
     * PUT /examresultanswers/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $examresultanswer = $this->model->find($id);
        
        if (!$examresultanswer) {
            throw new \Exception('ExamResultAnswer not found', 404);
        }
        
        $validated = $this->validate([
            'exam_result_id' => 'required|integer',
            'question_id' => 'required|integer',
            'answer' => 'string',
            'score' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['examResult:id,id', 'question:id,id']);

            return ExamResultAnswerResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update examresultanswer', $e);
        }
    }
    
    /**
     * Delete examresultanswer
     * DELETE /examresultanswers/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $examresultanswer = $this->model->find($id);
        
        if (!$examresultanswer) {
            throw new \Exception('ExamResultAnswer not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete examresultanswer', $e);
        }
    }
}