<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\LessonSession;
use App\Resources\LessonSessionResource;

class LessonSessionController extends Controller
{
    protected LessonSession $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new LessonSession();
    }
    
    /**
     * Get all lessonsessions with pagination
     * GET /lessonsessions
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule:id,id', 'teacher:id,name']);

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

        return LessonSessionResource::collection($result);
    }
    
    /**
     * Get all lessonsessions without pagination
     * GET /lessonsessions/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule:id,id', 'teacher:id,name']);

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
             return LessonSessionResource::collection($this->model->search($search, $orderBy));
        }
        return LessonSessionResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single lessonsession
     * GET /lessonsessions/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule:id,id', 'teacher:id,name']);

        $lessonsession = $this->model->find($id);
        
        if (!$lessonsession) {
            throw new \Exception('LessonSession not found', 404);
        }
        
        return LessonSessionResource::make($lessonsession);
    }
    
    /**
     * Create new lessonsession
     * POST /lessonsessions
     */
    public function store()
    {
        $validated = $this->validate([
            'teaching_schedule_id' => 'required|integer',
            'session_date' => 'required',
            'teacher_id' => 'required|integer',
            'material' => 'string',
            'note' => 'string|max:255',
            'status' => 'string|max:20',
            'created_by' => 'integer',
            'allow_self_attendance' => 'integer',
            'qr_token' => 'string|max:64'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule:id,id', 'teacher:id,name']);

            $lessonsession = $this->model->find($id);
            return $this->created(LessonSessionResource::make($lessonsession));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create lessonsession', $e);
        }
    }
    
    /**
     * Update lessonsession
     * PUT /lessonsessions/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $lessonsession = $this->model->find($id);
        
        if (!$lessonsession) {
            throw new \Exception('LessonSession not found', 404);
        }
        
        $validated = $this->validate([
            'teaching_schedule_id' => 'required|integer',
            'session_date' => 'required',
            'teacher_id' => 'required|integer',
            'material' => 'string',
            'note' => 'string|max:255',
            'status' => 'string|max:20',
            'created_by' => 'integer',
            'allow_self_attendance' => 'integer',
            'qr_token' => 'string|max:64'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['teachingSchedule:id,id', 'teacher:id,name']);

            return LessonSessionResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update lessonsession', $e);
        }
    }
    
    /**
     * Delete lessonsession
     * DELETE /lessonsessions/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $lessonsession = $this->model->find($id);
        
        if (!$lessonsession) {
            throw new \Exception('LessonSession not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete lessonsession', $e);
        }
    }
}