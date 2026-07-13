<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\User;

class UserController extends Controller
{
    protected User $model;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Get all users with pagination
     * GET /users
     */
    public function index()
    {
        $page = max(1, (int)$this->request->query('page', 1)); // Min page 1
        $perPage = min(100, max(1, (int)$this->request->query('per-page', 10))); // Max 100 per page
        $search = $this->request->query('search');

        if ($search) {
            // Limit search query length to prevent abuse
            $search = substr($search, 0, 255);
            return $this->model->search($search);
        }

        return $this->model->paginate($page, $perPage);
    }

    /**
     * Get all users without pagination
     * GET /users/all
     */
    public function all()
    {
        return $this->model::find()->all();
    }

    /**
     * Get single user
     * GET /users/{id}
     */
    public function show()
    {
        $id = $this->request->param('id');
        return User::findOrFail($id);
    }

    /**
     * Create new user
     * POST /users
     */
    public function store()
    {
        $validated = $this->validate([
            'username' => 'string|max:50|unique:users,username',
            'email' => 'required|string|max:255|email|unique:users,email',
            'password' => 'required|string|max:255',
            'role' => 'string|max:50',
            'status' => 'integer',
            'email_verified_at' => 'email',
            'remember_token' => 'string|max:100',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);

        try {
            $id = $this->model->create($validated);
            $user = $this->model->find($id);
            return $this->created($user);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to create user', $e);
        }
    }

    /**
     * Update user
     * PUT /users/{id}
     */
    public function update()
    {
        $id = $this->request->param('id');
        User::findOrFail($id);

        $validated = $this->validate([
            'username' => 'string|max:50|unique:users,username,' . $id,
            'email' => 'string|max:255|email|unique:users,email,' . $id,
            'password' => 'string|max:255',
            'role' => 'string|max:50',
            'status' => 'integer',
            'email_verified_at' => 'email',
            'remember_token' => 'string|max:100',
            'created_by' => 'integer',
            'updated_by' => 'integer'
        ]);

        try {
            $this->model->update($id, $validated);
            return User::findOrFail($id);
        } catch (\PDOException $e) {
            $this->databaseError('Failed to update user', $e);
        }
    }

    /**
     * Delete user
     * DELETE /users/{id}
     */
    public function destroy()
    {
        $id = $this->request->param('id');
        User::findOrFail($id);

        try {
            $this->model->delete($id);
            return $this->noContent();
        } catch (\PDOException $e) {
            $this->databaseError('Failed to delete user', $e);
        }
    }

    /**
     * Override methods here to add custom logic.
     *
     * Response format examples:
     *   return $data;                                        — Auto-wrapped by Router
     *   return $this->created($data);                        — HTTP 201
     *   return $this->noContent();                           — HTTP 204
     *   return $this->simple($data, 'success', 'CODE');      — {status, code, item}
     *   return $this->raw($data);                            — Raw passthrough
     */
}
