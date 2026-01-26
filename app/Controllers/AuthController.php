<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    private User $model;

    public function __construct(?\Core\Request $request = null)
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

        $token = \Core\Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);

        // Push Welcome Email Job to Queue
        \Core\Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $user['email'],
            'subject' => 'Welcome to ' . (\Core\Env::get('APP_NAME', 'Our API')),
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
        $stmt = $this->model->query(
            "SELECT * FROM users WHERE {$field} = :{$field}",
            [$field => $validated['username']]
        );

        $user = $stmt[0] ?? null;

        // Use consistent error message to prevent timing attacks
        if (!$user || !password_verify($validated['password'], $user['password'])) {
            throw new \Exception('Invalid credentials', 401);
        }

        // Check user status
        if ($user['status'] !== 'active') {
            throw new \Exception('Account is not active', 401);
        }

        // Update last login
        $now = date('Y-m-d H:i:s');
        $this->model->updateLastLogin($user['id']);
        $user['last_login_at'] = $now; // Update in memory to avoid extra query

        // Check if remember me is requested
        $rememberMe = isset($validated['remember_me']) &&
            in_array(strtolower($validated['remember_me']), ['true', '1', 'yes', 'on']);

        // Set token expiration based on remember me
        $expiration = $rememberMe ? (365 * 24 * 60 * 60) : 3600; // 365 days (1 year) for mobile apps, 1 hour default

        $token = \Core\Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ], $expiration);

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

        return [
            'user' => $this->request->user
        ];
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
        $token = \Core\Auth::generateToken([
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
        $db = \Core\Database::getInstance()->getConnection();

        // Delete old tokens for this email
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute(['email' => $user['email']]);

        // Insert new token
        $stmt = $db->prepare("
            INSERT INTO password_resets (email, token, expires_at) 
            VALUES (:email, :token, :expires_at)
        ");
        $stmt->execute([
            'email' => $user['email'],
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // Generate reset URL
        $resetUrl = (\Core\Env::get('FRONTEND_URL', 'http://localhost:3000')) . '/reset-password?token=' . $token . '&email=' . urlencode($user['email']);

        // Send email
        $emailBody = "
            <h2>Password Reset Request</h2>
            <p>Hello,</p>
            <p>You requested to reset your password. Click the link below to reset your password:</p>
            <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <br>
            <p>Best regards,<br>" . (\Core\Env::get('APP_NAME', 'Our API')) . "</p>
        ";

        // Push email job to queue
        \Core\Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $user['email'],
            'subject' => 'Password Reset Request - ' . (\Core\Env::get('APP_NAME', 'Our API')),
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
        $db = \Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM password_resets 
            WHERE email = :email 
            AND token = :token 
            AND expires_at > NOW()
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([
            'email' => $validated['email'],
            'token' => $validated['token']
        ]);
        $resetRecord = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$resetRecord) {
            throw new \Exception('Invalid or expired reset token', 400);
        }

        // Find user
        $user = $this->model->findByEmail($validated['email']);
        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        // Update password
        $hashedPassword = password_hash($validated['password'], PASSWORD_DEFAULT);
        $updateStmt = $db->prepare("UPDATE users SET password = :password WHERE email = :email");
        $updateStmt->execute([
            'password' => $hashedPassword,
            'email' => $validated['email']
        ]);

        // Delete used token
        $deleteStmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $deleteStmt->execute(['email' => $validated['email']]);

        // Send confirmation email
        $emailBody = "
            <h2>Password Reset Successful</h2>
            <p>Hello,</p>
            <p>Your password has been successfully reset.</p>
            <p>If you didn't make this change, please contact us immediately.</p>
            <br>
            <p>Best regards,<br>" . (\Core\Env::get('APP_NAME', 'Our API')) . "</p>
        ";

        \Core\Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $validated['email'],
            'subject' => 'Password Reset Successful - ' . (\Core\Env::get('APP_NAME', 'Our API')),
            'body' => $emailBody
        ]);

        return [
            'message' => 'Password has been reset successfully. You can now login with your new password.'
        ];
    }
}
