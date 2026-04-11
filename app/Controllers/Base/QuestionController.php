<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Question;
use App\Resources\QuestionResource;

class QuestionController extends Controller
{
    protected Question $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Question();
    }
    
    /**
     * Get all questions with pagination
     * GET /questions
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

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

        return QuestionResource::collection($result);
    }
    
    /**
     * Get all questions without pagination
     * GET /questions/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

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
             return QuestionResource::collection($this->model->search($search, $orderBy));
        }
        return QuestionResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single question
     * GET /questions/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

        $question = $this->model->find($id);
        
        if (!$question) {
            throw new \Exception('Question not found', 404);
        }
        
        return QuestionResource::make($question);
    }
    
    /**
     * Create new question
     * POST /questions
     */
    public function store()
    {
        $validated = $this->validate([
            'type' => 'integer',
            'question' => 'required|string',
            'answer' => 'string',
            'answer_discussion' => 'string',
            'options_json' => 'string',
            'number_of_choice' => 'integer',
            'answer_score' => 'integer',
            'question_bank_id' => 'required|integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

            $question = $this->model->find($id);
            return $this->created(QuestionResource::make($question));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create question', $e);
        }
    }
    
    /**
     * Update question
     * PUT /questions/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $question = $this->model->find($id);
        
        if (!$question) {
            throw new \Exception('Question not found', 404);
        }
        
        $validated = $this->validate([
            'type' => 'integer',
            'question' => 'required|string',
            'answer' => 'string',
            'answer_discussion' => 'string',
            'options_json' => 'string',
            'number_of_choice' => 'integer',
            'answer_score' => 'integer',
            'question_bank_id' => 'required|integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'questionBank:id,name', 'updatedBy:id,username']);

            return QuestionResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update question', $e);
        }
    }
    
    /**
     * Delete question
     * DELETE /questions/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $question = $this->model->find($id);
        
        if (!$question) {
            throw new \Exception('Question not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete question', $e);
        }
    }
}