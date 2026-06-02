<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table            = 'members';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $updatedField     = 'updated_at';
    protected $createdField     = 'created_at';

    protected $allowedFields = [
        'member_code',
        'shop_name',
        'shop_telephone',
        'address',
        'geo_location',
        'contact_id',
    ];

    protected $validationRules = [
        'shop_name'      => 'required|string|min_length[2]|max_length[150]',
        'shop_telephone' => 'required|string|min_length[6]|max_length[30]',
        'geo_location'   => 'required|string|max_length[120]',
        'contact_id'     => 'permit_empty|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'shop_name' => [
            'required'   => 'กรุณากรอกชื่อร้าน',
            'min_length' => 'ชื่อร้านต้องมีอย่างน้อย 2 ตัวอักษร',
            'max_length' => 'ชื่อร้านต้องไม่เกิน 150 ตัวอักษร',
        ],
        'shop_telephone' => [
            'required'   => 'กรุณากรอกเบอร์โทรศัพท์ร้าน',
            'min_length' => 'เบอร์โทรศัพท์ต้องมีอย่างน้อย 6 ตัวอักษร',
            'max_length' => 'เบอร์โทรศัพท์ต้องไม่เกิน 30 ตัวอักษร',
        ],
        'geo_location' => [
            'required' => 'กรุณาเลือกพิกัดร้าน',
        ],
    ];

    /**
     * Get a member record joined with the people (contact) table.
     *
     * @param array|string $where Condition array or string for the WHERE clause
     * @return array|null
     */
    public function getMemberWithContact($where): ?array
    {
        $result = $this->select('members.*, people.person_name, people.telephone, people.line_id')
            ->join('people', 'people.id = members.contact_id', 'left')
            ->where($where)
            ->first();

        return $result ?: null;
    }
}
