<?php

namespace App\Models;

use App\Models\Base\User as BaseModel;

class User extends BaseModel
{
    /**
     * Automatically hash password and set defaults before saving
     */
    protected function beforeSave(array &$data, bool $insert): bool
    {
        // 1. Hash password if it's being set or changed
        if (isset($data['password'])) {
            // Only hash if it's not already a BCrypt hash
            if (strpos($data['password'], '$2y$') !== 0) {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
        }

        // 2. Set defaults for new records only
        if ($insert) {
            $data['status'] = $data['status'] ?? 'active';
            $data['role'] = $data['role'] ?? 'user';
        }

        return true; // Return false to stop saving
    }

    /**
     * Get validation rules for User model
     * @return array
     */
    protected function getValidationRules(): array
    {
        return [
            'username' => 'string|max:50',
            'email' => 'required|string|max:255|email',
            'password' => 'required|string|min:8|max:255',
            'role' => 'string|max:50',
            'status' => 'string|max:20|in:active,inactive,banned',
            'email_verified_at' => 'nullable',
            'remember_token' => 'string|max:100',
            'last_login_at' => 'nullable'
        ];
    }

    protected function afterSave(bool $insert, array $data): void
    {
        if ($insert) {
            // Trigger welcome email or notification after creation
        }
    }

    protected function beforeDelete($id): bool
    {
        // Prevent deleting admin users
        if ($id == 1) return false;
        return true;
    }

    /**
     * Find user by email
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $results = $this->where(['email' => $email]);
        return $results[0] ?? null;
    }

    /**
     * Find user by username
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        $results = $this->where(['username' => $username]);
        return $results[0] ?? null;
    }

    /**
     * Find active user by email
     * @param string $email
     * @return array|null
     */
    public function findActiveByEmail(string $email): ?array
    {
        $results = $this->where([
            'email' => $email,
            'status' => 'active'
        ]);
        return $results[0] ?? null;
    }

    /**
     * Find active user by username
     * @param string $username
     * @return array|null
     */
    public function findActiveByUsername(string $username): ?array
    {
        $results = $this->where([
            'username' => $username,
            'status' => 'active'
        ]);
        return $results[0] ?? null;
    }

    /**
     * Create new user
     * @param array $data
     * @return int|string - New user ID
     */
    public function createUser(array $data): int|string
    {
        // Logic is now handled by beforeSave hook
        return $this->create($data);
    }

    /**
     * Mark email as verified
     * @param int|string $userId
     * @return bool
     */
    public function markEmailAsVerified(int|string $userId): bool
    {
        return $this->update($userId, [
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update last login timestamp
     * @param int|string $userId
     * @return bool
     */
    public function updateLastLogin(int|string $userId): bool
    {
        return $this->update($userId, [
            'last_login_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Change user status
     * @param int|string $userId
     * @param string $status - active, inactive, banned, etc.
     * @return bool
     */
    public function changeStatus(int|string $userId, string $status): bool
    {
        return $this->update($userId, [
            'status' => $status
        ]);
    }

    /**
     * Check if user is active
     * @param array $user
     * @return bool
     */
    public function isActive(array $user): bool
    {
        return isset($user['status']) && $user['status'] === 'active';
    }

    /**
     * Check if email is verified
     * @param array $user
     * @return bool
     */
    public function isEmailVerified(array $user): bool
    {
        return isset($user['email_verified_at']) && $user['email_verified_at'] !== null;
    }

    /**
     * Generate remember token
     * @return string
     */
    public function generateRememberToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Set remember token for user
     * @param int|string $userId
     * @param string $token
     * @return bool
     */
    public function setRememberToken(int|string $userId, string $token): bool
    {
        return $this->update($userId, [
            'remember_token' => $token
        ]);
    }
}
