<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExamSupervisor;
use App\Resources\ExamSupervisorResource;

class ExamSupervisorController extends Controller
{
    protected ExamSupervisor $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamSupervisor();
    }
    
    /**
     * Get all examsupervisors with pagination
     * GET /examsupervisors
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'teacher:id,name']);

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

        return ExamSupervisorResource::collection($result);
    }
    
    /**
     * Get all examsupervisors without pagination
     * GET /examsupervisors/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'teacher:id,name']);

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
             return ExamSupervisorResource::collection($this->model->search($search, $orderBy));
        }
        return ExamSupervisorResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single examsupervisor
     * GET /examsupervisors/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'teacher:id,name']);

        $examsupervisor = $this->model->find($id);
        
        if (!$examsupervisor) {
            throw new \Exception('ExamSupervisor not found', 404);
        }
        
        return ExamSupervisorResource::make($examsupervisor);
    }
    
    /**
     * Create new examsupervisor
     * POST /examsupervisors
     */
    public function store()
    {
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'classroom_id' => 'integer',
            'description' => 'string|max:50'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'teacher:id,name']);

            $examsupervisor = $this->model->find($id);
            return $this->created(ExamSupervisorResource::make($examsupervisor));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create examsupervisor', $e);
        }
    }
    
    /**
     * Update examsupervisor
     * PUT /examsupervisors/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $examsupervisor = $this->model->find($id);
        
        if (!$examsupervisor) {
            throw new \Exception('ExamSupervisor not found', 404);
        }
        
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'classroom_id' => 'integer',
            'description' => 'string|max:50'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'teacher:id,name']);

            return ExamSupervisorResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update examsupervisor', $e);
        }
    }
    
    /**
     * Delete examsupervisor
     * DELETE /examsupervisors/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $examsupervisor = $this->model->find($id);
        
        if (!$examsupervisor) {
            throw new \Exception('ExamSupervisor not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete examsupervisor', $e);
        }
    }
}