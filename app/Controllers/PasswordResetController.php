<?php

namespace App\Controllers;

use App\Models\PasswordReset;
use App\Models\User;
use Wibiesana\Padi\Core\Controller;
use Wibiesana\Padi\Core\Env;

class PasswordResetController extends Controller
{
    /**
     * Request password reset link
     * POST /api/password/forgot
     */
    public function forgotPassword()
    {
        $validated = $this->validate([
            'email' => 'required|email'
        ]);

        $userModel = new User();
        $user = $userModel->findByEmail($validated['email']);

        if (!$user) {
            // Secretive: don't reveal if user exists for security
            return [
                'message' => 'If your email is registered, you will receive a password reset link shortly.'
            ];
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + 3600; // 1 hour expiration in unix timestamp

        $passwordResetModel = new PasswordReset();
        $passwordResetModel->deleteByEmail($validated['email']);
        PasswordReset::create([
            'email' => $validated['email'],
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        // In a real app, send an email here
        // For now, return token in dev/testing if debug is on
        $data = [
            'message' => 'If your email is registered, you will receive a password reset link shortly.'
        ];

        if (Env::get('APP_DEBUG') === 'true') {
            $data['debug_token'] = $token;
        }

        return $data;
    }

    /**
     * Reset password using token
     * POST /api/password/reset
     */
    public function passwordReset()
    {
        $validated = $this->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ]);

        if ($validated['password'] !== $validated['password_confirmation']) {
            throw new \Exception('Password confirmation does not match', 422);
        }

        $passwordResetModel = new PasswordReset();
        $resetEntry = $passwordResetModel->findValidToken($validated['email'], $validated['token']);

        if (!$resetEntry) {
            throw new \Exception('Invalid or expired reset token', 400);
        }

        $userModel = new User();
        $user = $userModel->findByEmail($validated['email']);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        // Update password (ActiveRecord beforeSave will handle hashing)
        User::update($user['id'], [
            'password' => $validated['password']
        ]);

        // Clean up tokens
        $passwordResetModel->deleteByEmail($validated['email']);

        return [
            'message' => 'Password has been safely reset. You can now login with your new password.'
        ];
    }
}
