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
    public function register(): void
    {
        $validated = $this->validate([
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);

        // Additional password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/', $validated['password'])) {
            $this->error('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)', 422);
        }

        // Check password confirmation
        if ($validated['password'] !== $validated['password_confirmation']) {
            $this->error('Password confirmation does not match', 422);
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
            'subject' => 'Welcome to ' . ($_ENV['APP_NAME'] ?? 'Our API'),
            'body' => 'Thank you for registering!'
        ]);

        $this->success([
            'id' => $user['id'],
            'user_id' => $user['id'],
            'user' => $user,
            'token' => $token
        ], 'Registration successful. Welcome email will be sent shortly.', 201);
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(): void
    {
        $validated = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find active user only
        $user = $this->model->findActiveByEmail($validated['email']);

        if (!$user) {
            $this->unauthorized('Invalid credentials or account is inactive');
        }

        // Get user with password for verification
        $stmt = $this->model->query(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $validated['email']]
        );

        $userWithPassword = $stmt[0] ?? null;

        if (!$userWithPassword || !password_verify($validated['password'], $userWithPassword['password'])) {
            $this->unauthorized('Invalid credentials');
        }

        // Check user status
        if (!$this->model->isActive($userWithPassword)) {
            $this->unauthorized('Your account is inactive. Please contact support.');
        }

        // Update last login
        $this->model->updateLastLogin($user['id']);

        $token = \Core\Auth::generateToken([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ]);

        // Refresh user data with updated last_login_at
        $user = $this->model->find($user['id']);

        $this->success([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    /**
     * Get authenticated user
     * GET /api/auth/me
     */
    public function me(): void
    {
        if (!$this->request->user) {
            $this->unauthorized('Not authenticated');
        }

        $this->success([
            'user' => $this->request->user
        ]);
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout(): void
    {
        // In a stateless JWT system, logout is typically handled client-side
        // You can implement token blacklisting here if needed

        $this->success(null, 'Logout successful');
    }

    /**
     * Forgot Password - Send reset email
     * POST /auth/forgot-password
     */
    public function forgotPassword(): void
    {
        $validated = $this->validate([
            'email' => 'required|email'
        ]);

        $user = $this->model->findByEmail($validated['email']);

        // Always return success to prevent email enumeration
        if (!$user) {
            $this->success(null, 'If the email exists, a password reset link has been sent.');
            return;
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset token in database
        $db = \Core\Database::getInstance()->getConnection();

        // Delete old tokens for this email
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute(['email' => $validated['email']]);

        // Insert new token
        $stmt = $db->prepare("
            INSERT INTO password_resets (email, token, expires_at) 
            VALUES (:email, :token, :expires_at)
        ");
        $stmt->execute([
            'email' => $validated['email'],
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // Generate reset URL
        $resetUrl = ($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000') . '/reset-password?token=' . $token . '&email=' . urlencode($validated['email']);

        // Send email
        $emailBody = "
            <h2>Password Reset Request</h2>
            <p>Hello,</p>
            <p>You requested to reset your password. Click the link below to reset your password:</p>
            <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <br>
            <p>Best regards,<br>" . ($_ENV['APP_NAME'] ?? 'Our API') . "</p>
        ";

        // Push email job to queue
        \Core\Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $validated['email'],
            'subject' => 'Password Reset Request - ' . ($_ENV['APP_NAME'] ?? 'Our API'),
            'body' => $emailBody
        ]);

        $this->success(null, 'If the email exists, a password reset link has been sent.');
    }

    /**
     * Reset Password
     * POST /auth/reset-password
     */
    public function resetPassword(): void
    {
        $validated = $this->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);

        // Additional password complexity validation
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]/', $validated['password'])) {
            $this->error('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#)', 422);
        }

        // Check password confirmation
        if ($validated['password'] !== $validated['password_confirmation']) {
            $this->error('Password confirmation does not match', 422);
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
            $this->error('Invalid or expired reset token', 400);
        }

        // Find user
        $user = $this->model->findByEmail($validated['email']);
        if (!$user) {
            $this->error('User not found', 404);
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
            <p>Best regards,<br>" . ($_ENV['APP_NAME'] ?? 'Our API') . "</p>
        ";

        \Core\Queue::push(\App\Jobs\SendEmailJob::class, [
            'email' => $validated['email'],
            'subject' => 'Password Reset Successful - ' . ($_ENV['APP_NAME'] ?? 'Our API'),
            'body' => $emailBody
        ]);

        $this->success(null, 'Password has been reset successfully. You can now login with your new password.');
    }
}
