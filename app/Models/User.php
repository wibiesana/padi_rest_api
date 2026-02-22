<?php

namespace App\Models;

use Wibiesana\Padi\Core\ActiveRecord;
use Wibiesana\Padi\Core\Query;


class User extends ActiveRecord
{
    protected string $table = 'users';
    protected string|array $primaryKey = 'id';

    protected array $fillable = [
        'username',
        'email',
        'password',
        'role',
        'status',
        'email_verified_at',
        'remember_token',
        'last_login_at'
    ];

    protected array $hidden = ['password'];

    /**
     * Audit fields detected: created_at, updated_at, created_by, updated_by
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;

    /**
     * Timestamp format: 'datetime'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'datetime';

    /**
     * Search users
     */
    public function search(string $keyword): array
    {
        return Query::find()
            ->from($this->table)
            ->where([
                'OR',
                ['LIKE', 'username', "%$keyword%"],
                ['LIKE', 'email', "%$keyword%"],
                ['LIKE', 'status', "%$keyword%"],
                ['LIKE', 'email_verified_at', "%$keyword%"]
            ])
            ->limit(100)
            ->all();
    }
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
            $data['status'] = $data['status'] ?? 1;
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
            'status' => 'integer',
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
        // Superadmin is always active, or status is 1/'active'
        if (isset($user['role']) && $user['role'] === 'superadmin') {
            return true;
        }
        return isset($user['status']) && ($user['status'] == 1 || $user['status'] === 'active');
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
