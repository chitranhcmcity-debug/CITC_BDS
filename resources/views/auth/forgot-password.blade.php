<?php
/**
 * View: Quên mật khẩu – /nguoi-dung/forgotPassword
 */
$pageTitle  = $title ?? 'Quên mật khẩu – ' . SITE_NAME;
$csrf_token = Csrf::token();
$errors     = $errors  ?? [];
$success    = $success ?? false;
$message    = $message ?? '';
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
<div class="auth-verify-page" style="background:#f8fafc;">
<div class="auth-verify-card" style="max-width:440px;">
    <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-5 d-block text-center mb-4" style="color:#2563eb;">
        🏠 TimNhaDat.site
    </a>

    <?php if ($success): ?>
    <!-- ── Gửi thành công ── -->
    <div class="auth-verify-icon success">
        <i class="fas fa-paper-plane"></i>
    </div>
    <h2 class="fw-700 mb-2" style="font-size:1.3rem;color:#111827;">Kiểm tra email của bạn</h2>
    <p style="color:#6b7280;font-size:.9rem;line-height:1.7;"><?= htmlspecialchars($message) ?></p>
    <div class="auth-alert info mt-3" style="text-align:left;">
        <i class="fas fa-info-circle mt-1"></i>
        <div>
            <strong>Không nhận được email?</strong><br>
            Kiểm tra thư mục Thư rác hoặc <a href="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" style="color:#2563eb;">thử lại</a>.
        </div>
    </div>
    <div style="text-align:center;margin-top:24px;">
        <a href="<?= URL_ROOT ?>/dang-nhap" class="auth-btn" style="display:inline-block;width:auto;padding:12px 28px;text-decoration:none;">
            <i class="fas fa-sign-in-alt me-2"></i>Về đăng nhập
        </a>
    </div>

    <?php else: ?>
    <!-- ── Form nhập email/SĐT ── -->
    <div class="auth-verify-icon warn">
        <i class="fas fa-key"></i>
    </div>
    <h2 class="fw-700 mb-1" style="font-size:1.3rem;color:#111827;">Quên mật khẩu?</h2>
    <p style="color:#6b7280;font-size:.88rem;margin-bottom:24px;">
        Nhập email hoặc số điện thoại đăng ký. Chúng tôi sẽ gửi link đặt lại mật khẩu.
    </p>

    <?php if (!empty($errors['general'])): ?>
    <div class="auth-alert danger mb-3">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($errors['general']) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" class="auth-form">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

        <div class="auth-field">
            <label for="identifier">Email hoặc số điện thoại</label>
            <div class="auth-input-wrap">
                <i class="fas fa-envelope auth-icon"></i>
                <input type="text" id="identifier" name="identifier"
                    class="auth-input <?= !empty($errors['identifier']) ? 'is-invalid' : '' ?>"
                    placeholder="email@example.com hoặc 0901234567"
                    autofocus autocomplete="username">
            </div>
            <?php if (!empty($errors['identifier'])): ?>
            <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['identifier']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="auth-btn mt-1">
            <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Gửi hướng dẫn đặt lại</span>
            <div class="spinner"></div>
        </button>
    </form>

    <div class="auth-form-footer mt-4">
        <a href="<?= URL_ROOT ?>/dang-nhap"><i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập</a>
    </div>
    <?php endif; ?>
</div>
</div>

<script>
    const SITE_ROOT  = '<?= URL_ROOT ?>';
    const CSRF_TOKEN = '<?= $csrf_token ?>';
</script>
<script src="<?= URL_ROOT ?>/public/js/auth.js"></script>
</body>
</html>
