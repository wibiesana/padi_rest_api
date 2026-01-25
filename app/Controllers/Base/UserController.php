<?php

namespace App\Controllers\Base;

use Core\Controller;
use App\Models\User;

class UserController extends Controller
{
    protected User $model;

    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Get all users with pagination
     * GET /users
     */
    public function index(): void
    {
        $page = max(1, (int)$this->request->query('page', 1)); // Min page 1
        $perPage = min(100, max(1, (int)$this->request->query('per_page', 10))); // Max 100 per page
        $search = $this->request->query('search');

        if ($search) {
            // Limit search query length to prevent abuse
            $search = substr($search, 0, 255);
            $data = $this->model->search($search);
            $this->success(['data' => $data]);
            return;
        }

        $result = $this->model->paginate($page, $perPage);
        $this->success($result);
    }

    /**
     * Get all users without pagination
     * GET /users/all
     */
    public function all(): void
    {
        $data = $this->model::findQuery()->all();
        $this->success(['data' => $data]);
    }

    /**
     * Get single user
     * GET /users/{id}
     */
    public function show(): void
    {
        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            $this->notFound('User not found');
        }

        $this->success($user);
    }

    /**
     * Create new user
     * POST /users
     */
    public function store(): void
    {
        $validated = $this->validate([
            'username' => 'string|max:50|unique:users,username',
            'email' => 'required|string|max:255|email|unique:users,email',
            'password' => 'required|string|max:255',
            'role' => 'string|max:50',
            'status' => 'string|max:20',
            'remember_token' => 'string|max:100'
        ]);

        $id = $this->model->create($validated);

        $this->success([
            'id' => $id,
            'user' => $this->model->find($id)
        ], 'User created successfully', 201);
    }

    /**
     * Update user
     * PUT /users/{id}
     */
    public function update(): void
    {
        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            $this->notFound('User not found');
        }

        $validated = $this->validate([
            'username' => 'string|max:50|unique:users,username,' . $id,
            'email' => 'required|string|max:255|email|unique:users,email,' . $id,
            'password' => 'required|string|max:255',
            'role' => 'string|max:50',
            'status' => 'string|max:20',
            'email_verified_at' => 'email',
            'remember_token' => 'string|max:100'
        ]);

        $this->model->update($id, $validated);

        $this->success(
            $this->model->find($id),
            'User updated successfully'
        );
    }

    /**
     * Delete user
     * DELETE /users/{id}
     */
    public function destroy(): void
    {
        $id = $this->request->param('id');
        $user = $this->model->find($id);

        if (!$user) {
            $this->notFound('User not found');
        }

        $this->model->delete($id);

        $this->success(null, 'User deleted successfully');
    }
}
