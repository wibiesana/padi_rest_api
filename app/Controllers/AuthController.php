<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use App\Models\User;
use Wibiesana\Padi\Core\Auth;
use Wibiesana\Padi\Core\Env;
use Wibiesana\Padi\Core\Query;
use Wibiesana\Padi\Core\Queue;

class AuthController extends Controller
{
    private User $model;

    public function __construct(?Request $request = null)
    {
        parent::__construct($request);
        $this->model = new User();
    }

    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register()
    {
        $validated = $this->validate([
            'username' => 'min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);

        // Validate password confirmation matches
        if ($validated['password'] !== $validated['password_confirmation']) {
            throw new \Exception('Password confirmation does not match', 422);
        }

        // Additional password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/', $validated['password'])) {
            throw new \Exception('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)', 422);
        }

        unset($validated['password_confirmation']);
        $validated['role'] = 'user';

        $userId = $this->model->createUser($validated);
        $user = $this->model->find($userId);

        $token = Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        // Push Welcome Email Job to Queue
        Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $user['email'],
            'subject' => 'Welcome to ' . (Env::get('APP_NAME', 'Our API')),
            'body' => 'Thank you for registering!'
        ]);

        $this->setStatusCode(201);
        return [
            'id' => $user['id'],
            'user_id' => $user['id'],
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful. Welcome email will be sent shortly.'
        ];
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login()
    {
        $validated = $this->validate([
            'username' => 'required', // Can be email or username
            'password' => 'required',
            'remember_me' => '' // Optional
        ]);

        // Determine if login is email or username
        $isEmail = filter_var($validated['username'], FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'username';

        // Whitelist field name to prevent SQL injection
        if (!in_array($field, ['email', 'username'], true)) {
            throw new \Exception('Invalid credentials', 401);
        }

        // Get user with password in one query
        $user = $this->model::findQuery()
            ->where([$field => $validated['username']])
            ->one();

        // Use consistent error message to prevent timing attacks
        if (!$user || !password_verify($validated['password'], $user['password'])) {
            throw new \Exception('Invalid credentials', 401);
        }

        // Check user status
        if (!$this->model->isActive($user)) {
            throw new \Exception('Account is not active', 401);
        }

        // Update last login
        $now = date('Y-m-d H:i:s');
        $this->model->updateLastLogin($user['id']);
        $user['last_login_at'] = $now; // Update in memory to avoid extra query

        // Check if remember me is requested
        $rememberMeInput = $validated['remember_me'] ?? null;
        if ($rememberMeInput === null) {
            $rememberMeInput = $this->request->input('remember_me');
        }

        $rememberMe = false;
        if ($rememberMeInput !== null) {
            if (is_bool($rememberMeInput)) {
                $rememberMe = $rememberMeInput;
            } else {
                $rememberMe = in_array(strtolower((string)$rememberMeInput), ['true', '1', 'yes', 'on']);
            }
        }

        // Set token expiration based on remember me
        // 1 year = 31536000 seconds
        $expiration = $rememberMe ? 31536000 : 3600;

        if (Env::get('APP_DEBUG') === 'true') {
            error_log("[Auth] Login request - User: " . $user['username'] . ", Remember Me: " . ($rememberMe ? 'YES' : 'NO') . ", Expiration: " . $expiration);
        }

        $token = Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ], (int)$expiration);

        // Generate and store remember token if requested
        $rememberToken = null;
        if ($rememberMe) {
            $rememberToken = $this->model->generateRememberToken();
            $this->model->setRememberToken($user['id'], $rememberToken);
            $user['remember_token'] = $rememberToken; // Update in memory
        }

        // Remove password from response
        unset($user['password']);

        $response = [
            'user' => $user,
            'token' => $token,
            'permissions' => $this->getUserPermissions($user['id']),
            'message' => 'Login successful'
        ];

        if ($rememberToken) {
            $response['remember_token'] = $rememberToken;
            $response['expires_in'] = 365 * 24 * 60 * 60; // 365 days (1 year) in seconds
        }

