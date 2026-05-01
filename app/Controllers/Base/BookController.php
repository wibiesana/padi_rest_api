<?php

namespace App\Controllers\Base;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\Book;
use App\Resources\BookResource;

class BookController extends Controller
{
    protected Book $model;
    
    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new Book();
    }
    
    /**
     * Get all books with pagination
     * GET /books
     */
    public function index()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

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

        return BookResource::collection($result);
    }
    
    /**
     * Get all books without pagination
     * GET /books/all
     */
    public function all()
    {
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

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
             return BookResource::collection($this->model->search($search, $orderBy));
        }
        return BookResource::collection($this->model->all(['*'], $orderBy));
    }
    
    /**
     * Get single book
     * GET /books/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

        $book = $this->model->find($id);
        
        if (!$book) {
            throw new \Exception('Book not found', 404);
        }
        
        return BookResource::make($book);
    }
    
    /**
     * Create new book
     * POST /books
     */
    public function store()
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'author' => 'string|max:255',
            'publisher' => 'string|max:255',
            'isbn' => 'string|max:50',
            'category' => 'string|max:100',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'location' => 'string|max:100',
            'cover_image' => 'string|max:255',
            'description' => 'string',
            'barcode' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $id = $this->model->create($validated);
            // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            $book = $this->model->find($id);
            return $this->created(BookResource::make($book));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create book', $e);
        }
    }
    
    /**
     * Update book
     * PUT /books/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        $book = $this->model->find($id);
        
        if (!$book) {
            throw new \Exception('Book not found', 404);
        }
        
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'author' => 'string|max:255',
            'publisher' => 'string|max:255',
            'isbn' => 'string|max:50',
            'category' => 'string|max:100',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'location' => 'string|max:100',
            'cover_image' => 'string|max:255',
            'description' => 'string',
            'barcode' => 'string|max:255',
            'status' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);
        
        try {
            $this->model->update($id, $validated);
             // Auto-generated eager loading
            
        // Auto-generated eager loading
        $this->model->with(['createdBy:id,username', 'updatedBy:id,username']);

            return BookResource::make($this->model->find($id));
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update book', $e);
        }
    }
    
    /**
     * Delete book
     * DELETE /books/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        $book = $this->model->find($id);
        
        if (!$book) {
            throw new \Exception('Book not found', 404);
        }
        
        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete book', $e);
        }
    }
}