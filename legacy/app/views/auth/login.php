<?php
/**
 * View: Đăng nhập – /nguoi-dung/dang-nhap
 * Trang đăng nhập người dùng với thiết kế split-panel premium.
 */
$pageTitle  = $data['title'] ?? 'Đăng nhập – ' . SITE_NAME;
$csrf_token = Csrf::token();
$errors     = $errors ?? [];
$identifier = $identifier ?? '';
$captchaNeeded = $captcha_needed ?? false;
$unverified    = $unverified ?? false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="<?= URL_ROOT ?>/public/images/favicon.png" type="image/png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Auth CSS -->
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-split">
    <!-- ── LEFT: Hero Panel ── -->
    <div class="auth-hero d-none d-lg-flex flex-column">
        <a href="<?= URL_ROOT ?>" class="auth-hero-logo">
            🏠 Tim<span>NhaDat</span>.site
        </a>
        <h1 class="auth-hero-title">Chào mừng trở lại!</h1>
        <p class="auth-hero-sub">
            Đăng nhập để quản lý tin đăng, xem thống kê và tìm kiếm bất động sản ưng ý của bạn.
        </p>
        <ul class="auth-feature-list">
            <li><i class="fas fa-shield-alt"></i> Bảo mật 2 lớp</li>
            <li><i class="fas fa-bell"></i> Thông báo tin đăng mới nhất</li>
            <li><i class="fas fa-chart-line"></i> Thống kê lượt xem tin của bạn</li>
            <li><i class="fas fa-heart"></i> Lưu bất động sản yêu thích</li>
        </ul>
    </div>

    <!-- ── RIGHT: Form Panel ── -->
    <div class="auth-form-panel">
        <!-- Mobile logo -->
        <div class="d-lg-none text-center mb-4">
            <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-4" style="color:#2563eb;">
                🏠 TimNhaDat.site
            </a>
        </div>

        <div class="auth-form-header">
            <h1>Đăng nhập</h1>
            <p>Nhập thông tin tài khoản để tiếp tục</p>
        </div>

        <!-- Flash messages -->
        <?php if (Session::get('success')): ?>
        <div class="auth-alert success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars(Session::get('success')) ?></span>
        </div>
        <?php Session::delete('success'); endif; ?>

        <!-- General error -->
        <?php if (!empty($errors['general'])): ?>
        <div class="auth-alert danger" id="login-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($errors['general']) ?></span>
        </div>
        <?php endif; ?>

        <!-- Unverified email notice -->
        <?php if ($unverified): ?>
        <div class="auth-alert info">
            <i class="fas fa-envelope"></i>
            <div>
                <div>Tài khoản chưa xác thực email.</div>
                <input type="hidden" id="resend-identifier" value="<?= htmlspecialchars($identifier) ?>">
                <button id="resend-verify-btn" class="btn btn-link p-0 mt-1" style="font-size:.83rem;">
                    📧 Gửi lại email xác thực
                </button>
            </div>
        </div>
        <?php endif; ?>

        <?php if (false): // Tạm ẩn đăng nhập mạng xã hội; bật lại khi hoàn thiện OAuth. ?>
        <!-- Social login (placeholder) -->
        <div class="auth-social-grid">
            <a href="#" onclick="return openSocialPopup('<?= URL_ROOT ?>/nguoi-dung/social-redirect?provider=google')" class="auth-social-btn google">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </a>
            <a href="#" onclick="return openSocialPopup('<?= URL_ROOT ?>/nguoi-dung/social-redirect?provider=facebook')" class="auth-social-btn facebook">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Facebook
            </a>
        </div>

        <div class="auth-divider"><span>hoặc đăng nhập bằng tài khoản</span></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/dang-nhap" class="auth-form" id="login-form">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

            <!-- Identifier: email or phone -->
            <div class="auth-field">
                <label for="identifier">Email hoặc Số điện thoại</label>
                <div class="auth-input-wrap">
                    <i class="fas fa-user auth-icon"></i>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        class="auth-input <?= !empty($errors['identifier']) ? 'is-invalid' : '' ?>"
                        placeholder="email@example.com hoặc 0901234567"
                        value="<?= htmlspecialchars($identifier) ?>"
                        autocomplete="username"
                        autofocus
                    >
                </div>
                <?php if (!empty($errors['identifier'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['identifier']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="auth-field">
                <label for="mat_khau">Mật khẩu</label>
                <div class="auth-input-wrap">
                    <i class="fas fa-lock auth-icon"></i>
                    <input
                        type="password"
                        id="mat_khau"
                        name="mat_khau"
                        class="auth-input <?= !empty($errors['mat_khau']) ? 'is-invalid' : '' ?>"
                        placeholder="Nhập mật khẩu"
                        autocomplete="current-password"
                    >
                    <button type="button" class="auth-toggle-pass" data-target="mat_khau" title="Hiện/ẩn mật khẩu">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($errors['mat_khau'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['mat_khau']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Captcha (hiện sau >= 3 lần sai) -->
            <?php if ($captchaNeeded): ?>
            <div class="auth-field">
                <label>Mã xác nhận bảo mật</label>
                <?php
                $captcha = '';
                $chars   = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
                for ($i = 0; $i < 5; $i++) $captcha .= $chars[random_int(0, strlen($chars)-1)];
                Session::set('auth_captcha', strtolower($captcha));
                ?>
                <div class="captcha-wrap">
                    <div class="captcha-box" id="captcha-display"><?= $captcha ?></div>
                    <input type="text" name="captcha" class="captcha-input" placeholder="Nhập mã" maxlength="5" autocomplete="off">
                    <button type="button" class="captcha-refresh" id="captcha-refresh" title="Tải lại">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <?php if (!empty($errors['captcha'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['captcha']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Remember me + Forgot -->
            <div class="auth-extras">
                <label class="auth-remember">
                    <input type="checkbox" name="remember_me" value="1">
                    Ghi nhớ đăng nhập
                </label>
                <a href="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" class="auth-forgot" style="color: #2563eb; font-weight: 500;">Quên mật khẩu?</a>
            </div>

            <!-- Submit -->
            <button type="submit" class="auth-btn" id="login-submit">
                <span class="btn-text"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Đăng nhập</span>
                <div class="spinner"></div>
            </button>
        </form>

        <!-- Register link -->
        <div class="auth-form-footer mt-4">
            Chưa có tài khoản? <a href="<?= URL_ROOT ?>/nguoi-dung/register" style="color: #2563eb; font-weight: 600;">Đăng ký miễn phí</a>
        </div>
    </div><!-- /auth-form-panel -->
</div><!-- /auth-split -->

<script>
    const SITE_ROOT  = '<?= URL_ROOT ?>';
    const CSRF_TOKEN = '<?= $csrf_token ?>';
    
    function openSocialPopup(url) {
        const width = 500;
        const height = 600;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        window.open(url, 'socialLoginPopup', `width=${width},height=${height},left=${left},top=${top},status=no,resizable=yes`);
        return false;
    }
</script>
<script src="<?= URL_ROOT ?>/public/js/auth.js"></script>
</body>
</html>
