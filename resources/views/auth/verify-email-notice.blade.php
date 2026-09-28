<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email – {{ SITE_NAME }}</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="icon" href="{{ URL_ROOT }}/public/images/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL_ROOT }}/public/css/auth.css">
</head>
<body class="auth-page">
<div class="auth-verify-page">
    <div class="auth-verify-card">
        <a href="{{ URL_ROOT }}" class="text-decoration-none fw-bold fs-5 d-block text-center mb-4" style="color:#2563eb;">
            <i class="fas fa-house me-1"></i> {{ SITE_NAME }}
        </a>

        <div class="auth-verify-icon warn">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <h1 class="fw-800 mb-2" style="font-size:1.4rem;color:#1f2937;">Vui lòng xác thực email</h1>
        <p style="color:#6b7280;font-size:.9rem;line-height:1.7;">
            Hãy mở email chúng tôi đã gửi và bấm vào liên kết xác thực để tiếp tục sử dụng tài khoản.
        </p>

        @if (session('verification_error'))
            <div class="auth-alert error mt-3 text-start">
                <i class="fas fa-circle-exclamation"></i>
                <div>{{ session('verification_error') }}</div>
            </div>
        @endif

        <div class="mt-4 text-start">
            <label for="resend-identifier" class="form-label fw-semibold" style="font-size:.84rem;">
                Chưa nhận được email? Nhập email để gửi lại:
            </label>
            <input
                type="email"
                id="resend-identifier"
                class="form-control"
                value="{{ $email }}"
                placeholder="email@example.com"
                autocomplete="email"
            >
            <button id="resend-verify-btn" class="auth-btn mt-3" type="button">
                <i class="fas fa-paper-plane me-2"></i>Gửi lại email xác thực
            </button>
        </div>

        <div class="auth-form-footer mt-4">
            <a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>Về trang đăng nhập</a>
        </div>
    </div>
</div>

<script>
    const SITE_ROOT = @json(rtrim(URL_ROOT, '/'));
    const CSRF_TOKEN = @json(csrf_token());
</script>
<script src="{{ URL_ROOT }}/public/js/auth.js"></script>
</body>
</html>
