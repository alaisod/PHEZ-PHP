<?php

namespace App\Controllers;

use App\Models\MemberModel;
use App\Models\PeopleModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Register extends BaseController
{
    public function index(): string
    {
        return view('register', [
            'errors'        => session()->getFlashdata('errors') ?? [],
            'showWelcome'   => session()->getFlashdata('showWelcome') ?? false,
            'member'        => session()->getFlashdata('member') ?? null,
            'memberCode'    => session()->getFlashdata('memberCode') ?? null,
            'welcomeMessage' => session()->getFlashdata('welcomeMessage') ?? null,
        ]);
    }

    public function submit(): RedirectResponse
    {
        $rules = [
            'shop_name'      => 'required|string|min_length[2]|max_length[150]',
            'shop_telephone' => 'required|string|min_length[6]|max_length[30]',
            'contact_name'   => 'required|string|min_length[2]|max_length[150]',
            'geo_location'   => 'required|string',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/register')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);
        /** @var PeopleModel $peopleModel */
        $peopleModel = model(PeopleModel::class);

        $maxCode  = (int) ($memberModel->selectMax('member_code')->first()['member_code'] ?? 0);
        $nextCode = max(1000, $maxCode + 1);

        $lineId = trim((string) $this->request->getPost('line_id'));
        $lineId = $lineId === '' ? null : $lineId;
        $lineDisplayName = trim((string) $this->request->getPost('line_display_name'));
        $lineDisplayName = $lineDisplayName === '' ? null : $lineDisplayName;

        // Check for duplicate LINE registration
        if ($lineId !== null) {
            $existingPerson = $peopleModel->where('line_id', $lineId)->first();

            if ($existingPerson !== null) {
                $member = $memberModel->getMemberWithContact(['members.contact_id' => $existingPerson['id']]);

                return redirect()->to('/register')
                    ->with('showWelcome', true)
                    ->with('member', $member)
                    ->with('memberCode', $member['member_code'] ?? null)
                    ->with('welcomeMessage', lang('Register.alreadyRegistered'));
            }
        }

        // Insert contact person and member within a transaction
        $db = db_connect();

        try {
            $db->transStart();

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
                'address'        => null,
                'geo_location'   => $this->request->getPost('geo_location'),
                'contact_id'     => $contactId,
            ]);

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->to('/register')
                ->withInput()
                ->with('errors', [lang('Register.errorOccurred')]);
        }

        $member = $memberModel->getMemberWithContact(['members.id' => $memberModel->getInsertID()]);

        return redirect()->to('/register')
            ->with('showWelcome', true)
            ->with('member', $member)
            ->with('memberCode', $nextCode)
            ->with('welcomeMessage', lang('Register.registrationSuccess'));
    }

    public function checkLine(): \CodeIgniter\HTTP\ResponseInterface
    {
        $lineId = trim((string) $this->request->getPost('line_id'));

        if ($lineId === '') {
            return $this->response->setJSON(['exists' => false]);
        }

        /** @var MemberModel $memberModel */
        $memberModel = model(MemberModel::class);
        $member = $memberModel->getMemberWithContact(['people.line_id' => $lineId]);

        if ($member === null) {
            return $this->response->setJSON(['exists' => false]);
        }

        return $this->response->setJSON([
            'exists' => true,
            'member' => $member,
        ]);
    }

}
