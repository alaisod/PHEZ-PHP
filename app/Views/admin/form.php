<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $member ? 'แก้ไขสมาชิก' : 'เพิ่มสมาชิก' ?> | PH.EASY Admin</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css?v=<?= file_exists(FCPATH . 'assets/css/bulma.min.css') ? filemtime(FCPATH . 'assets/css/bulma.min.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(FCPATH . 'assets/css/theme.css') ? filemtime(FCPATH . 'assets/css/theme.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= file_exists(FCPATH . 'assets/css/admin.css') ? filemtime(FCPATH . 'assets/css/admin.css') : '1' ?>">
</head>
<body class="theme-bg">
    <section class="section">
        <div class="container">
            <div class="box theme-card">
                <div class="mb-4">
                    <h1 class="title has-text-warning"><?= $member ? 'แก้ไขสมาชิก' : 'เพิ่มสมาชิกใหม่' ?></h1>
                    <p class="subtitle has-text-light">
                        <a href="/admin" class="has-text-warning">&larr; กลับสู่หน้าจัดการ</a>
                        <span class="mx-2 has-text-grey">|</span>
                        <a href="/logout" class="has-text-grey">ออกจากระบบ</a>
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

                <form method="post" action="/admin/save">
                    <?= csrf_field() ?>

                    <?php if ($member) : ?>
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="member_code" value="<?= $member['member_code'] ?>">
                    <?php endif; ?>

                    <div class="field">
                        <label class="label has-text-warning">ชื่อร้าน</label>
                        <div class="control">
                            <input class="input" type="text" name="shop_name" value="<?= old('shop_name', $member['shop_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">เบอร์โทรศัพท์ร้าน</label>
                        <div class="control">
                            <input class="input" type="tel" name="shop_telephone" value="<?= old('shop_telephone', $member['shop_telephone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">ชื่อผู้ติดต่อ</label>
                        <div class="control">
                            <input class="input" type="text" name="contact_name" value="<?= old('contact_name', $member['person_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">LINE ID</label>
                        <div class="control">
                            <input class="input" type="text" name="line_id" value="<?= old('line_id', $member['line_id'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">LINE Display Name</label>
                        <div class="control">
                            <input class="input" type="text" name="line_display_name" value="<?= old('line_display_name', $member['line_display_name'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">พิกัดร้าน (ละติจูด,ลองจิจูด)</label>
                        <div class="control">
                            <input class="input" type="text" name="geo_location" value="<?= old('geo_location', $member['geo_location'] ?? '') ?>" required placeholder="เช่น 16.4419,102.8350">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label has-text-warning">ที่อยู่</label>
                        <div class="control">
                            <textarea class="textarea" name="address" rows="3"><?= old('address', $member['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="field mt-5">
                        <div class="control">
                            <button class="button is-warning is-fullwidth is-medium" type="submit">
                                <?= $member ? 'บันทึกการแก้ไข' : 'เพิ่มสมาชิก' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
