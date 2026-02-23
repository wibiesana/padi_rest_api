<?php

namespace App\Controllers;

use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Request;
use Wibiesana\Padi\Core\Auth;
use Wibiesana\Padi\Core\Queue;
use Wibiesana\Padi\Core\Env;
use App\Models\User;

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
}
