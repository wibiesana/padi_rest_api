<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Exam;
use App\Resources\ExamResource;

class ExamController extends Controller
{
    protected Exam $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Exam();
    }
    
    /**
     * Get all exams with pagination
     * GET /exams
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'subject:id,name', 'updatedBy:id,username']);

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

        return ExamResource::collection($result);
    }
    
    /**
     * Get all exams without pagination
     * GET /exams/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'subject:id,name', 'updatedBy:id,username']);

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
             return ExamResource::collection($this->model->search($search, $orderBy));
        }
        return ExamResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single exam
     * GET /exams/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'subject:id,name', 'updatedBy:id,username']);

        $exam = $this->model->find($id);
        
        if (!$exam) {
            throw new \Exception('Exam not found', 404);
        }
        
        return ExamResource::make($exam);
    }
    
    /**
     * Create new exam
     * POST /exams
     */
    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'token' => 'string|max:6',
            'test_duration' => 'integer',
            'exam_event_id' => 'integer',
            'question_bank_id' => 'required|integer',
            'subject_id' => 'integer',
            'use_dynamic_token' => 'integer',
            'show_pg' => 'integer',
            'show_essay' => 'integer',
            'show_result' => 'integer',
            'percentage_mc_value' => 'integer',
            'percentage_essay_value' => 'integer',
            'is_random' => 'integer',
            'status' => 'integer',
            'randomize_questions' => 'integer',
            'randomize_options' => 'integer',
            'lock_on_switch' => 'integer',
            'require_supervisor' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'semester_id' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'subject:id,name', 'updatedBy:id,username']);

            $exam = $this->model->find($id);
            return $this->created(ExamResource::make($exam));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create exam', $e);
        }
    }
    
    /**
     * Update exam
     * PUT /exams/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $exam = $this->model->find($id);
        
        if (!$exam) {
            throw new \Exception('Exam not found', 404);
        }
        
        $validated = $this->validate([
            'name' => 'required|string|max:50',
            'token' => 'string|max:6',
            'test_duration' => 'integer',
            'exam_event_id' => 'integer',
            'question_bank_id' => 'required|integer',
            'subject_id' => 'integer',
            'use_dynamic_token' => 'integer',
            'show_pg' => 'integer',
            'show_essay' => 'integer',
            'show_result' => 'integer',
            'percentage_mc_value' => 'integer',
            'percentage_essay_value' => 'integer',
            'is_random' => 'integer',
            'status' => 'integer',
            'randomize_questions' => 'integer',
            'randomize_options' => 'integer',
            'lock_on_switch' => 'integer',
            'require_supervisor' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'semester_id' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'semester:id,name', 'subject:id,name', 'updatedBy:id,username']);

            return ExamResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update exam', $e);
        }
    }
    
    /**
     * Delete exam
     * DELETE /exams/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $exam = $this->model->find($id);
        
        if (!$exam) {
            throw new \Exception('Exam not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete exam', $e);
        }
    }
}