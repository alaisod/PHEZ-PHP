<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\PeopleModel;

class Register extends BaseController
{
    public function index(): string
    {
        helper('form');

        return view('register', [
            'errors' => session()->getFlashdata('errors') ?? [],
            'showWelcome' => session()->getFlashdata('showWelcome') ?? false,
            'member' => session()->getFlashdata('member') ?? null,
            'memberCode' => session()->getFlashdata('memberCode') ?? null,
            'welcomeMessage' => session()->getFlashdata('welcomeMessage') ?? null,
        ]);
    }

    public function submit()
    {
        helper('form');

        $rules = [
            'shop_name' => 'required|string|min_length[2]|max_length[150]',
            'shop_telephone' => 'required|string|min_length[6]|max_length[30]',
            'contact_name' => 'required|string|min_length[2]|max_length[150]',
            'geo_location' => 'required|string',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/register')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $memberModel = new MemberModel();
        $peopleModel = new PeopleModel();

        $maxCode = (int) ($memberModel->selectMax('member_code')->first()['member_code'] ?? 0);
        $nextCode = max(1000, $maxCode + 1);


        $lineId = trim((string) $this->request->getPost('line_id'));
        $lineId = $lineId === '' ? null : $lineId;
        $lineDisplayName = trim((string) $this->request->getPost('line_display_name'));
        $lineDisplayName = $lineDisplayName === '' ? null : $lineDisplayName;

        if ($lineId !== null) {
            $existingPerson = $peopleModel->where('line_id', $lineId)->first();
            if ($existingPerson) {
                $member = $memberModel->select('members.*, people.person_name, people.telephone, people.line_id, people.line_display_name')
                    ->join('people', 'people.id = members.contact_id', 'left')
                    ->where('members.contact_id', $existingPerson['id'])
                    ->first();

                return redirect()->to('/register')
                    ->with('showWelcome', true)
                    ->with('member', $member)
                    ->with('memberCode', $member['member_code'] ?? null)
                    ->with('welcomeMessage', 'คุณลงทะเบียนไปแล้ว');
            }
        }

        $peopleModel->insert([
            'person_name' => $this->request->getPost('contact_name'),
            'telephone' => $this->request->getPost('shop_telephone'),
            'line_id' => $lineId,
            'line_display_name' => $lineDisplayName,
        ]);
        $contactId = $peopleModel->getInsertID();

        $data = [
            'member_code' => $nextCode,
            'shop_name' => $this->request->getPost('shop_name'),
            'shop_telephone' => $this->request->getPost('shop_telephone'),
            'address' => null,
            'geo_location' => $this->request->getPost('geo_location'),
            'contact_id' => $contactId,
        ];

        $memberModel->insert($data);

        $member = $memberModel->select('members.*, people.person_name, people.telephone, people.line_id, people.line_display_name')
            ->join('people', 'people.id = members.contact_id', 'left')
            ->where('members.id', $memberModel->getInsertID())
            ->first();

        return redirect()->to('/register')
            ->with('showWelcome', true)
            ->with('member', $member)
            ->with('memberCode', $nextCode)
            ->with('welcomeMessage', 'ลงทะเบียนสำเร็จ');
    }

    public function checkLine()
    {
        $lineId = trim((string) $this->request->getGet('line_id'));
        if ($lineId === '') {
            return $this->response->setJSON(['exists' => false]);
        }

        $memberModel = new MemberModel();
        $member = $memberModel->select('members.*, people.person_name, people.telephone, people.line_id, people.line_display_name')
            ->join('people', 'people.id = members.contact_id', 'left')
            ->where('people.line_id', $lineId)
            ->first();

        if (! $member) {
            return $this->response->setJSON(['exists' => false]);
        }

        return $this->response->setJSON([
            'exists' => true,
            'member' => $member,
        ]);
    }

    public function welcome(int $memberCode): string
    {
        $memberModel = new MemberModel();
        $member = $memberModel->select('members.*, people.person_name, people.telephone, people.line_id, people.line_display_name')
            ->join('people', 'people.id = members.contact_id', 'left')
            ->where('member_code', $memberCode)
            ->first();

        return view('welcome', [
            'member' => $member,
            'memberCode' => $memberCode,
        ]);
    }
}
