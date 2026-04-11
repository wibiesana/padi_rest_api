<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\TeachingSchedule;
use App\Resources\TeachingScheduleResource;

class TeachingScheduleController extends Controller
{
    protected TeachingSchedule $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new TeachingSchedule();
    }
    
    /**
     * Get all teachingschedules with pagination
     * GET /teachingschedules
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

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

        return TeachingScheduleResource::collection($result);
    }
    
    /**
     * Get all teachingschedules without pagination
     * GET /teachingschedules/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

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
             return TeachingScheduleResource::collection($this->model->search($search, $orderBy));
        }
        return TeachingScheduleResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single teachingschedule
     * GET /teachingschedules/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

        $teachingschedule = $this->model->find($id);
        
        if (!$teachingschedule) {
            throw new \Exception('TeachingSchedule not found', 404);
        }
        
        return TeachingScheduleResource::make($teachingschedule);
    }
    
    /**
     * Create new teachingschedule
     * POST /teachingschedules
     */
    public function store()
    {
        $validated = $this->validate([
            'classroom_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'semester_id' => 'integer',
            'day_of_week' => 'required|integer',
            'period_number' => 'integer',
            'period_number_end' => 'integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'asc_lesson_id' => 'string|max:50',
            'asc_subject_id' => 'string|max:50',
            'asc_teacher_id' => 'string|max:50',
            'asc_class_id' => 'string|max:50',
            'periods_per_week' => 'numeric',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

            $teachingschedule = $this->model->find($id);
            return $this->created(TeachingScheduleResource::make($teachingschedule));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create teachingschedule', $e);
        }
    }
    
    /**
     * Update teachingschedule
     * PUT /teachingschedules/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $teachingschedule = $this->model->find($id);
        
        if (!$teachingschedule) {
            throw new \Exception('TeachingSchedule not found', 404);
        }
        
        $validated = $this->validate([
            'classroom_id' => 'required|integer',
            'subject_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'semester_id' => 'integer',
            'day_of_week' => 'required|integer',
            'period_number' => 'integer',
            'period_number_end' => 'integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'asc_lesson_id' => 'string|max:50',
            'asc_subject_id' => 'string|max:50',
            'asc_teacher_id' => 'string|max:50',
            'asc_class_id' => 'string|max:50',
            'periods_per_week' => 'numeric',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['classroom:id,name', 'createdBy:id,username', 'semester:id,name', 'subject:id,name', 'teacher:id,name', 'updatedBy:id,username']);

            return TeachingScheduleResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update teachingschedule', $e);
        }
    }
    
    /**
     * Delete teachingschedule
     * DELETE /teachingschedules/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $teachingschedule = $this->model->find($id);
        
        if (!$teachingschedule) {
            throw new \Exception('TeachingSchedule not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete teachingschedule', $e);
        }
    }
}