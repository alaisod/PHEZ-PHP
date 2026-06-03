<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\PeopleModel;
use CodeIgniter\HTTP\RedirectResponse;

class Admin extends BaseController
{
    public function index(): string
    {
        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);

        $search = trim($this->request->getGet('q') ?? '');
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);
        $perPage = max(10, min(100, $perPage));

        $memberModel->select('members.*, people.person_name, people.telephone, people.line_id')
            ->join('people', 'people.id = members.contact_id', 'left');

        if ($search !== '') {
            $memberModel->groupStart()
                ->like('members.shop_name', $search)
                ->orLike('members.member_code', $search)
                ->orLike('members.shop_telephone', $search)
                ->orLike('people.person_name', $search)
                ->orLike('people.line_id', $search)
                ->groupEnd();
        }

        $members = $memberModel->orderBy('members.created_at', 'DESC')
            ->paginate($perPage);

        return view('admin/index', [
            'members'  => $members,
            'pager'    => $memberModel->pager,
            'search'   => $search,
            'perPage'  => $perPage,
        ]);
    }

    public function create(): string
    {
        return view('admin/form', [
            'member' => null,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function edit(int $id)
    {
        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);
        $member = $memberModel->getMemberWithContact(['members.id' => $id]);

        if ($member === null) {
            return redirect()->to('/admin')->with('error', 'ไม่พบข้อมูลสมาชิก');
        }

        return view('admin/form', [
            'member' => $member,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function save(): RedirectResponse
    {
        $rules = [
            'shop_name'      => 'required|string|min_length[2]|max_length[150]',
            'shop_telephone' => 'required|string|min_length[6]|max_length[30]',
            'contact_name'   => 'required|string|min_length[2]|max_length[150]',
            'geo_location'   => 'required|string',
            'address'        => 'permit_empty|string|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // ── Handle store photo upload ──
        $storePhoto = null;
        $photoFile = $this->request->getFile('store_photo');

        if ($photoFile !== null && $photoFile->isValid() && ! $photoFile->hasMoved()) {
            $validationRule = [
                'store_photo' => [
                    'rules'  => 'is_image[store_photo]|max_size[store_photo,5120]|mime_in[store_photo,image/jpg,image/jpeg,image/png,image/webp]',
                    'errors' => [
                        'is_image' => 'ไฟล์ที่อัปโหลดต้องเป็นรูปภาพ',
                        'max_size' => 'รูปภาพต้องมีขนาดไม่เกิน 5MB',
                        'mime_in'  => 'รองรับเฉพาะไฟล์ JPG, PNG, WebP เท่านั้น',
                    ],
                ],
            ];

            if ($this->validate($validationRule)) {
                $storePhoto = $photoFile->getRandomName();
                $uploadPath = FCPATH . 'uploads/store_photos';

                if (! is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $photoFile->move($uploadPath, $storePhoto);
            }
        }

        $memberId = $this->request->getPost('id');
        $memberId = $memberId ? (int) $memberId : null;

        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);
        /** @var PeopleModel $peopleModel */
        $peopleModel = model(PeopleModel::class);

        $lineId = trim((string) $this->request->getPost('line_id'));
        $lineId = $lineId === '' ? null : $lineId;
        $lineDisplayName = trim((string) $this->request->getPost('line_display_name'));
        $lineDisplayName = $lineDisplayName === '' ? null : $lineDisplayName;

        $db = db_connect();

        try {
            $db->transStart();

            if ($memberId === null) {
                // ── Create new ──
                $maxCode  = (int) ($memberModel->selectMax('member_code')->first()['member_code'] ?? 0);
                $nextCode = max(1000, $maxCode + 1);

                $peopleModel->insert([
                    'person_name'       => $this->request->getPost('contact_name'),
                    'telephone'         => $this->request->getPost('shop_telephone'),
                    'line_id'           => $lineId,
                    'line_display_name' => $lineDisplayName,
                ]);
                $contactId = $peopleModel->getInsertID();

                $memberModel->insert([
                    'member_code'    => $nextCode,
                    'shop_name'      => $this->request->getPost('shop_name'),
                    'shop_telephone' => $this->request->getPost('shop_telephone'),
                    'address'        => $this->request->getPost('address') ?: null,
                    'geo_location'   => $this->request->getPost('geo_location'),
                    'store_photo'    => $storePhoto ?: ($this->request->getPost('existing_photo') ?: null),
                    'contact_id'     => $contactId,
                ]);
            } else {
                // ── Update existing ──
                $member = $memberModel->find($memberId);
                if (! $member) {
                    throw new \RuntimeException('Member not found');
                }

                // Delete old photo if new one uploaded
                if ($storePhoto !== null && ! empty($member['store_photo'])) {
                    $oldPath = FCPATH . 'uploads/store_photos/' . $member['store_photo'];
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // Skip model validation on update — controller validation already ran
                $peopleModel->skipValidation(true);
                $peopleModel->update($member['contact_id'], [
                    'person_name'       => $this->request->getPost('contact_name'),
                    'telephone'         => $this->request->getPost('shop_telephone'),
                    'line_id'           => $lineId,
                    'line_display_name' => $lineDisplayName,
                ]);

                $memberModel->skipValidation(true);
                $memberModel->update($memberId, [
                    'shop_name'      => $this->request->getPost('shop_name'),
                    'shop_telephone' => $this->request->getPost('shop_telephone'),
                    'address'        => $this->request->getPost('address') ?: null,
                    'geo_location'   => $this->request->getPost('geo_location'),
                    'store_photo'    => $storePhoto ?: ($this->request->getPost('existing_photo') ?: null),
                ]);
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()
                ->withInput()
                ->with('errors', ['เกิดข้อผิดพลาด กรุณาลองอีกครั้ง']);
        }

        return redirect()->to('/admin')->with('success', $memberId === null ? 'เพิ่มสมาชิกสำเร็จ' : 'อัปเดตสมาชิกสำเร็จ');
    }

    public function delete(int $id): RedirectResponse
    {
        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);
        /** @var PeopleModel $peopleModel */
        $peopleModel = model(PeopleModel::class);

        $member = $memberModel->find($id);

        if ($member === null) {
            return redirect()->to('/admin')->with('error', 'ไม่พบข้อมูลสมาชิก');
        }

        $db = db_connect();

        try {
            $db->transStart();

            $memberModel->delete($id);

            if ($member['contact_id']) {
                $peopleModel->delete($member['contact_id']);
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->to('/admin')->with('error', 'เกิดข้อผิดพลาดในการลบข้อมูล');
        }

        return redirect()->to('/admin')->with('success', 'ลบสมาชิกสำเร็จ');
    }

    public function map(): string
    {
        return view('admin/map');
    }

    public function mapData(): \CodeIgniter\HTTP\ResponseInterface
    {
        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);

        $members = $memberModel
            ->select('members.id, members.shop_name, members.member_code, members.geo_location, members.store_photo, members.shop_telephone, members.address')
            ->where('members.geo_location IS NOT NULL')
            ->where('members.geo_location !=', '')
            ->findAll();

        return $this->response
            ->setContentType('application/json')
            ->setJSON($members);
    }
}
