<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

/**
 * Example Controller with Role-Based Access Control
 * 
 * This demonstrates best practices for handling different roles:
 * - Admin: Full access
 * - Teacher: Can manage students, view reports
 * - Student: Can only view/update own data
 */
class ExampleRBACController extends Controller
{
    private User $model;

    public function __construct(?\Core\Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Example 1: Only admin can access
     * GET /admin/dashboard
     */
    public function adminDashboard(): void
    {
        // Method 1: Using helper
        $this->requireRole('admin');

        // Admin-only logic
        $stats = [
            'total_users' => $this->model::findQuery()->count(),
            'total_teachers' => $this->model::findQuery()->where('role = :role', ['role' => 'teacher'])->count(),
            'total_students' => $this->model::findQuery()->where('role = :role', ['role' => 'student'])->count(),
        ];

        $this->single($stats, 'Admin dashboard data');
    }

    /**
     * Example 2: Admin or Teacher can access
     * GET /reports
     */
    public function viewReports(): void
    {
        // Method 2: Multiple roles
        $this->requireAnyRole(['admin', 'teacher'], 'Only admin and teachers can view reports');

        // Logic accessible by admin and teacher
        $reports = []; // Fetch reports

        $this->success($reports, 'Reports retrieved successfully');
    }

    /**
     * Example 3: Student can only view own data, Teacher can view their students, Admin can view all
     * GET /students/{id}
     */
    public function viewStudent(): void
    {
        $studentId = (int)$this->request->param('id');
        $student = $this->model->find($studentId);

        if (!$student || $student['role'] !== 'student') {
            $this->notFound('Student not found');
        }

        // Check permissions
        if ($this->isAdmin()) {
            // Admin can view any student
        } elseif ($this->hasRole('teacher')) {
            // Teacher can view their students (you'd check teacher_id relationship here)
            // For demo purposes, we allow all teachers
        } elseif ($this->hasRole('student')) {
            // Student can only view their own data
            if (!$this->isOwner($studentId)) {
                $this->forbidden('Students can only view their own data');
            }
        } else {
            $this->forbidden('You do not have permission to view student data');
        }

        // Remove sensitive data based on role
        $response = $student;
        if (!$this->isAdmin()) {
            unset($response['password']);
            unset($response['remember_token']);
        }

        $this->single($response, 'Student data retrieved');
    }

    /**
     * Example 4: Student can only update own data, Teacher cannot update students, Admin can update all
     * PUT /students/{id}
     */
    public function updateStudent(): void
    {
        $studentId = (int)$this->request->param('id');
        $student = $this->model->find($studentId);

        if (!$student || $student['role'] !== 'student') {
            $this->notFound('Student not found');
        }

        // Permission check
        if ($this->hasRole('student')) {
            // Student can only update their own data
            if (!$this->isOwner($studentId)) {
                $this->forbidden('You can only update your own data');
            }
        } elseif ($this->hasRole('teacher')) {
            // Teacher CANNOT update student data (only view)
            $this->forbidden('Teachers cannot update student data');
        } elseif (!$this->isAdmin()) {
            // Must be admin, teacher, or the student themselves
            $this->forbidden('You do not have permission to update student data');
        }

        $validated = $this->validate([
            'username' => 'max:50|unique:users,username,' . $studentId,
            'email' => 'max:255|email|unique:users,email,' . $studentId,
            'password' => 'max:255',
        ]);

        // Students cannot change their role
        if ($this->hasRole('student')) {
            unset($validated['role'], $validated['status']);
        }

        // Remove password if empty
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $this->model->update($studentId, $validated);

        $this->success(
            $this->model->find($studentId),
            'Student updated successfully'
        );
    }

    /**
     * Example 5: Only Admin can update teacher data
     * PUT /teachers/{id}
     */
    public function updateTeacher(): void
    {
        $teacherId = (int)$this->request->param('id');
        $teacher = $this->model->find($teacherId);

        if (!$teacher || $teacher['role'] !== 'teacher') {
            $this->notFound('Teacher not found');
        }

        // Only admin can update teacher data
        if ($this->hasRole('teacher')) {
            // Teacher can update their own profile
            if (!$this->isOwner($teacherId)) {
                $this->forbidden('Teachers can only update their own profile');
            }
        } elseif ($this->hasRole('student')) {
            // Student cannot update any teacher
            $this->forbidden('Students cannot update teacher data');
        } elseif (!$this->isAdmin()) {
            $this->forbidden('Only administrators can update teacher data');
        }

        $validated = $this->validate([
            'username' => 'max:50|unique:users,username,' . $teacherId,
            'email' => 'max:255|email|unique:users,email,' . $teacherId,
            'password' => 'max:255',
        ]);

        // Teachers cannot change their own role
        if ($this->hasRole('teacher') && !$this->isAdmin()) {
            unset($validated['role'], $validated['status']);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $this->model->update($teacherId, $validated);

        $this->success(
            $this->model->find($teacherId),
            'Teacher updated successfully'
        );
    }

    /**
     * Example 6: Complex permission - Admin and Teacher can create students, Students cannot
     * POST /students
     */
    public function createStudent(): void
    {
        // Admin and Teacher can create students
        $this->requireAnyRole(['admin', 'teacher'], 'Only admin and teachers can create students');

        $validated = $this->validate([
            'username' => 'max:50|unique:users,username',
            'email' => 'required|max:255|email|unique:users,email',
            'password' => 'required|max:255',
        ]);

        // Force role to student
        $validated['role'] = 'student';
        $validated['status'] = 'active';

        $id = $this->model->create($validated);

        $this->success([
            'id' => $id,
            'student' => $this->model->find($id)
        ], 'Student created successfully', 201);
    }

    /**
     * Example 7: Different data based on role
     * GET /users
     */
    public function listUsers(): void
    {
        $this->requireAnyRole(['admin', 'teacher']);

        $query = $this->model::findQuery();

        // Teachers can only see students, not other teachers or admins
        if ($this->hasRole('teacher')) {
            $query->where('role = :role', ['role' => 'student']);
        }

        // Admin can see everyone (no filter)

        $users = $query->all();

        // Filter sensitive data for non-admins
        if (!$this->isAdmin()) {
            $users = array_map(function ($user) {
                unset($user['password'], $user['remember_token']);
                return $user;
            }, $users);
        }

        $this->collection($users, [], 'Users retrieved successfully');
    }

    /**
     * Example 8: Using manual role check with if-else
     * GET /profile
     */
    public function getProfile(): void
    {
        $userId = $this->request->user->user_id ?? null;

        if (!$userId) {
            $this->unauthorized('Authentication required');
        }

        $user = $this->model->find($userId);

        if (!$user) {
            $this->notFound('User not found');
        }

        // Different response based on role
        $response = $user;
        unset($response['password'], $response['remember_token']);

        if ($this->isAdmin()) {
            $response['permissions'] = ['all'];
        } elseif ($this->hasRole('teacher')) {
            $response['permissions'] = ['view_students', 'create_students', 'view_reports'];
        } elseif ($this->hasRole('student')) {
            $response['permissions'] = ['view_own_data', 'update_own_data'];
        }

        $this->success($response, 'Profile retrieved successfully');
    }
}
