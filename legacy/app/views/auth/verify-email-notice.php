<?php
$pageTitle = $title ?? 'Xác thực Email – '.SITE_NAME;
$message = htmlspecialchars((string) ($message ?? 'Mã OTP đã được gửi đến email của bạn.'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="<?= URL_ROOT ?>/public/images/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-verify-page">
    <div class="auth-verify-card">
        <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-5 d-block text-center mb-4" style="color:#2563eb;">
            <i class="fas fa-house me-1"></i> <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?>
        </a>

        <div class="auth-verify-icon warn">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <h1 class="fw-800 mb-2" style="font-size:1.4rem;color:#1f2937;">Xác thực OTP</h1>
        <p style="color:#6b7280;font-size:.9rem;line-height:1.7;">
            <?= $message ?> Nhập mã 6 chữ số để xác thực email và đăng nhập.
        </p>

        <form id="email-otp-form" class="mt-4 text-start">
            <label for="email-otp" class="form-label fw-semibold" style="font-size:.84rem;">Mã OTP</label>
            <input type="text" id="email-otp" name="otp" class="form-control text-center fw-bold fs-5"
                inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                placeholder="------" required style="letter-spacing:8px;">
            <div id="email-otp-error" class="auth-alert error mt-3 d-none"></div>
            <button class="auth-btn mt-3" type="submit">
                <i class="fas fa-shield-check me-2"></i>Xác nhận và đăng nhập
            </button>
        </form>

        <button id="resend-email-otp" class="btn btn-link mt-3 p-0 fw-semibold" type="button">
            Không nhận được mã? Gửi lại OTP
        </button>

        <div class="auth-form-footer mt-4">
            <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap"><i class="fas fa-arrow-left me-1"></i>Về trang đăng nhập</a>
        </div>
    </div>
</div>

<script>
    const SITE_ROOT = <?= json_encode(rtrim(URL_ROOT, '/'), JSON_UNESCAPED_SLASHES) ?>;
    const CSRF_TOKEN = <?= json_encode(Csrf::token()) ?>;

    const form = document.getElementById('email-otp-form');
    const errorBox = document.getElementById('email-otp-error');
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox.classList.add('d-none');
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const body = new URLSearchParams({
                _csrf_token: CSRF_TOKEN,
                otp: document.getElementById('email-otp').value.trim(),
            });
            const response = await fetch(SITE_ROOT + '/nguoi-dung/verify-otp', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body,
            });
            const data = await response.json();
            if (data.success && data.authenticated) {
                window.location.href = data.redirect || SITE_ROOT + '/nguoi-dung/post';
                return;
            }
            errorBox.textContent = data.message || 'Mã OTP không hợp lệ.';
            errorBox.classList.remove('d-none');
        } catch (_) {
            errorBox.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại.';
            errorBox.classList.remove('d-none');
        } finally {
            button.disabled = false;
        }
    });

    document.getElementById('resend-email-otp').addEventListener('click', async function () {
        this.disabled = true;
        try {
            const body = new URLSearchParams({_csrf_token: CSRF_TOKEN});
            const response = await fetch(SITE_ROOT + '/nguoi-dung/resend-otp', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body,
            });
            const data = await response.json();
            alert(data.message || (data.success ? 'Đã gửi lại mã OTP.' : 'Không thể gửi lại mã OTP.'));
        } finally {
            setTimeout(() => { this.disabled = false; }, 60000);
        }
    });
</script>
</body>
</html>
