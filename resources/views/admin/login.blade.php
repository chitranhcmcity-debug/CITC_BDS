<?php $loginSiteName = (string)($data['layout']['settings']['site_name'] ?? SITE_NAME); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập – <?= htmlspecialchars($loginSiteName) ?></title>
    <link rel="icon" href="<?= URL_ROOT ?>/public/images/favicon.png?v=2" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f2f5;
        }

        /* ── Cột trái: ảnh nền + overlay ── */
        .login-hero {
            flex: 1;
            display: none;
            position: relative;
            overflow: hidden;
        }
        .login-hero-bg {
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            animation: hero-zoom 20s linear infinite alternate;
            z-index: 0;
        }
        @keyframes hero-zoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.15); }
        }
        @media (min-width: 768px) { .login-hero { display: block; } }

        .login-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(180deg,
                transparent 0%,
                rgba(13,148,136,0) 60%,
                rgba(2,78,70,0.75) 100%);
            z-index: 1;
        }
        /* Shimmer quét qua ảnh */
        .login-hero::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(110deg,
                transparent 0%,
                rgba(255,255,255,0.06) 40%,
                rgba(255,255,255,0.12) 50%,
                rgba(255,255,255,0.06) 60%,
                transparent 100%);
            animation: hero-shimmer 5s ease-in-out infinite;
            pointer-events: none;
            z-index: 2;
        }
        @keyframes hero-shimmer {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }

        .login-hero-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 48px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5); /* 2. Improve Text Readability */
        }
        .login-hero-content h2 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 12px;
            animation: fade-up 0.8s ease 0.2s both;
        }
        .login-hero-content p {
            font-size: 0.95rem;
            opacity: 0.85;
            margin: 0 0 28px;
            animation: fade-up 0.8s ease 0.4s both;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(13,191,170,0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(45,212,191,0.5);
            border-radius: 50px;
            padding: 7px 18px;
            font-size: 0.8rem;
            color: #fff;
            margin-bottom: 24px;
            animation: fade-up 0.8s ease 0.0s both, float-badge 3s ease-in-out 1s infinite alternate;
        }
        @keyframes float-badge {
            from { transform: translateY(0); }
            to   { transform: translateY(-5px); }
        }

        /* Stat cards */
        .hero-stats {
            display: flex;
            gap: 14px;
            animation: fade-up 0.8s ease 0.6s both;
        }
        .hero-stat {
            background: rgba(255,255,255,0.10);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 12px;
            padding: 10px 16px;
            text-align: center;
            min-width: 90px;
        }
        .hero-stat strong {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            color: #5eead4;   /* teal sáng */
        }
        .hero-stat span {
            font-size: 0.72rem;
            opacity: 0.75;
        }

        /* ── Cột phải: form ── */
        .login-panel {
            width: 100%;
            max-width: 480px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
        }
        @media (max-width: 767px) {
            .login-panel { max-width: 100%; padding: 36px 24px; }
        }

        /* Logo */
        .login-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            margin-bottom: 40px;
        }
        .login-logo img { width: 40px; height: 40px; object-fit: contain; }
        .login-logo span { font-weight: 700; font-size: 1.05rem; color: #1a1a2e; }

        /* Heading */
        .login-title { font-size: 1.75rem; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .login-sub   { font-size: 0.88rem; color: #6b7280; margin-bottom: 32px; }

        /* Input group */
        .field-wrap { position: relative; margin-bottom: 18px; }
        .field-wrap label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .field-wrap .field-icon {
            position: absolute;
            left: 14px;
            bottom: 13px;
            color: #9ca3af;
            font-size: 0.9rem;
            pointer-events: none;
        }
        .field-wrap input {
            width: 100%;
            padding: 12px 40px 12px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.92rem;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
            background: #fafafa;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .field-wrap input:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.10);
            background: #fff;
        }
        .field-wrap input.is-invalid { border-color: #ef4444; }
        .field-wrap .toggle-pw-btn {
            position: absolute;
            right: 12px;
            bottom: 10px;
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 0.9rem;
            padding: 4px;
        }
        .invalid-feedback { font-size: 0.78rem; color: #ef4444; margin-top: 4px; }

        /* Checkbox Ghi nhớ đăng nhập */
        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .remember-wrap input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #dc2626;
            cursor: pointer;
        }
        .remember-wrap label {
            font-size: 0.88rem;
            color: #4b5563;
            cursor: pointer;
            margin: 0;
            user-select: none;
            transition: color .2s;
        }
        .remember-wrap label:hover {
            color: #1a1a2e;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: 0.03em;
            transition: all .2s ease;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(220, 38, 38, 0.2);
        }
        .btn-login:hover { 
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            box-shadow: 0 6px 12px rgba(220, 38, 38, 0.3);
            transform: translateY(-2px); 
        }
        .btn-login:active { 
            transform: translateY(0); 
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
        }
        .btn-login:focus {
            outline: 3px solid rgba(220, 38, 38, 0.4);
            outline-offset: 2px;
        }

        /* Bottom links */
        .login-links {
            display: flex;
            justify-content: space-between;
            font-size: 0.82rem;
        }
        .login-links a { color: #dc2626; text-decoration: none; font-weight: 500; }
        .login-links a:hover { text-decoration: underline; }

        /* Alert error */
        .alert-login {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #dc2626;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Divider dưới cùng */
        .login-footer {
            margin-top: 40px;
            font-size: 0.78rem;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Cột trái: Hero -->
<div class="login-hero">
    <div class="login-hero-bg"></div>
    <div class="login-hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-house-chimney"></i>
            Nền tảng BDS uy tín số 1
        </div>
        <h2>Tìm kiếm ngôi nhà<br>mơ ước của bạn</h2>
        <p>Hàng nghìn bất động sản được cập nhật mỗi ngày,<br>kết nối trực tiếp chủ nhà và người mua.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong>10K+</strong>
                <span>Tin đăng</span>
            </div>
            <div class="hero-stat">
                <strong>500+</strong>
                <span>Môi giới</span>
            </div>
            <div class="hero-stat">
                <strong>63</strong>
                <span>Tỉnh thành</span>
            </div>
        </div>
    </div>
</div>

<!-- Cột phải: Form -->
<div class="login-panel">

    <!-- Logo -->
    <a href="<?= URL_ROOT ?>" class="login-logo">
        <img src="<?= URL_ROOT ?>/public/images/favicon.png?v=2" alt="Logo" style="width: 40px; height: 40px; object-fit: contain;">
        <span><?= htmlspecialchars($loginSiteName) ?></span>
    </a>

    <h1 class="login-title">Chào mừng trở lại</h1>
    <p class="login-sub">Đăng nhập để tiếp tục vào hệ thống</p>

    <!-- Error flash -->
    <?php if (Session::get('error_msg')): ?>
    <div class="alert-login">
        <i class="fa-solid fa-circle-exclamation"></i>
        <?= htmlspecialchars(Session::get('error_msg')); Session::delete('error_msg'); ?>
    </div>
    <?php endif; ?>

    <form action="<?= URL_ROOT ?>/admin/login" method="POST" autocomplete="off">
        <?= Csrf::field() ?>

        <!-- Email -->
        <div class="field-wrap">
            <label for="email">Tài khoản (Email / Username)</label>
            <i class="fa-solid fa-envelope field-icon"></i>
            <input type="text" id="email" name="email"
                   value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                   placeholder="Nhập email hoặc tên đăng nhập của bạn..."
                   class="<?= !empty($data['email_err']) ? 'is-invalid' : '' ?>"
                   autocomplete="off">
            <?php if (!empty($data['email_err'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($data['email_err']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Mật khẩu -->
        <div class="field-wrap" style="margin-bottom:16px;">
            <label for="password">Mật khẩu</label>
            <i class="fa-solid fa-lock field-icon"></i>
            <input type="password" id="password" name="password"
                   placeholder="Nhập mật khẩu..."
                   class="<?= !empty($data['password_err']) ? 'is-invalid' : '' ?>"
                   autocomplete="new-password">
            <button type="button" class="toggle-pw-btn" onclick="togglePw()" title="Hiện/ẩn mật khẩu">
                <i class="fa-solid fa-eye" id="pwEyeIcon"></i>
            </button>
            <?php if (!empty($data['password_err'])): ?>
                <div class="invalid-feedback"><?= htmlspecialchars($data['password_err']) ?></div>
            <?php endif; ?>
        </div>

        <!-- 3. Ghi nhớ đăng nhập -->
        <div class="remember-wrap">
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Ghi nhớ đăng nhập</label>
        </div>

        <button type="submit" class="btn-login">
            <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng Nhập
        </button>
    </form>

    <div class="login-links">
        <a href="<?= URL_ROOT ?>/nguoi-dung/forgotPassword">Quên mật khẩu?</a>
        <a href="<?= URL_ROOT ?>/nguoi-dung/register">Chưa có tài khoản? Đăng ký</a>
    </div>

    <div class="login-footer">
        © <?= date('Y') ?> <?= htmlspecialchars($loginSiteName) ?>. Bảo lưu mọi quyền.
    </div>
</div>

<script>
function togglePw() {
    var inp  = document.getElementById('password');
    var icon = document.getElementById('pwEyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}
</script>
</body>
</html>
