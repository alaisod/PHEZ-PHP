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

                <form method="post" action="/admin/save" enctype="multipart/form-data">
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
                        <label class="label has-text-warning">รูปหน้าร้าน</label>
                        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;flex:1">
                            <div>
                                <?php if ($member && ! empty($member['store_photo'])) : ?>
                                    <figure class="image is-96x96" style="overflow:hidden">
                                        <img src="/uploads/store_photos/<?= esc($member['store_photo']) ?>" alt="Store" style="border-radius:12px;object-fit:cover;border:2px solid #3a3a5c;width:100%;height:100%;display:block">
                                    </figure>
                                    <input type="hidden" name="existing_photo" value="<?= esc($member['store_photo']) ?>">
                                <?php else : ?>
                                    <div style="width:96px;height:96px;border-radius:12px;border:2px dashed #3a3a5c;display:flex;align-items:center;justify-content:center;color:#7a7a7a;font-size:2rem">&#x1F4F7;</div>
                                <?php endif; ?>
                            </div>
                            <div style="min-width:200px">
                                <div class="file is-warning is-small">
                                    <label class="file-label">
                                        <input class="file-input" type="file" name="store_photo" accept="image/*">
                                        <span class="file-cta">
                                            <span class="file-icon">&#x1F4C1;</span>
                                            <span class="file-label">เลือกรูปภาพ</span>
                                        </span>
                                    </label>
                                </div>
                                <p class="help has-text-grey mt-1">รองรับ JPG, PNG, WebP ขนาดไม่เกิน 5MB</p>
                            </div>
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
