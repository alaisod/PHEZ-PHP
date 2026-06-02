<?php

namespace App\Controllers;

use CodeIgniter\Database\Exceptions\DatabaseException;

class Setup extends BaseController
{
    public function index()
    {
        $messages = [];
        $hasError = false;

        // ── Detect: database already set up? ──
        $dbConnected = false;
        $dbName      = '';
        try {
            $dbCheck = db_connect();
            $dbConnected = $dbCheck->tableExists('users') || $dbCheck->tableExists('members') || $dbCheck->tableExists('people');
            $dbCheck->close();
        } catch (\Throwable $e) {
            // Not connected yet — first-time setup
        }

        // ── Step 1: Create database if not exists ──
        if ($dbConnected) {
            $dbConfig = config('Database');
            $dbName   = $dbConfig->default['database'] ?? '';
        } else {
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

                    if (! $exists) {
                        $charset = $default['charset'] ?? 'utf8mb4';
                        $collate = $default['DBCollat'] ?? 'utf8mb4_general_ci';
                        $db->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET `{$charset}` COLLATE `{$collate}`");
                    }

                    $db->close();
                } catch (DatabaseException $e) {
                    // Some shared hosts require a default database to connect — proceed anyway
                }
            } catch (\Throwable $e) {
                $messages[] = '✗ เกิดข้อผิดพลาด: ' . esc($e->getMessage());
                $hasError = true;
            }
        }

        // ── Step 2: Run migrations (always — picks up new migration files) ──
        if (! $hasError) {
            try {
                $migrator = service('migrations');
                $migrator->latest();
            } catch (\Throwable $e) {
                $messages[] = '✗ Migrate ล้มเหลว: ' . esc($e->getMessage());
                $hasError = true;
            }
        }

        // ── Step 3: Seed admin (insert directly via db) ──
        if (! $hasError) {
            try {
                $db = db_connect();

                if ($db->tableExists('users')) {
                    $adminExists = $db->table('users')->where('username', 'admin')->countAllResults() > 0;

                    if (! $adminExists) {
                        $db->table('users')->insert([
                            'username'   => 'admin',
                            'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $messages[] = '✗ Seed ล้มเหลว: ' . esc($e->getMessage());
            }
        }

        // ── Result: show errors or redirect to admin ──
        if ($hasError) {
            return view('setup/result', [
                'messages' => $messages,
                'hasError' => true,
            ]);
        }

        return redirect()->to('/admin');
    }
}
