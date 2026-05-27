<?php

namespace App\Models;

use CodeIgniter\Model;

class PeopleModel extends Model
{
    protected $table = 'people';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $updateField = 'updated_at';
    protected $createdField = 'created_at';

    protected $allowedFields = [
        'person_name',
        'telephone',
        'line_id',
        'line_display_name',
    ];
}
