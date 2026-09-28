<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title'] ?? 'Quên Mật Khẩu') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { max-width: 460px; width: 100%; border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,.1); }
        .verification-code { font-size: 1.6rem; letter-spacing: .55rem; text-align: center; font-weight: 700; }
    </style>
</head>
<body>

<div class="card login-card border-0">
    <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">Quên Mật Khẩu</h2>
            <?php if ($data['step'] === 'email'): ?>
                <p class="text-muted mb-0">Nhập email để nhận mã xác thực</p>
            <?php elseif ($data['step'] === 'code'): ?>
                <p class="text-muted mb-0">Nhập mã xác thực đã gửi đến email</p>
            <?php elseif ($data['step'] === 'password'): ?>
                <p class="text-muted mb-0">Tạo mật khẩu mới cho tài khoản</p>
            <?php else: ?>
                <p class="text-muted mb-0">Khôi phục mật khẩu hoàn tất</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($data['error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> <?= htmlspecialchars($data['error']) ?></div>
        <?php endif; ?>

        <?php if (!empty($data['success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i> <?= htmlspecialchars($data['success']) ?></div>
        <?php endif; ?>

        <?php if ($data['step'] === 'email'): ?>
            <form action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="request_code">
                <div class="mb-4">
                    <label for="resetEmail" class="form-label">Địa chỉ Gmail/Email</label>
                    <input type="email" id="resetEmail" name="email" class="form-control form-control-lg"
                           value="<?= htmlspecialchars($data['email'] ?? '') ?>" placeholder="vidu@gmail.com" autocomplete="email" required autofocus>
                </div>
                <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold mb-3 text-dark">
                    Gửi Mã Xác Thực
                </button>
            </form>

        <?php elseif ($data['step'] === 'code'): ?>
            <div class="text-center small text-muted mb-3">
                Mã gồm 6 chữ số đã được gửi đến<br>
                <strong class="text-dark"><?= htmlspecialchars($data['email']) ?></strong>
            </div>
            <form action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="verify_code">
                <div class="mb-4">
                    <label for="verificationCode" class="form-label">Mã xác thực</label>
                    <input type="text" id="verificationCode" name="verification_code"
                           class="form-control form-control-lg verification-code" inputmode="numeric"
                           pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="000000" required autofocus>
                    <div class="form-text text-center">Mã có hiệu lực trong 10 phút.</div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold mb-3">Xác Nhận Mã</button>
            </form>
            <div class="d-flex justify-content-between gap-2">
                <form action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" method="POST">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="restart">
                    <button type="submit" class="btn btn-link text-secondary p-0">Đổi email</button>
                </form>
                <form action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" method="POST">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="request_code">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($data['email']) ?>">
                    <button type="submit" class="btn btn-link p-0">Gửi lại mã</button>
                </form>
            </div>

        <?php elseif ($data['step'] === 'password'): ?>
            <form action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" method="POST">
                <?= Csrf::field() ?>
                <input type="hidden" name="action" value="reset_password">
                <div class="mb-3">
                    <label for="newPassword" class="form-label">Mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" id="newPassword" name="new_password" class="form-control form-control-lg"
                               minlength="8" autocomplete="new-password" required autofocus>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="newPassword" aria-label="Hiện mật khẩu">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Mật khẩu phải có ít nhất 8 ký tự.</div>
                </div>
                <div class="mb-4">
                    <label for="confirmPassword" class="form-label">Xác nhận mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" id="confirmPassword" name="confirm_password" class="form-control form-control-lg"
                               minlength="8" autocomplete="new-password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirmPassword" aria-label="Hiện mật khẩu">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">Cập Nhật Mật Khẩu</button>
            </form>

        <?php else: ?>
            <a href="<?= URL_ROOT ?>/dang-nhap" class="btn btn-primary btn-lg w-100 fw-bold">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng Nhập Ngay
            </a>
        <?php endif; ?>

        <?php if ($data['step'] !== 'complete'): ?>
            <div class="text-center mt-4">
                <a href="<?= URL_ROOT ?>/dang-nhap" class="text-decoration-none small text-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại Đăng Nhập
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(function(button) {
    button.addEventListener('click', function() {
        var input = document.getElementById(button.dataset.target);
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.innerHTML = '<i class="fa-solid fa-eye' + (show ? '-slash' : '') + '"></i>';
        button.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
});
</script>
</body>
</html>
