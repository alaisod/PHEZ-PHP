<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้งระบบ | PH.EASY</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css?v=<?= file_exists(FCPATH . 'assets/css/bulma.min.css') ? filemtime(FCPATH . 'assets/css/bulma.min.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(FCPATH . 'assets/css/theme.css') ? filemtime(FCPATH . 'assets/css/theme.css') : '1' ?>">
    <style>
        .setup-section {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
        }
        .setup-icon {
            font-size: 4rem;
        }
    </style>
</head>
<body class="theme-bg">
    <section class="section setup-section">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-half-tablet is-two-thirds-desktop">
                    <div class="box theme-card">
                        <div class="has-text-centered mb-5">
                            <div class="setup-icon has-text-<?= $hasError ? 'danger' : 'success' ?> mb-3">
                                <?= $hasError ? '&#x26A0;' : '&#x2705;' ?>
                            </div>
                            <h1 class="title has-text-warning">ติดตั้งระบบ</h1>
                            <p class="subtitle has-text-light">PH.EASY Setup</p>
                        </div>

                        <div class="notification <?= $hasError ? 'is-danger' : 'is-success' ?> is-light">
                            <p class="has-text-weight-semibold mb-2">
                                <?= $hasError ? 'การติดตั้งมีข้อผิดพลาด' : 'ติดตั้งระบบสำเร็จ' ?>
                            </p>
                        </div>

                        <ul class="setup-log">
                            <?php foreach ($messages as $msg) : ?>
                                <li class="py-2 has-text-light" style="border-bottom:1px solid #2a2a2a"><?= $msg ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="has-text-centered mt-5">
                            <?php if ($hasError) : ?>
                                <a href="/setup" class="button is-warning">ลองอีกครั้ง</a>
                            <?php else : ?>
                                <a href="/login" class="button is-warning">ไปที่หน้า Login</a>
                                <p class="has-text-grey is-size-7 mt-2">Username: <strong class="has-text-warning">admin</strong> / Password: <strong class="has-text-warning">admin123</strong></p>
                            <?php endif; ?>
                        </div>

                        <div class="has-text-centered mt-4">
                            <a href="/" class="has-text-grey is-size-7">กลับสู่หน้าแรก</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
