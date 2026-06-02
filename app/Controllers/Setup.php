<?php

namespace App\Controllers;

use App\Database\Seeds\AdminSeeder;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Setup extends BaseController
{
    public function index()
    {
        $messages = [];
        $hasError = false;

        // ── Guard: tables already exist? Require ?force=1 to re-run ──
        try {
            $dbCheck = db_connect();
            if ($dbCheck->tableExists('users') && $dbCheck->tableExists('members') && $dbCheck->tableExists('people')) {
                return view('setup/result', [
                    'messages' => [
                        'ℹ ระบบได้ติดตั้งเรียบร้อยแล้ว',
                        '— ไปที่ <a href="/login" class="has-text-warning">หน้า Login</a> เพื่อเข้าสู่ระบบ',
                        '— Username: <strong class="has-text-warning">admin</strong> / Password: <strong class="has-text-warning">admin123</strong>',
                    ],
                    'hasError' => false,
                ]);
            }
            $dbCheck->close();
        } catch (\Throwable $e) {
            // Database not connected yet — proceed with setup
        }

        // ── Step 1: Create database if not exists ──
        try {
            $dbConfig = config('Database');
            $default  = $dbConfig->default;

            $dbName = $default['database'];
            if (empty($dbName)) {
                throw new \RuntimeException('ไม่พบชื่อ database ใน Config');
            }

            // Try connecting without a database first (to create it)
            $noDbConfig             = $default;
            $noDbConfig['database'] = '';
            $noDbConfig['DBDebug']  = false;

            try {
                $db = db_connect($noDbConfig);

                $exists = $db->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $db->escape($dbName))->getRow();

                if ($exists) {
                    $messages[] = '✓ Database <strong>' . esc($dbName) . '</strong> มีอยู่แล้ว';
                } else {
                    $charset = $default['charset'] ?? 'utf8mb4';
                    $collate = $default['DBCollat'] ?? 'utf8mb4_general_ci';
                    $db->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET `{$charset}` COLLATE `{$collate}`");
                    $messages[] = '✓ สร้าง Database <strong>' . esc($dbName) . '</strong> สำเร็จ';
                }

                $db->close();
            } catch (DatabaseException $e) {
                // Some shared hosts require a default database to connect
                $messages[] = '⚠ ไม่สามารถเชื่อมต่อโดยไม่ระบุ database — จะใช้การเชื่อมต่อปกติ';
            }
        } catch (\Throwable $e) {
            $messages[] = '✗ เกิดข้อผิดพลาด: ' . esc($e->getMessage());
            $hasError = true;
        }

        // ── Step 2: Run migrations ──
        if (! $hasError) {
            try {
                $migrator = service('migrations');
                $result   = $migrator->latest();

                if ($result === null || $result === true) {
                    $messages[] = '⚠ ไม่มีการ migrate ใหม่ — ตารางมีอยู่แล้ว';
                } else {
                    $messages[] = '✓ Migrate สำเร็จ (batch #' . $result . ')';
                }
            } catch (\Throwable $e) {
                $messages[] = '✗ Migrate ล้มเหลว: ' . esc($e->getMessage());
                $hasError = true;
            }
        } else {
            $messages[] = '⏭ ข้าม Migrate — เนื่องจากขั้นตอนก่อนหน้ามีข้อผิดพลาด';
        }

        // ── Step 3: Seed admin (only if users table is empty) ──
        if (! $hasError) {
            try {
                $db = db_connect();

                if (! $db->tableExists('users')) {
                    $messages[] = '⏭ ข้าม Seed — ตาราง users ยังไม่มี';
                } else {
                    $adminExists = $db->table('users')->where('username', 'admin')->countAllResults() > 0;

                    if ($adminExists) {
                        $messages[] = 'ℹ Admin user มีอยู่แล้ว — ข้ามการ seed';
                    } else {
                        $seeder = new AdminSeeder();
                        $seeder->run();
                        $messages[] = '✓ Seed Admin user (admin / admin123) สำเร็จ';
                    }
                }
            } catch (\Throwable $e) {
                $messages[] = '✗ Seed ล้มเหลว: ' . esc($e->getMessage());
            }
        } else {
            $messages[] = '⏭ ข้าม Seed — เนื่องจากขั้นตอนก่อนหน้ามีข้อผิดพลาด';
        }

        // ── Show result page ──
        return view('setup/result', [
            'messages' => $messages,
            'hasError' => $hasError,
        ]);
    }
}
