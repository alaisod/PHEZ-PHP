<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้ | PH.EASY Admin</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css?v=<?= file_exists(FCPATH . 'assets/css/bulma.min.css') ? filemtime(FCPATH . 'assets/css/bulma.min.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(FCPATH . 'assets/css/theme.css') ? filemtime(FCPATH . 'assets/css/theme.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= file_exists(FCPATH . 'assets/css/admin.css') ? filemtime(FCPATH . 'assets/css/admin.css') : '1' ?>">
</head>
<body class="theme-bg">
    <section class="section">
        <div class="container">
            <div class="box theme-card">
                <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                    <div>
                        <h1 class="title has-text-warning">จัดการผู้ใช้</h1>
                        <p class="subtitle has-text-light">
                            <a href="/admin" class="has-text-warning">&larr; กลับสู่หน้าหลัก</a>
                        </p>
                    </div>
                    <div>
                        <a href="/admin/users/create" class="button is-warning">+ เพิ่มผู้ใช้</a>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="notification is-success is-light"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="notification is-danger is-light"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <?php if (empty($users)) : ?>
                    <div class="has-text-centered has-text-light py-6">
                        <p class="is-size-5">ยังไม่มีผู้ใช้</p>
                    </div>
                <?php else : ?>
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>บทบาท</th>
                                    <th>วันที่สร้าง</th>
                                    <th>อัปเดตล่าสุด</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; ?>
                                <?php foreach ($users as $user) : ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><strong><?= esc($user['username']) ?></strong></td>
                                        <td>
                                            <?php if ($user['role'] === 'admin') : ?>
                                                <span class="tag is-warning">Admin</span>
                                            <?php elseif ($user['role'] === 'editor') : ?>
                                                <span class="tag is-info">Editor</span>
                                            <?php else : ?>
                                                <span class="tag is-light">Viewer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="is-size-7"><?= esc(date('d/m/Y H:i', strtotime($user['created_at']))) ?></td>
                                        <td class="is-size-7"><?= esc(date('d/m/Y H:i', strtotime($user['updated_at']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>
