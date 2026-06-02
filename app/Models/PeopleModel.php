<?php

namespace App\Models;

use CodeIgniter\Model;

class PeopleModel extends Model
{
    protected $table            = 'people';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $updatedField     = 'updated_at';
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'person_name',
        'telephone',
        'line_id',
        'line_display_name',
    ];

    protected $validationRules = [
        'person_name'      => 'required|string|min_length[2]|max_length[150]',
        'telephone'        => 'permit_empty|string|min_length[6]|max_length[30]',
        'line_id'          => 'permit_empty|is_unique[people.line_id]|max_length[150]',
        'line_display_name' => 'permit_empty|max_length[200]',
    ];

    protected $validationMessages = [
        'person_name' => [
            'required'   => 'กรุณากรอกชื่อผู้ติดต่อ',
            'min_length' => 'ชื่อผู้ติดต่อต้องมีอย่างน้อย 2 ตัวอักษร',
            'max_length' => 'ชื่อผู้ติดต่อต้องไม่เกิน 150 ตัวอักษร',
        ],
        'telephone' => [
            'min_length' => 'เบอร์โทรศัพท์ต้องมีอย่างน้อย 6 ตัวอักษร',
            'max_length' => 'เบอร์โทรศัพท์ต้องไม่เกิน 30 ตัวอักษร',
        ],
        'line_id' => [
            'is_unique' => 'LINE ID นี้ลงทะเบียนแล้ว',
        ],
    ];
}
