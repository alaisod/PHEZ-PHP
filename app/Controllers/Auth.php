<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
    public function login(): string
    {
        return view('admin/login', [
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function attempt(): RedirectResponse
    {
        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /** @var UserModel $userModel */
        $userModel = model(UserModel::class);

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('errors', ['ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
        }

        $role = $user['role'] ?? 'admin';

        session()->set([
            'isLoggedIn' => true,
            'userId'     => $user['id'],
            'username'   => $user['username'],
            'role'       => $role,
        ]);

        return redirect()->to('/admin')->with('success', 'ยินดีต้อนรับ ' . $user['username']);
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'ออกจากระบบสำเร็จ');
    }
}
