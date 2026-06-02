<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | PH.EASY Admin</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css?v=<?= file_exists(FCPATH . 'assets/css/bulma.min.css') ? filemtime(FCPATH . 'assets/css/bulma.min.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= file_exists(FCPATH . 'assets/css/theme.css') ? filemtime(FCPATH . 'assets/css/theme.css') : '1' ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= file_exists(FCPATH . 'assets/css/admin.css') ? filemtime(FCPATH . 'assets/css/admin.css') : '1' ?>">
    <style>
        .login-section {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body class="theme-bg">
    <section class="section login-section">
        <div class="container">
            <div class="columns is-centered">
                <div class="column is-half-tablet is-one-third-desktop">
                    <div class="box theme-card">
                        <div class="has-text-centered mb-4">
                            <h1 class="title has-text-warning">PH.EASY</h1>
                            <p class="subtitle has-text-light">Admin Login</p>
                        </div>

                        <?php if (session()->getFlashdata('success')) : ?>
                            <div class="notification is-success is-light"><?= esc(session()->getFlashdata('success')) ?></div>
                        <?php endif; ?>

                        <?php if (! empty($errors)) : ?>
                            <div class="notification is-danger">
                                <ul>
                                    <?php foreach ($errors as $error) : ?>
                                        <li><?= esc($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="/login/attempt">
                            <?= csrf_field() ?>

                            <div class="field">
                                <label class="label has-text-warning">ชื่อผู้ใช้</label>
                                <div class="control">
                                    <input class="input" type="text" name="username" value="<?= old('username') ?>" required autofocus>
                                </div>
                            </div>

                            <div class="field">
                                <label class="label has-text-warning">รหัสผ่าน</label>
                                <div class="control">
                                    <input class="input" type="password" name="password" required>
                                </div>
                            </div>

                            <div class="field mt-5">
                                <div class="control">
                                    <button class="button is-warning is-fullwidth is-medium" type="submit">เข้าสู่ระบบ</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
