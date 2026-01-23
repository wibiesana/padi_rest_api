<?php

namespace App\Models\Base;

use Core\ActiveRecord;

class User extends ActiveRecord
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'status',
        'email_verified_at',
        'remember_token',
        'last_login_at'
    ];

    protected array $hidden = ['password'];

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
                LIMIT 100";

        return $this->query($sql, [
            'keyword' => $searchTerm,
            'keyword2' => $searchTerm,
            'keyword3' => $searchTerm
        ]);
    }
}
