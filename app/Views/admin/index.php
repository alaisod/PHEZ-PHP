<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก | PH.EASY Admin</title>
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
                        <h1 class="title has-text-warning">จัดการสมาชิก</h1>
                        <p class="subtitle has-text-light">PH.EASY Admin Dashboard</p>
                    </div>
                    <div class="is-flex is-align-items-center" style="gap:0.5rem">
                        <a href="/admin/create" class="button is-warning">+ เพิ่มสมาชิก</a>
                        <a href="/logout" class="button is-light is-small">ออกจากระบบ</a>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="notification is-success is-light"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="notification is-danger is-light"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>

                <!-- Search -->
                <form method="get" action="/admin" class="mb-4">
                    <div class="field has-addons">
                        <div class="control is-expanded">
                            <input class="input" type="text" name="q" placeholder="ค้นหา ร้าน, รหัส, เบอร์, ผู้ติดต่อ, LINE ID..." value="<?= esc($search) ?>">
                        </div>
                        <div class="control">
                            <button type="submit" class="button is-warning">ค้นหา</button>
                        </div>
                        <?php if ($search !== '') : ?>
                            <div class="control">
                                <a href="/admin" class="button is-light">ล้าง</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (empty($members)) : ?>
                    <div class="has-text-centered has-text-light py-6">
                        <p class="is-size-5"><?= $search !== '' ? 'ไม่พบผลการค้นหา' : 'ยังไม่มีข้อมูลสมาชิก' ?></p>
                        <?php if ($search !== '') : ?>
                            <a href="/admin" class="button is-light mt-3">กลับไปทั้งหมด</a>
                        <?php else : ?>
                            <a href="/admin/create" class="button is-warning is-light mt-3">เพิ่มสมาชิกแรก</a>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <p class="has-text-grey is-size-7 mb-2">พบ <?= count($members) ?> รายการ</p>
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>รหัส</th>
                                    <th>ชื่อร้าน</th>
                                    <th>เบอร์โทร</th>
                                    <th>ผู้ติดต่อ</th>
                                    <th>LINE ID</th>
                                    <th>พิกัด</th>
                                    <th>ที่อยู่</th>
                                    <th>วันที่</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = ($pager->getCurrentPage() - 1) * $perPage + 1; ?>
                                <?php foreach ($members as $member) : ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><strong><?= esc($member['member_code']) ?></strong></td>
                                        <td><?= esc($member['shop_name']) ?></td>
                                        <td><?= esc($member['shop_telephone']) ?></td>
                                        <td><?= esc($member['person_name'] ?? '-') ?></td>
                                        <td class="is-size-7"><?= esc($member['line_id'] ?? '-') ?></td>
                                        <td class="is-size-7"><?= esc($member['geo_location']) ?></td>
                                        <td class="is-size-7"><?= esc($member['address'] ?? '-') ?></td>
                                        <td class="is-size-7"><?= esc(date('d/m/Y', strtotime($member['created_at']))) ?></td>
                                        <td class="has-text-nowrap">
                                            <a href="/admin/edit/<?= $member['id'] ?>" class="button is-small is-warning is-light">แก้ไข</a>
                                            <button type="button" class="button is-small is-danger is-light btn-delete" data-id="<?= $member['id'] ?>" data-name="<?= esc($member['shop_name']) ?>">ลบ</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="is-flex is-justify-content-space-between is-align-items-center mt-4">
                        <div class="is-flex is-align-items-center">
                            <span class="has-text-grey is-size-7 mr-2">แสดง</span>
                            <div class="select is-small">
                                <select id="perPageSelect" onchange="window.location.href='/admin?q=<?= urlencode($search) ?>&per_page='+this.value">
                                    <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10</option>
                                    <option value="20" <?= $perPage === 20 ? 'selected' : '' ?>>20</option>
                                    <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100</option>
                                </select>
                            </div>
                            <span class="has-text-grey is-size-7 ml-2">รายการ</span>
                        </div>
                        <?= $pager->links('default', 'admin_pagination') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head theme-card">
                <p class="modal-card-title has-text-warning">ยืนยันการลบ</p>
                <button class="delete" aria-label="close" id="closeDeleteModal"></button>
            </header>
            <section class="modal-card-body theme-card">
                <p class="has-text-light">คุณแน่ใจหรือไม่ที่จะลบ <strong id="deleteName" class="has-text-warning"></strong>?</p>
                <p class="has-text-grey is-size-7 mt-2">การกระทำนี้ไม่สามารถย้อนกลับได้</p>
            </section>
            <footer class="modal-card-foot theme-card">
                <button class="button" id="cancelDelete">ยกเลิก</button>
                <form method="post" action="" id="deleteForm" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="button is-danger" id="confirmDelete">ลบ</button>
                </form>
            </footer>
        </div>
    </div>

    <script src="/assets/js/admin.js?v=<?= file_exists(FCPATH . 'assets/js/admin.js') ? filemtime(FCPATH . 'assets/js/admin.js') : '1' ?>"></script>
</body>
</html>
