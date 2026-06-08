<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มผู้ใช้ | PH.EASY Admin</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css?v=<?= file_exists(FCPATH . 'assets/css/bulma.min.css') ? filemtime(FCPATH . 'assets/css/bulma.min.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(FCPATH . 'assets/css/theme.css') ? filemtime(FCPATH . 'assets/css/theme.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= file_exists(FCPATH . 'assets/css/admin.css') ? filemtime(FCPATH . 'assets/css/admin.css') : '1' ?>">
</head>
<body class="theme-bg">
    <section class="section">
        <div class="container">
            <div class="box theme-card">
                <div class="mb-4">
                    <h1 class="title has-text-warning">เพิ่มผู้ใช้ใหม่</h1>
                    <p class="subtitle has-text-light">
                        <a href="/admin/users" class="has-text-warning">&larr; กลับสู่หน้าจัดการผู้ใช้</a>
                    </p>
                </div>

                <?php if (! empty($errors)) : ?>
                    <div class="notification is-danger">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="/admin/users/save">
                    <?= csrf_field() ?>

                    <div class="field">
                        <label class="label has-text-warning">ชื่อผู้ใช้</label>
                        <div class="control">
                            <input class="input" type="text" name="username" value="<?= old('username') ?>" required placeholder="เช่น editor01">
                        </div>
                        <p class="help has-text-grey">อย่างน้อย 3 ตัวอักษร</p>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">รหัสผ่าน</label>
                        <div class="control">
                            <input class="input" type="password" name="password" required placeholder="รหัสผ่านอย่างน้อย 6 ตัวอักษร">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">บทบาท</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="role" required>
                                    <option value="">-- เลือกบทบาท --</option>
                                    <option value="editor" <?= old('role') === 'editor' ? 'selected' : '' ?>>Editor (แก้ไขได้, ลบไม่ได้)</option>
                                    <option value="viewer" <?= old('role') === 'viewer' ? 'selected' : '' ?>>Viewer (ดูข้อมูลอย่างเดียว)</option>
                                    <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin (สิทธิ์เต็ม)</option>
                                </select>
                            </div>
                        </div>
                        <p class="help has-text-grey">
                            <strong class="has-text-warning">Admin</strong> — จัดการสมาชิกและผู้ใช้ได้ทั้งหมด<br>
                            <strong class="has-text-info">Editor</strong> — เพิ่ม/แก้ไขสมาชิก, ลบไม่ได้<br>
                            <strong class="has-text-light">Viewer</strong> — ดูข้อมูลอย่างเดียว
                        </p>
                    </div>

                    <div class="field mt-5">
                        <div class="control">
                            <button class="button is-warning is-fullwidth is-medium" type="submit">เพิ่มผู้ใช้</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
