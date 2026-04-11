<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExamReport;
use App\Resources\ExamReportResource;

class ExamReportController extends Controller
{
    protected ExamReport $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamReport();
    }
    
    /**
     * Get all examreports with pagination
     * GET /examreports
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'supervisor:id,name']);

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

        return ExamReportResource::collection($result);
    }
    
    /**
     * Get all examreports without pagination
     * GET /examreports/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'supervisor:id,name']);

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
             return ExamReportResource::collection($this->model->search($search, $orderBy));
        }
        return ExamReportResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single examreport
     * GET /examreports/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'supervisor:id,name']);

        $examreport = $this->model->find($id);
        
        if (!$examreport) {
            throw new \Exception('ExamReport not found', 404);
        }
        
        return ExamReportResource::make($examreport);
    }
    
    /**
     * Create new examreport
     * POST /examreports
     */
    public function store()
    {
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'classroom_id' => 'required|integer',
            'supervisor_id' => 'required|integer',
            'student_count' => 'integer',
            'present_count' => 'integer',
            'absent_count' => 'integer',
            'incident_report' => 'string'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'supervisor:id,name']);

            $examreport = $this->model->find($id);
            return $this->created(ExamReportResource::make($examreport));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create examreport', $e);
        }
    }
    
    /**
     * Update examreport
     * PUT /examreports/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $examreport = $this->model->find($id);
        
        if (!$examreport) {
            throw new \Exception('ExamReport not found', 404);
        }
        
        $validated = $this->validate([
            'exam_id' => 'required|integer',
            'classroom_id' => 'required|integer',
            'supervisor_id' => 'required|integer',
            'student_count' => 'integer',
            'present_count' => 'integer',
            'absent_count' => 'integer',
            'incident_report' => 'string'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'exam:id,name', 'supervisor:id,name']);

            return ExamReportResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update examreport', $e);
        }
    }
    
    /**
     * Delete examreport
     * DELETE /examreports/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $examreport = $this->model->find($id);
        
        if (!$examreport) {
            throw new \Exception('ExamReport not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete examreport', $e);
        }
    }
}