<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class User extends ActiveRecord
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'username', 'email', 'password', 'role', 'status', 'email_verified_at', 'remember_token', 'last_login_at'
    ];
    
    protected array $hidden = ['password'];

    /**
     * Audit fields detected: created_at, updated_at
     * These will be auto-populated by ActiveRecord
     */
    protected bool $useAudit = true;
    
    /**
     * Timestamp format: 'unix'
     * 'datetime' = Y-m-d H:i:s (DATETIME/TIMESTAMP columns)
     * 'unix' = integer timestamp (INT/BIGINT columns)
     */
    protected string $timestampFormat = 'unix';
    
    /**
     * Search users
     */
    public function search(string $keyword): array
    {
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE username LIKE :keyword
                   OR email LIKE :keyword2
                   OR status LIKE :keyword3
                   OR email_verified_at LIKE :keyword4
                LIMIT 100";
        
        return $this->query($sql, [
            'keyword' => $searchTerm,
            'keyword2' => $searchTerm,
            'keyword3' => $searchTerm,
            'keyword4' => $searchTerm
        ]);
    }
}
