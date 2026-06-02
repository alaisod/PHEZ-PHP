<?php
$assetVersion = static function (string $path): string {
    $path = ltrim($path, '/');
    $fullPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $mtime = is_file($fullPath) ? @filemtime($fullPath) : false;

    return $mtime === false ? '1' : (string) $mtime;
};

$assetUrl = static function (string $path) use ($assetVersion): string {
    $path = ltrim($path, '/');

    return base_url($path) . '?v=' . $assetVersion($path);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนร้านค้า | PH.EASY</title>
    <link rel="stylesheet" href="<?= esc($assetUrl('assets/css/bulma.min.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc($assetUrl('assets/css/theme.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc($assetUrl('assets/css/register.css'), 'attr') ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
</head>

<body class="theme-bg <?= $showWelcome ? 'welcome-active' : '' ?>">
<section class="section">
    <div class="container">
        <div class="box theme-card">
            <div class="is-flex is-justify-content-space-between is-align-items-center">
                <div>
                    <h1 class="title has-text-warning">ลงทะเบียนร้านค้า</h1>
                    <p class="subtitle has-text-light">PH.EASY Registration</p>
                </div>
                <div class="has-text-centered">
                    <figure class="image is-48x48 is-inline-block">
                        <img id="profileImage" src="<?= esc(base_url('assets/img/user-mockup.svg'), 'attr') ?>" class="is-rounded" alt="Profile">
                    </figure>
                    <p class="has-text-light is-size-7 mt-0" id="profileName">Guest</p>
                </div>
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

            <form method="post" action="<?= esc(site_url('register/submit'), 'attr') ?>" id="registerForm" class="<?= $showWelcome ? 'is-hidden' : '' ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="field">
                    <label class="label has-text-warning">ชื่อร้าน</label>
                    <div class="control">
                        <input class="input" type="text" name="shop_name" value="<?= old('shop_name') ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label has-text-warning">เบอร์โทรศัพท์ร้าน</label>
                    <div class="control">
                        <input class="input" type="tel" name="shop_telephone" value="<?= old('shop_telephone') ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label has-text-warning">ชื่อผู้ติดต่อ</label>
                    <div class="control">
                        <input class="input" type="text" name="contact_name" value="<?= old('contact_name') ?>" required>
                    </div>
                </div>

                <div class="field">
                    <div class="is-flex is-justify-content-space-between is-align-items-center">
                        <label class="label has-text-warning mb-0">พิกัดร้าน</label>
                        <p class="has-text-light is-size-7" id="geoPreview">-</p>
                    </div>
                    <div class="buttons mt-2">
                        <button type="button" class="button is-warning is-light" id="autoLocate">ใช้พิกัดปัจจุบัน</button>
                        <button type="button" class="button is-warning" id="openMap">เลือกตำแหน่งบนแผนที่</button>
                    </div>
                    <input type="hidden" id="geoDisplay" name="geo_location" value="<?= old('geo_location') ?>" required>
                </div>


                <div class="modal" id="mapModal">
                    <div class="modal-background"></div>
                    <div class="modal-card">
                        <header class="modal-card-head theme-card">
                            <p class="modal-card-title has-text-warning">เลือกตำแหน่งร้าน</p>
                            <button class="delete" aria-label="close" id="closeMap"></button>
                        </header>
                        <section class="modal-card-body theme-card">
                            <div id="map" class="map-box"></div>
                        </section>
                        <footer class="modal-card-foot theme-card">
                            <button type="button" class="button is-warning" id="confirmMap">ยืนยันตำแหน่ง</button>
                        </footer>
                    </div>
                </div>

                <!-- ── Store Photo ── -->
                <div class="field">
                    <label class="label has-text-warning">รูปหน้าร้าน</label>
                    <div class="photo-upload-area" id="photoArea">
                        <div class="photo-placeholder" id="photoPlaceholder">
                            <span class="photo-icon">&#x1F4F7;</span>
                            <span class="photo-text">เพิ่มรูปหน้าร้าน</span>
                        </div>
                        <img class="photo-preview is-hidden" id="photoPreview" alt="Store photo">
                        <div class="photo-overlay is-hidden" id="photoOverlay">
                            <button type="button" class="button is-small is-danger" id="removePhoto">&#x2715; ลบรูป</button>
                        </div>
                    </div>
                    <div class="buttons is-centered mt-2" style="gap:0.5rem">
                        <button type="button" class="button is-warning is-light is-small" id="takePhotoBtn">
                            &#x1F4F7; ถ่ายรูป
                        </button>
                        <button type="button" class="button is-warning is-light is-small" id="choosePhotoBtn">
                            &#x1F4C2; เลือกรูปภาพ
                        </button>
                    </div>
                    <input type="file" id="storePhotoInput" name="store_photo" accept="image/*" capture="environment" class="is-hidden">
                    <p class="help has-text-grey is-size-7">รองรับ JPG, PNG, WebP ขนาดไม่เกิน 5MB</p>
                </div>

                <input type="hidden" name="line_id" id="lineId">
                <input type="hidden" name="line_display_name" id="lineDisplayName">
                <input type="hidden" name="is_liff" id="isLiff" value="0">

                <div class="field mt-5">
                    <div class="control">
                        <button class="button is-warning is-fullwidth is-medium" type="submit" id="submitBtn">Register</button>
                    </div>
                </div>
            </form>
            <p class="has-text-centered version-label">v1.0.1</p>

            <div class="notification is-warning is-light is-hidden" id="alreadyRegistered">
                <p class="has-text-weight-semibold">คุณลงทะเบียนไปแล้ว</p>
                <p class="is-size-7" id="registeredInfo"></p>
                <button type="button" class="button is-warning mt-3" id="closeRegistered">ปิด</button>
            </div>

            <div class="<?= $showWelcome ? '' : 'is-hidden' ?> welcome-screen" id="welcomeSection">
                <div class="box theme-card has-text-centered pop">
                    <h1 class="title has-text-warning glow">ยินดีต้อนรับ!</h1>
                    <p class="subtitle has-text-light"><?= esc($welcomeMessage ?? 'ขอบคุณสำหรับการลงทะเบียน') ?></p>
                    <?php if ($member && ! empty($member['store_photo'])) : ?>
                        <div class="welcome-photo-box">
                            <img src="/uploads/store_photos/<?= esc($member['store_photo']) ?>" alt="Store" class="welcome-photo">
                        </div>
                    <?php endif; ?>
                    <div class="notification theme-notice">
                        <p>Member Code</p>
                        <p class="is-size-3 has-text-weight-bold has-text-warning glow"><?= esc($memberCode ?? '') ?></p>
                    </div>
                    <button class="button is-warning" id="closeLiff">ปิด</button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Data bootstrap: pass PHP data to external JS
    window._member = <?= json_encode($member ?? null) ?>;
    window._showWelcome = <?= $showWelcome ? 'true' : 'false' ?>;
</script>
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="<?= esc($assetUrl('assets/js/register.js'), 'attr') ?>"></script>
</body>
</html>
