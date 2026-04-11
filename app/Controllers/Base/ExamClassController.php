<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\ExamClass;
use App\Resources\ExamClassResource;

class ExamClassController extends Controller
{
    protected ExamClass $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new ExamClass();
    }
    
    /**
     * Get all examclasss with pagination
     * GET /examclasss
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exam:id,name']);

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

        return ExamClassResource::collection($result);
    }
    
    /**
     * Get all examclasss without pagination
     * GET /examclasss/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exam:id,name']);

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
             return ExamClassResource::collection($this->model->search($search, $orderBy));
        }
        return ExamClassResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single examclass
     * GET /examclasss/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exam:id,name']);

        $examclass = $this->model->find($id);
        
        if (!$examclass) {
            throw new \Exception('ExamClass not found', 404);
        }
        
        return ExamClassResource::make($examclass);
    }
    
    /**
     * Create new examclass
     * POST /examclasss
     */
    public function store()
    {
        $validated = $this->validate([

        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exam:id,name']);

            $examclass = $this->model->find($id);
            return $this->created(ExamClassResource::make($examclass));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create examclass', $e);
        }
    }
    
    /**
     * Update examclass
     * PUT /examclasss/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $examclass = $this->model->find($id);
        
        if (!$examclass) {
            throw new \Exception('ExamClass not found', 404);
        }
        
        $validated = $this->validate([

        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['class:id,name', 'exam:id,name']);

            return ExamClassResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update examclass', $e);
        }
    }
    
    /**
     * Delete examclass
     * DELETE /examclasss/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $examclass = $this->model->find($id);
        
        if (!$examclass) {
            throw new \Exception('ExamClass not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete examclass', $e);
        }
    }
}