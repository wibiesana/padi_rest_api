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
        'expires_at'
    ];

    /**
     * Find a valid (non-expired) reset token for the given email
     */
    public function findValidToken(string $email, string $token): ?array
    {
        $results = static::find()
            ->where([
                'email' => $email,
                'token' => $token,
                ['expires_at', '>', date('Y-m-d H:i:s')]
            ])
            ->one();

        return $results ?: null;
    }

    /**
     * Delete all reset tokens for an email
     */
    public function deleteByEmail(string $email): bool
    {
        return static::deleteAll(['email' => $email]) >= 0;
    }
}
