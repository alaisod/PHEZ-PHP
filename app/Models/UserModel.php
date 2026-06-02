<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $updatedField     = 'updated_at';
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'username',
        'password',
    ];

    protected $validationRules = [
        'username' => 'required|string|min_length[3]|max_length[100]|is_unique[users.username]',
        'password' => 'required|string|min_length[6]',
    ];
}
