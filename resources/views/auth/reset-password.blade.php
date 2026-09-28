<?php
/**
 * View: Đặt lại mật khẩu – /nguoi-dung/resetPassword/{token}
 */
$pageTitle  = $title ?? 'Đặt lại mật khẩu – ' . SITE_NAME;
$csrf_token = Csrf::token();
$errors     = $errors ?? [];
$token      = htmlspecialchars($token ?? '', ENT_QUOTES);
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
<div class="auth-verify-card" style="max-width:440px;text-align:left;">
    <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-5 d-block text-center mb-4" style="color:#2563eb;">
        🏠 TimNhaDat.site
    </a>

    <div style="text-align:center;margin-bottom:24px;">
        <div class="auth-verify-icon success" style="margin:0 auto 12px;">
            <i class="fas fa-lock-open"></i>
        </div>
        <h2 class="fw-700 mb-1" style="font-size:1.3rem;color:#111827;">Đặt lại mật khẩu</h2>
        <p style="color:#6b7280;font-size:.88rem;">Tạo mật khẩu mới bảo mật cho tài khoản của bạn</p>
    </div>

    <?php if (!empty($errors['general'])): ?>
    <div class="auth-alert danger">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= htmlspecialchars($errors['general']) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/resetPassword" class="auth-form">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
        <input type="hidden" name="token" value="<?= $token ?>">

        <!-- New password -->
        <div class="auth-field">
            <label for="mat_khau">Mật khẩu mới <span class="text-danger">*</span></label>
            <div class="auth-input-wrap">
                <i class="fas fa-lock auth-icon"></i>
                <input type="password" id="mat_khau" name="mat_khau"
                    class="auth-input <?= !empty($errors['mat_khau']) ? 'is-invalid' : '' ?>"
                    placeholder="Ít nhất 8 ký tự"
                    autocomplete="new-password"
                    autofocus>
                <button type="button" class="auth-toggle-pass" data-target="mat_khau"><i class="fas fa-eye"></i></button>
            </div>
            <?php if (!empty($errors['mat_khau'])): ?>
            <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['mat_khau']) ?></div>
            <?php endif; ?>

            <!-- Password strength -->
            <div class="pw-strength-wrap">
                <div class="pw-strength-bar" id="pw-strength-bar"><div class="pw-strength-fill"></div></div>
                <span class="pw-strength-label" id="pw-strength-label"></span>
                <ul class="pw-checklist" id="pw-checklist">
                    <li data-check="check-length"><i class="fas fa-circle"></i> 8+ ký tự</li>
                    <li data-check="check-upper"><i class="fas fa-circle"></i> Chữ hoa</li>
                    <li data-check="check-lower"><i class="fas fa-circle"></i> Chữ thường</li>
                    <li data-check="check-digit"><i class="fas fa-circle"></i> Số</li>
                    <li data-check="check-special"><i class="fas fa-circle"></i> Ký tự đặc biệt</li>
                </ul>
            </div>
        </div>

        <!-- Confirm password -->
        <div class="auth-field">
            <label for="mat_khau_xac_nhan">Nhập lại mật khẩu <span class="text-danger">*</span></label>
            <div class="auth-input-wrap">
                <i class="fas fa-lock auth-icon"></i>
                <input type="password" id="mat_khau_xac_nhan" name="mat_khau_xac_nhan"
                    class="auth-input <?= !empty($errors['mat_khau_xac_nhan']) ? 'is-invalid' : '' ?>"
                    placeholder="Nhập lại mật khẩu"
                    autocomplete="new-password">
                <button type="button" class="auth-toggle-pass" data-target="mat_khau_xac_nhan"><i class="fas fa-eye"></i></button>
            </div>
            <?php if (!empty($errors['mat_khau_xac_nhan'])): ?>
            <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['mat_khau_xac_nhan']) ?></div>
            <?php endif; ?>
        </div>

        <div class="auth-alert info" style="font-size:.82rem;">
            <i class="fas fa-info-circle"></i>
            <span>Sau khi đặt lại, bạn sẽ bị đăng xuất khỏi tất cả thiết bị.</span>
        </div>

        <button type="submit" class="auth-btn mt-2">
            <span class="btn-text"><i class="fas fa-save me-2"></i>Lưu mật khẩu mới</span>
            <div class="spinner"></div>
        </button>
    </form>

    <div class="auth-form-footer mt-4" style="text-align:center;">
        <a href="<?= URL_ROOT ?>/dang-nhap"><i class="fas fa-arrow-left me-1"></i>Quay lại đăng nhập</a>
    </div>
</div>
</div>

<script>
    const SITE_ROOT  = '<?= URL_ROOT ?>';
    const CSRF_TOKEN = '<?= $csrf_token ?>';
</script>
<script src="<?= URL_ROOT ?>/public/js/auth.js"></script>
</body>
</html>
