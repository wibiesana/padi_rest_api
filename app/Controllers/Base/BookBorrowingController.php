<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\BookBorrowing;
use App\Resources\BookBorrowingResource;

class BookBorrowingController extends Controller
{
    protected BookBorrowing $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new BookBorrowing();
    }
    
    /**
     * Get all bookborrowings with pagination
     * GET /bookborrowings
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['book:id,title', 'processedBy:id,username', 'user:id,username']);

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

        return BookBorrowingResource::collection($result);
    }
    
    /**
     * Get all bookborrowings without pagination
     * GET /bookborrowings/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['book:id,title', 'processedBy:id,username', 'user:id,username']);

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
             return BookBorrowingResource::collection($this->model->search($search, $orderBy));
        }
        return BookBorrowingResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single bookborrowing
     * GET /bookborrowings/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['book:id,title', 'processedBy:id,username', 'user:id,username']);

        $bookborrowing = $this->model->find($id);
        
        if (!$bookborrowing) {
            throw new \Exception('BookBorrowing not found', 404);
        }
        
        return BookBorrowingResource::make($bookborrowing);
    }
    
    /**
     * Create new bookborrowing
     * POST /bookborrowings
     */
    public function store()
    {
        $validated = $this->validate([
            'book_id' => 'required|integer',
            'user_id' => 'required|integer',
            'borrow_date' => 'required',
            'due_date' => 'required',
            'status' => 'integer',
            'notes' => 'string',
            'fine_amount' => 'numeric',
            'processed_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['book:id,title', 'processedBy:id,username', 'user:id,username']);

            $bookborrowing = $this->model->find($id);
            return $this->created(BookBorrowingResource::make($bookborrowing));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create bookborrowing', $e);
        }
    }
    
    /**
     * Update bookborrowing
     * PUT /bookborrowings/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $bookborrowing = $this->model->find($id);
        
        if (!$bookborrowing) {
            throw new \Exception('BookBorrowing not found', 404);
        }
        
        $validated = $this->validate([
            'book_id' => 'required|integer',
            'user_id' => 'required|integer',
            'borrow_date' => 'required',
            'due_date' => 'required',
            'status' => 'integer',
            'notes' => 'string',
            'fine_amount' => 'numeric',
            'processed_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['book:id,title', 'processedBy:id,username', 'user:id,username']);

            return BookBorrowingResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update bookborrowing', $e);
        }
    }
    
    /**
     * Delete bookborrowing
     * DELETE /bookborrowings/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $bookborrowing = $this->model->find($id);
        
        if (!$bookborrowing) {
            throw new \Exception('BookBorrowing not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete bookborrowing', $e);
        }
    }
}