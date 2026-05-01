<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\CalendarAcademic;
use App\Resources\CalendarAcademicResource;

class CalendarAcademicController extends Controller
{
    protected CalendarAcademic $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new CalendarAcademic();
    }
    
    /**
     * Get all calendaracademics with pagination
     * GET /calendaracademics
     */
    public function index()
    {
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

        return CalendarAcademicResource::collection($result);
    }
    
    /**
     * Get all calendaracademics without pagination
     * GET /calendaracademics/all
     */
    public function all()
    {
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
             return CalendarAcademicResource::collection($this->model->search($search, $orderBy));
        }
        return CalendarAcademicResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single calendaracademic
     * GET /calendaracademics/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        $calendaracademic = $this->model->find($id);
        
        if (!$calendaracademic) {
            throw new \Exception('CalendarAcademic not found', 404);
        }
        
        return CalendarAcademicResource::make($calendaracademic);
    }
    
    /**
     * Create new calendaracademic
     * POST /calendaracademics
     */
    public function store()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'string',
            'start_date' => 'required',
            'color' => 'string|max:10',
            'is_holiday' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
            $calendaracademic = $this->model->find($id);
            return $this->created(CalendarAcademicResource::make($calendaracademic));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create calendaracademic', $e);
        }
    }
    
    /**
     * Update calendaracademic
     * PUT /calendaracademics/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $calendaracademic = $this->model->find($id);
        
        if (!$calendaracademic) {
            throw new \Exception('CalendarAcademic not found', 404);
        }
        
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'string',
            'start_date' => 'required',
            'color' => 'string|max:10',
            'is_holiday' => 'integer',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
            return CalendarAcademicResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update calendaracademic', $e);
        }
    }
    
    /**
     * Delete calendaracademic
     * DELETE /calendaracademics/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $calendaracademic = $this->model->find($id);
        
        if (!$calendaracademic) {
            throw new \Exception('CalendarAcademic not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete calendaracademic', $e);
        }
    }
}