        return $response;
    }

    /**
     * Get authenticated user
     * GET /api/auth/me
     */
    public function me()
    {
        if (!$this->request->user) {
            throw new \Exception('Not authenticated', 401);
        }

        $user = $this->request->user;
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

        // Load full user record to ensure profile and other fields are there
        $userModel = new \App\Models\User();
        $userRecord = $userModel->find($userId);

        $roleId = $userRecord['role_id'] ?? $userRecord['role'] ?? (is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null));
        $permissions = $this->getUserPermissions($userId);

        $studentInfo = null;
        $teacherInfo = null;

        // Normalize role check
        $isStudent = $roleId === 'student' || (int)$roleId === 4;
        $isTeacher = $roleId === 'teacher' || (int)$roleId === 2;

        if ($isStudent) {
            $studentModel = new \App\Models\Student();
            $students = $studentModel->where(['id' => $userId]);
            if (!empty($students)) {
                $studentInfo = $students[0];
                // Get classroom
                $studentInfo['classroom'] = \App\Models\ClassroomMember::findQuery()
                    ->select('classroom_member.*, classroom.name as class_name')
                    ->innerJoin('classroom', 'classroom_member.class_id = classroom.id')
                    ->where(['classroom_member.student_id' => $userId])
                    ->one();
            }
        } else if ($isTeacher) {
            $teacherModel = new \App\Models\Teacher();
            $teachers = $teacherModel->where(['id' => $userId]);
            if (!empty($teachers)) {
                $teacherInfo = $teachers[0];
                // Check if homeroom teacher
                $teacherInfo['homeroom_class'] = \App\Models\Classroom::findQuery()
                    ->where(['teacher_id' => $userId])
                    ->one();
            }
        }

        $activeSemester = \App\Models\Semester::findQuery()->where(['status' => 1])->one();

        $mergedUser = array_merge(is_array($user) ? $user : (array)$user, $userRecord ?? []);
        // Fallback for name to make frontend code cleaner
        if (empty($mergedUser['name'])) {
            $mergedUser['name'] = $studentInfo['name'] ?? $teacherInfo['name'] ?? $mergedUser['username'] ?? null;
        }

        return [
            'user' => $mergedUser,
            'permissions' => $permissions,
            'student' => $studentInfo,
            'teacher' => $teacherInfo,
            'semester' => $activeSemester
        ];
    }

    /**
     * Helper to get all permissions for a user across all their roles
     */
    private function getUserPermissions($userId)
    {
        try {
            $roles = \App\Models\UserRole::findQuery()
                ->select('role.permissions')
                ->innerJoin('role', 'user_role.role_id = role.id')
                ->where(['user_role.user_id' => $userId])
                ->all();

            $allPermissions = [];
            foreach ($roles as $role) {
                if (empty($role['permissions'])) continue;

                $perms = json_decode($role['permissions'], true);
                if (!is_array($perms)) {
                    $perms = explode(',', $role['permissions']);
                    $perms = array_map('trim', $perms);
                }
                $allPermissions = array_merge($allPermissions, $perms);
            }

            return array_values(array_unique(array_filter($allPermissions)));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout()
    {
        // In a stateless JWT system, logout is typically handled client-side
        // You can implement token blacklisting here if needed

        return [
            'message' => 'Logout successful'
        ];
    }

    /**
     * Refresh token using remember token
     * POST /auth/refresh
     */
    public function refresh()
    {
        $validated = $this->validate([
            'remember_token' => 'required'
        ]);

        // Find user by remember token
        $users = $this->model->where(['remember_token' => $validated['remember_token']]);
        $user = $users[0] ?? null;

        if (!$user) {
            throw new \Exception('Invalid or expired remember token', 401);
        }

        // Check if user is active
        if (!$this->model->isActive($user)) {
            throw new \Exception('Your account is inactive. Please contact support.', 401);
        }

        // Generate new access token (365 days / 1 year for mobile apps)
        $token = Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ], 365 * 24 * 60 * 60);

        // Update last login
        $now = date('Y-m-d H:i:s');
        $this->model->updateLastLogin($user['id']);
        $user['last_login_at'] = $now; // Update in memory to avoid extra query

        // Remove password from response
        unset($user['password']);

        return [
            'user' => $user,
            'token' => $token,
            'remember_token' => $validated['remember_token'],
            'expires_in' => 365 * 24 * 60 * 60, // 365 days (1 year)
            'message' => 'Token refreshed successfully'
        ];
    }

    /**
     * Forgot Password - Send reset email
     * POST /auth/forgot-password
     */
    public function forgotPassword()
    {
        $validated = $this->validate([
            'login' => 'required' // Can be email or username
        ]);

        // Determine if login is email or username
        $isEmail = filter_var($validated['login'], FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $user = $this->model->findByEmail($validated['login']);
        } else {
            $user = $this->model->findByUsername($validated['login']);
        }

        // Always return success to prevent email enumeration
        if (!$user) {
            return [
                'message' => 'If the account exists, a password reset link has been sent.'
            ];
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset token in database
        // Delete old tokens for this email
        Query::find()
            ->from('password_resets')
            ->where(['email' => $user['email']])
            ->delete();

        // Insert new token
        Query::find()
            ->from('password_resets')
            ->insert([
                'email' => $user['email'],
                'token' => $token,
                'expires_at' => $expiresAt
            ]);

        // Generate reset URL
        $resetUrl = (Env::get('FRONTEND_URL', 'http://localhost:3000')) . '/reset-password?token=' . $token . '&email=' . urlencode($user['email']);

        // Send email
        $emailBody = "
            <h2>Password Reset Request</h2>
            <p>Hello,</p>
            <p>You requested to reset your password. Click the link below to reset your password:</p>
            <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <br>
            <p>Best regards,<br>" . (Env::get('APP_NAME', 'Our API')) . "</p>
        ";

        // Push email job to queue
        Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $user['email'],
            'subject' => 'Password Reset Request - ' . (Env::get('APP_NAME', 'Our API')),
            'body' => $emailBody
        ]);

        return [
            'message' => 'If the account exists, a password reset link has been sent.'
        ];
    }

    /**
     * Reset Password
     * POST /auth/reset-password
     */
    public function resetPassword()
    {
        $validated = $this->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);

        // Additional password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/', $validated['password'])) {
            throw new \Exception('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)', 422);
        }

        // Check password confirmation
        if ($validated['password'] !== $validated['password_confirmation']) {
            throw new \Exception('Password confirmation does not match', 422);
        }

        // Verify token
        $resetRecord = Query::find()
            ->from('password_resets')
            ->where(['email' => $validated['email']])
            ->andWhere(['token' => $validated['token']])
            ->andWhere('expires_at > NOW()')
            ->orderBy('created_at DESC')
            ->one();

        if (!$resetRecord) {
            throw new \Exception('Invalid or expired reset token', 400);
        }

        // Find user
        $user = $this->model::findQuery()
            ->where(['email' => $validated['email']])
            ->one();

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        // Update password
        $hashedPassword = password_hash($validated['password'], PASSWORD_DEFAULT);
        $this->model::findQuery()
            ->where(['email' => $validated['email']])
            ->update(['password' => $hashedPassword]);

        // Delete used token
        Query::find()
            ->from('password_resets')
            ->where(['email' => $validated['email']])
            ->delete();

        // Send confirmation email
        $emailBody = "
            <h2>Password Reset Successful</h2>
            <p>Hello,</p>
            <p>Your password has been successfully reset.</p>
            <p>If you didn't make this change, please contact us immediately.</p>
            <br>
            <p>Best regards,<br>" . (Env::get('APP_NAME', 'Our API')) . "</p>
        ";

        Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $validated['email'],
            'subject' => 'Password Reset Successful - ' . (Env::get('APP_NAME', 'Our API')),
            'body' => $emailBody
        ]);

        return [
            'message' => 'Password has been reset successfully. You can now login with your new password.'
        ];
    }

    /**
     * Change Password for authenticated user
     * POST /api/auth/change-password
     */
    public function changePassword()
    {
        if (!$this->request->user) {
            throw new \Exception('Not authenticated', 401);
        }

        $validated = $this->validate([
            'oldPassword' => 'required',
            'newPassword' => 'required|min:8',
            'confirmPassword' => 'required'
        ]);

        if ($validated['newPassword'] !== $validated['confirmPassword']) {
            throw new \Exception('Password confirmation does not match', 422);
        }

        $user = $this->request->user;
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);

        // Fetch user from DB using raw Query builder to ensure 'password' is not hidden/unset
        $userRecord = (new \Wibiesana\Padi\Core\Query())
            ->from('users')
            ->where(['id' => $userId])
            ->one();

        if (!$userRecord) {
            throw new \Exception('User not found', 404);
        }

        if (!password_verify($validated['oldPassword'], $userRecord['password'])) {
            throw new \Exception('Current password incorrect', 422);
        }

        // Additional password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/', $validated['newPassword'])) {
            throw new \Exception('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)', 422);
        }

        $this->model->update($userId, [
            'password' => $validated['newPassword']
        ]);

        return [
            'success' => true,
            'message' => 'Password changed successfully'
        ];
    }

    /**
     * Update profile for authenticated user
     * POST /api/user/update-profile
     */
    public function updateProfile()
    {
        if (!$this->request->user) {
            throw new \Exception('Not authenticated', 401);
        }

        $user = $this->request->user;
        $userId = is_array($user) ? ($user['id'] ?? $user['user_id'] ?? null) : ($user->id ?? $user->user_id ?? null);
        $role = is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null);

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gender' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'date_of_birth' => 'nullable',
            'photo' => 'nullable|string',
        ]);

        // 1. Update User table (email only usually)
        $this->model->update($userId, ['email' => $validated['email']]);

        // 2. Update Student or Staff table
        // Fetch full user record to check role using raw Query builder to bypass hooks/hidden fields
        $userRecord = (new \Wibiesana\Padi\Core\Query())
            ->from('users')
            ->where(['id' => $userId])
            ->one();

        $roleId = $userRecord['role_id'] ?? $userRecord['role'] ?? (is_array($user) ? ($user['role'] ?? null) : ($user->role ?? null));

        // Normalize role check
        $isStudent = $roleId === 'student' || (int)$roleId === 4;
        $isTeacher = $roleId === 'teacher' || (int)$roleId === 2;

        $profileData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'photo' => $validated['photo'] ?? null,
        ];

        if ($isStudent) {
            $studentModel = new \App\Models\Student();
            $profileData['jenis_kelamin'] = $validated['gender'] ?? null;
            $profileData['tempat_lahir'] = $validated['place_of_birth'] ?? null;
            $profileData['tanggal_lahir'] = $validated['date_of_birth'] ?? null;
            $profileData['no_telp'] = $validated['phone'] ?? null;
            $profileData['alamat'] = $validated['address'] ?? null;

            if ($studentModel::findQuery()->where(['id' => $userId])->exists()) {
                $studentModel->update($userId, $profileData);
            }
        } else if ($isTeacher) {
            $teacherModel = new \App\Models\Teacher();
            $profileData['gender'] = $validated['gender'] ?? null;
            $profileData['place_of_birth'] = $validated['place_of_birth'] ?? null;
            $profileData['date_of_birth'] = $validated['date_of_birth'] ?? null;
            $profileData['phone'] = $validated['phone'] ?? null;
            $profileData['address'] = $validated['address'] ?? null;

            if ($teacherModel::findQuery()->where(['id' => $userId])->exists()) {
                $teacherModel->update($userId, $profileData);
            }
        } else {
            $staffModel = new \App\Models\Staff();
            $profileData['gender'] = $validated['gender'] ?? null;
            $profileData['place_of_birth'] = $validated['place_of_birth'] ?? null;
            $profileData['date_of_birth'] = $validated['date_of_birth'] ?? null;
            $profileData['phone'] = $validated['phone'] ?? null;
            $profileData['address'] = $validated['address'] ?? null;

            if ($staffModel::findQuery()->where(['id' => $userId])->exists()) {
                $staffModel->update($userId, $profileData);
            }
        }

        return [
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $validated
        ];
    }
}
