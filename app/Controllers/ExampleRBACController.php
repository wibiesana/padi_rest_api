<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\User;

/**
 * Simple RBAC Controller Example
 * 
 * Demonstrates three common RBAC patterns:
 * 1. Admin-only access
 * 2. Role-based access (admin + teacher)
 * 3. Self-access (view/edit own data)
 */
class ExampleRBACController extends Controller
{
    private User $model;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Example 1: Admin-only access
     * GET /admin/stats
     */
    public function getStats()
    {
        $this->requireRole('admin');

        return [
            'total_users' => $this->model::findQuery()->count(),
            'active_users' => $this->model::findQuery()->where('status = :status', ['status' => 1])->count(),
            'total_teachers' => $this->model::findQuery()->where('role = :role', ['role' => 'teacher'])->count(),
            'total_students' => $this->model::findQuery()->where('role = :role', ['role' => 'student'])->count(),
        ];
    }

    /**
     * Example 2: Admin or Teacher access
     * GET /users/list
     */
    public function listUsers()
    {
        $this->requireAnyRole(['admin', 'teacher']);

        $query = $this->model::findQuery();

        // Teachers can only see students
        if ($this->hasRole('teacher')) {
            $query->where('role = :role', ['role' => 'student']);
        }

        $users = $query->all();

        // Remove sensitive data for non-admins
        if (!$this->isAdmin()) {
            $users = array_map(function ($user) {
                unset($user['password'], $user['remember_token']);
                return $user;
            }, $users);
        }

        return $users;
    }

    /**
     * Example 3: Self-access (view own profile)
     * GET /my-profile
     */
    public function getMyProfile()
    {
        $userId = $this->request->user->user_id ?? null;

        if (!$userId) {
            throw new \Exception('Authentication required', 401);
        }

        $user = $this->model->find($userId);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        unset($user['password'], $user['remember_token']);

        return $user;
    }

    /**
     * Example 4: Update own profile (self-access with restrictions)
     * PUT /my-profile
     */
    public function updateMyProfile()
    {
        $userId = $this->request->user->user_id ?? null;

        if (!$userId) {
            throw new \Exception('Authentication required', 401);
        }

        $validated = $this->validate([
            'username' => 'max:50|unique:users,username,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
        ]);

        // Prevent role/status changes by non-admin
        if (!$this->isAdmin()) {
            unset($validated['role'], $validated['status']);
        }

        $this->model->update($userId, $validated);

        return $this->model->find($userId);
    }

    /**
     * Example 5: Student management (admin + teacher)
     * POST /students
     */
    public function createStudent()
    {
        $this->requireAnyRole(['admin', 'teacher']);

        $validated = $this->validate([
            'username' => 'required|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $validated['role'] = 'student';
        $validated['status'] = 1;

        $id = $this->model->create($validated);

        $this->setStatusCode(201);
        return $this->model->find($id);
    }
}
