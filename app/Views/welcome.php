<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="/assets/css/bulma.min.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <style>
        .glow {
            animation: glow 1.5s ease-in-out infinite alternate;
        }
        @keyframes glow {
            from { text-shadow: 0 0 6px rgba(255, 204, 0, 0.5); }
            to { text-shadow: 0 0 16px rgba(255, 204, 0, 0.9); }
        }
        .pop {
            animation: pop 0.8s ease;
        }
        @keyframes pop {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="theme-bg">
<section class="section">
    <div class="container">
        <div class="box theme-card has-text-centered pop">
            <h1 class="title has-text-warning glow">ยินดีต้อนรับ!</h1>
            <p class="subtitle has-text-light">ขอบคุณสำหรับการลงทะเบียน</p>
            <div class="notification theme-notice">
                <p>Member Code</p>
                <p class="is-size-3 has-text-weight-bold has-text-warning glow"><?= esc($memberCode) ?></p>
            </div>
            <button class="button is-warning" id="closeLiff">ปิด</button>
        </div>
    </div>
</section>

<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script>
    const member = <?= json_encode($member ?? null) ?>;

    async function sendLineMessage() {
        if (!member || !member.line_id) {
            return;
        }
        try {
            await liff.init({ liffId: 'xx2010178930-H6pNBOQl' });
            if (!liff.isLoggedIn()) {
                return;
            }
            if (!liff.isInClient()) {
                return;
            }
            const text = `สมัครสมาชิกสำเร็จ\nMember Code: ${member.member_code}\nร้าน: ${member.shop_name}\nผู้ติดต่อ: ${member.person_name}\nเบอร์โทร: ${member.telephone}`;
            await liff.sendMessages([{ type: 'text', text }]);
        } catch (error) {
            console.error('Send message failed', error);
        }
    }

    document.getElementById('closeLiff').addEventListener('click', () => {
        if (typeof liff !== 'undefined' && liff.isInClient()) {
            liff.closeWindow();
        } else {
            window.location.href = '/';
        }
    });

    sendLineMessage();
</script>
</body>
</html>
