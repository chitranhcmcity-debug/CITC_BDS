<?php
/**
 * View: Xác thực Email – /nguoi-dung/verify-email/{token}
 */
$pageTitle = $title ?? 'Xác thực Email – ' . SITE_NAME;
$success   = $success ?? false;
$expired   = $expired ?? false;
$message   = htmlspecialchars($message ?? '');
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
<div class="auth-verify-page">
<div class="auth-verify-card">
    <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-5 d-block text-center mb-4" style="color:#2563eb;">
        🏠 TimNhaDat.site
    </a>

    <?php if ($success): ?>
    <!-- ── Thành công ── -->
    <div class="auth-verify-icon success">
        <i class="fas fa-check-circle"></i>
    </div>
    <h2 class="fw-800 mb-2" style="font-size:1.4rem;color:#166534;">Xác thực thành công! 🎉</h2>
    <p style="color:#6b7280;font-size:.9rem;line-height:1.7;"><?= $message ?></p>
    <div class="auth-alert success mt-3" style="text-align:left;">
        <i class="fas fa-check-circle"></i>
        <div>Tài khoản của bạn đã được kích hoạt. Hãy đăng nhập để bắt đầu sử dụng!</div>
    </div>
    <div style="text-align:center;margin-top:24px;">
        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap" class="auth-btn" style="display:inline-block;width:auto;padding:13px 32px;text-decoration:none;">
            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập ngay
        </a>
    </div>

    <?php elseif ($expired): ?>
    <!-- ── Hết hạn ── -->
    <div class="auth-verify-icon warn">
        <i class="fas fa-clock"></i>
    </div>
    <h2 class="fw-800 mb-2" style="font-size:1.3rem;color:#92400e;">Link đã hết hạn</h2>
    <p style="color:#6b7280;font-size:.9rem;line-height:1.7;"><?= $message ?></p>
    <div style="margin-top:20px;">
        <label for="resend-identifier" style="font-size:.84rem;font-weight:600;color:#374151;">
            Nhập email để nhận lại link xác thực:
        </label>
        <div style="display:flex;gap:8px;margin-top:6px;">
            <input type="email" id="resend-identifier" placeholder="email@example.com"
                style="flex:1;padding:10px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:.88rem;outline:none;">
            <button id="resend-verify-btn" class="auth-btn" style="width:auto;padding:10px 18px;font-size:.84rem;">
                Gửi lại
            </button>
        </div>
        <div id="resend-feedback" style="font-size:.78rem;margin-top:6px;color:#6b7280;"></div>
    </div>
    <div class="auth-form-footer mt-4">
        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap"><i class="fas fa-arrow-left me-1"></i>Về đăng nhập</a>
    </div>

    <?php else: ?>
    <!-- ── Không hợp lệ ── -->
    <div class="auth-verify-icon error">
        <i class="fas fa-times-circle"></i>
    </div>
    <h2 class="fw-800 mb-2" style="font-size:1.3rem;color:#991b1b;">Link không hợp lệ</h2>
    <p style="color:#6b7280;font-size:.9rem;line-height:1.7;"><?= $message ?></p>
    <div style="text-align:center;margin-top:24px;">
        <a href="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" class="auth-btn" style="display:inline-block;width:auto;padding:12px 28px;text-decoration:none;">
            <i class="fas fa-redo me-2"></i>Yêu cầu gửi lại
        </a>
    </div>
    <div class="auth-form-footer mt-3">
        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap"><i class="fas fa-arrow-left me-1"></i>Về đăng nhập</a>
    </div>
    <?php endif; ?>
</div>
</div>

<?php if ($success): ?>
<script>
    // Auto redirect sau 5 giây
    let count = 5;
    const interval = setInterval(() => {
        count--;
        if (count <= 0) {
            clearInterval(interval);
            window.location.href = '<?= URL_ROOT ?>/nguoi-dung/dang-nhap';
        }
    }, 1000);
</script>
<?php endif; ?>

<script>
    const SITE_ROOT  = '<?= URL_ROOT ?>';
    const CSRF_TOKEN = '<?= Csrf::token() ?>';
</script>
<script src="<?= URL_ROOT ?>/public/js/auth.js"></script>
</body>
</html>
