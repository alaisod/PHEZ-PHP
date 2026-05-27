<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table = 'members';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $updateField = 'updated_at';
    protected $createdField = 'created_at';

    protected $allowedFields = [
        'member_code',
        'shop_name',
        'shop_telephone',
        'address',
        'geo_location',
        'contact_id',
    ];
}
