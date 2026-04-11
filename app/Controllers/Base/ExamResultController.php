<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExamResult;
use App\Resources\ExamResultResource;

class ExamResultController extends Controller
{
    protected ExamResult $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamResult();
    }
    
    /**
     * Get all examresults with pagination
     * GET /examresults
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'student:id,name']);

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

        return ExamResultResource::collection($result);
    }
    
    /**
     * Get all examresults without pagination
     * GET /examresults/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'student:id,name']);

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
             return ExamResultResource::collection($this->model->search($search, $orderBy));
        }
        return ExamResultResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single examresult
     * GET /examresults/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'student:id,name']);

        $examresult = $this->model->find($id);
        
        if (!$examresult) {
            throw new \Exception('ExamResult not found', 404);
        }
        
        return ExamResultResource::make($examresult);
    }
    
    /**
     * Create new examresult
     * POST /examresults
     */
    public function store()
    {
        $validated = $this->validate([
            'status' => 'integer',
            'exam_status_id' => 'integer',
            'is_locked' => 'integer',
            'contain_essay' => 'integer',
            'attemp' => 'integer',
            'essay_result' => 'integer',
            'mc_result' => 'integer',
            'total_result' => 'integer',
            'answer_score_list' => 'required|string|max:200',
            'duration' => 'integer',
            'exam_id' => 'required|integer',
            'student_id' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'student:id,name']);

            $examresult = $this->model->find($id);
            return $this->created(ExamResultResource::make($examresult));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create examresult', $e);
        }
    }
    
    /**
     * Update examresult
     * PUT /examresults/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $examresult = $this->model->find($id);
        
        if (!$examresult) {
            throw new \Exception('ExamResult not found', 404);
        }
        
        $validated = $this->validate([
            'status' => 'integer',
            'exam_status_id' => 'integer',
            'is_locked' => 'integer',
            'contain_essay' => 'integer',
            'attemp' => 'integer',
            'essay_result' => 'integer',
            'mc_result' => 'integer',
            'total_result' => 'integer',
            'answer_score_list' => 'required|string|max:200',
            'duration' => 'integer',
            'exam_id' => 'required|integer',
            'student_id' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['exam:id,name', 'student:id,name']);

            return ExamResultResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update examresult', $e);
        }
    }
    
    /**
     * Delete examresult
     * DELETE /examresults/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $examresult = $this->model->find($id);
        
        if (!$examresult) {
            throw new \Exception('ExamResult not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete examresult', $e);
        }
    }
}