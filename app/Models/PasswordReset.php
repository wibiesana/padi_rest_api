<?php

namespace App\Models;

use Wibiesana\Padi\Core\ActiveRecord;

class PasswordReset extends ActiveRecord
{
    protected string $table = 'password_resets';
    protected string|array $primaryKey = 'id';

    protected array $fillable = [
        'email',
        'token',
        'expires_at',
        'created_by'
    ];

    /**
     * Find valid token
     */
    public function findValidToken(string $email, string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email 
                AND token = :token 
                AND expires_at > :now 
                LIMIT 1";

        $results = $this->query($sql, [
            'email' => $email,
            'token' => $token,
            'now' => date('Y-m-d H:i:s')
        ]);

        return $results[0] ?? null;
    }

    /**
     * Delete all tokens for an email
     */
    public function deleteByEmail(string $email): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE email = :email";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute(['email' => $email]);
    }
}
