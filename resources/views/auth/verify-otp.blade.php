<?php
/**
 * View: Xác thực OTP – /nguoi-dung/verify-otp
 */
$pageTitle  = $title ?? 'Xác thực OTP – ' . SITE_NAME;
$csrf_token = Csrf::token();
$purpose    = htmlspecialchars($purpose ?? 'reset_password');
$cooldown   = (int)($cooldown ?? 60);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="<?= URL_ROOT ?>/public/images/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-verify-page" id="otp-section">
<div class="auth-verify-card" style="max-width:440px;">
    <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-5 d-block text-center mb-4" style="color:#2563eb;">
        🏠 TimNhaDat.site
    </a>

    <div class="auth-verify-icon success" style="background:#eff6ff;color:#2563eb;">
        <i class="fas fa-shield-alt"></i>
    </div>
    <h2 class="fw-800 mb-1" style="font-size:1.3rem;color:#111827;">Nhập mã OTP</h2>
    <p style="color:#6b7280;font-size:.88rem;line-height:1.6;">
        Mã xác thực 6 chữ số đã được gửi đến email của bạn.<br>
        Hiệu lực trong <strong>5 phút</strong>.
    </p>

    <!-- OTP 6-ô -->
    <div class="otp-inputs my-3">
        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code">
        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]">
    </div>

    <!-- Error -->
    <div id="otp-error" class="auth-alert danger" style="display:none;">
        <i class="fas fa-exclamation-circle"></i>
        <span id="otp-error-msg">Mã OTP không đúng hoặc đã hết hạn.</span>
    </div>

    <!-- Hidden full OTP -->
    <input type="hidden" id="otp_full" name="otp">
    <input type="hidden" id="otp_purpose" value="<?= $purpose ?>">

    <!-- Verify button -->
    <button type="button" id="verify-otp-btn" class="auth-btn mb-3">
        <span class="btn-text"><i class="fas fa-check-circle me-2"></i>Xác thực</span>
        <div class="spinner"></div>
    </button>

    <!-- Resend -->
    <div class="otp-resend">
        Chưa nhận được mã?
        <button type="button" id="resend-otp-btn" class="otp-resend-btn" disabled>
            Gửi lại
        </button>
        <span id="resend-countdown"></span>
    </div>

    <div class="auth-form-footer mt-4">
        <a href="<?= URL_ROOT ?>/nguoi-dung/forgotPassword"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
    </div>
</div>
</div>

<script>
    const SITE_ROOT   = '<?= URL_ROOT ?>';
    const CSRF_TOKEN  = '<?= $csrf_token ?>';
    const OTP_PURPOSE = '<?= $purpose ?>';

    document.addEventListener('DOMContentLoaded', () => {
        // Khởi động countdown
        if (window.AUTH) {
            AUTH.initOTPCountdown(<?= $cooldown ?>);
        }

        // Verify button
        const btn = document.getElementById('verify-otp-btn');
        btn && btn.addEventListener('click', async () => {
            const code = document.getElementById('otp_full')?.value || '';
            if (code.length < 6) {
                document.getElementById('otp-error-msg').textContent = 'Vui lòng nhập đủ 6 chữ số.';
                document.getElementById('otp-error').style.display = 'flex';
                return;
            }
            btn.classList.add('loading');
            btn.disabled = true;

            try {
                const res  = await fetch(SITE_ROOT + '/nguoi-dung/verify-otp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `otp=${encodeURIComponent(code)}&purpose=${encodeURIComponent(OTP_PURPOSE)}&_csrf_token=${encodeURIComponent(CSRF_TOKEN)}`,
                });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('otp-error').style.display = 'none';
                    if (window.AUTH) AUTH.showToast('OTP hợp lệ! Đang chuyển hướng...', 'success');
                    setTimeout(() => {
                        window.location.href = data.reset_url || (SITE_ROOT + '/nguoi-dung/forgotPassword');
                    }, 1200);
                } else {
                    document.getElementById('otp-error-msg').textContent = data.message || 'Mã OTP không đúng.';
                    document.getElementById('otp-error').style.display = 'flex';
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            } catch {
                btn.classList.remove('loading');
                btn.disabled = false;
                if (window.AUTH) AUTH.showToast('Lỗi kết nối. Vui lòng thử lại.', 'error');
            }
        });
    });
</script>
<script src="<?= URL_ROOT ?>/public/js/auth.js"></script>
</body>
</html>
