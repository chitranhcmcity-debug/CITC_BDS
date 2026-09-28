<?php
/**
 * View: Đăng ký tài khoản – /nguoi-dung/register
 */
$pageTitle  = $title ?? 'Đăng ký tài khoản – ' . SITE_NAME;
$csrf_token = Csrf::token();
$errors     = $errors ?? [];
$old        = $old    ?? [];
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
<div class="auth-split">
    <!-- ── LEFT: Hero Panel ── -->
    <div class="auth-hero d-none d-lg-flex flex-column">
        <a href="<?= URL_ROOT ?>" class="auth-hero-logo">🏠 Tim<span>NhaDat</span>.site</a>
        <h1 class="auth-hero-title">Tham gia cộng đồng BĐS lớn nhất!</h1>
        <p class="auth-hero-sub">
            Hàng ngàn bất động sản đang chờ bạn. Đăng ký miễn phí và đăng tin ngay hôm nay.
        </p>
        <ul class="auth-feature-list">
            <li><i class="fas fa-home"></i> Đăng tin BĐS miễn phí</li>
            <li><i class="fas fa-search"></i> Tìm kiếm nhanh hàng ngàn tin</li>
            <li><i class="fas fa-star"></i> Gói VIP tăng hiển thị</li>
            <li><i class="fas fa-lock"></i> Thông tin bảo mật tuyệt đối</li>
        </ul>
    </div>

    <!-- ── RIGHT: Form Panel ── -->
    <div class="auth-form-panel" style="overflow-y:auto; max-height:100vh;">
        <div class="d-lg-none text-center mb-4">
            <a href="<?= URL_ROOT ?>" class="text-decoration-none fw-bold fs-4" style="color:#2563eb;">
                🏠 TimNhaDat.site
            </a>
        </div>

        <div class="auth-form-header">
            <h1>Tạo tài khoản mới</h1>
            <p>Điền thông tin bên dưới để đăng ký</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
        <div class="auth-alert danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($errors['general']) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/register" class="auth-form">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

            <!-- Họ tên -->
            <div class="auth-field">
                <label for="ten">Họ và tên <span class="text-danger">*</span></label>
                <div class="auth-input-wrap">
                    <i class="fas fa-user auth-icon"></i>
                    <input type="text" id="ten" name="ten"
                        class="auth-input <?= !empty($errors['ten']) ? 'is-invalid' : '' ?>"
                        placeholder="Nguyễn Văn A"
                        value="<?= htmlspecialchars($old['ten'] ?? '') ?>"
                        autocomplete="name" maxlength="100">
                </div>
                <?php if (!empty($errors['ten'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['ten']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="auth-field">
                <label for="email">Địa chỉ Email <span class="text-danger">*</span></label>
                <div class="auth-input-wrap">
                    <i class="fas fa-envelope auth-icon"></i>
                    <input type="email" id="email" name="email"
                        class="auth-input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                        placeholder="email@example.com"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        data-validate autocomplete="email">
                </div>
                <?php if (!empty($errors['email'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Số điện thoại -->
            <div class="auth-field">
                <label for="dien_thoai">Số điện thoại</label>
                <div class="auth-input-wrap">
                    <i class="fas fa-phone auth-icon"></i>
                    <input type="tel" id="dien_thoai" name="dien_thoai"
                        class="auth-input <?= !empty($errors['dien_thoai']) ? 'is-invalid' : '' ?>"
                        placeholder="0901234567"
                        value="<?= htmlspecialchars($old['dien_thoai'] ?? '') ?>"
                        data-validate="phone" autocomplete="tel" maxlength="15">
                </div>
                <?php if (!empty($errors['dien_thoai'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['dien_thoai']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Mật khẩu -->
            <div class="auth-field">
                <label for="mat_khau">Mật khẩu <span class="text-danger">*</span></label>
                <div class="auth-input-wrap">
                    <i class="fas fa-lock auth-icon"></i>
                    <input type="password" id="mat_khau" name="mat_khau"
                        class="auth-input <?= !empty($errors['mat_khau']) ? 'is-invalid' : '' ?>"
                        placeholder="Ít nhất 8 ký tự"
                        autocomplete="new-password">
                    <button type="button" class="auth-toggle-pass" data-target="mat_khau"><i class="fas fa-eye"></i></button>
                </div>
                <?php if (!empty($errors['mat_khau'])): ?>
                <div class="auth-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['mat_khau']) ?></div>
                <?php endif; ?>
                <!-- Password strength -->
                <div class="pw-strength-wrap">
                    <div class="pw-strength-bar" id="pw-strength-bar">
                        <div class="pw-strength-fill"></div>
                    </div>
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

            <!-- Nhập lại mật khẩu -->
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

            <!-- Terms -->
            <div class="auth-terms <?= !empty($errors['dong_y_dieu_khoan']) ? 'text-danger' : '' ?>">
                <input type="checkbox" name="dong_y_dieu_khoan" id="dong_y_dieu_khoan" value="1"
                    <?= !empty($old['dong_y_dieu_khoan']) ? 'checked' : '' ?>>
                <label for="dong_y_dieu_khoan">
                    Tôi đồng ý với <a href="<?= URL_ROOT ?>/dieu-khoan" target="_blank">Điều khoản sử dụng</a>
                    và <a href="<?= URL_ROOT ?>/chinh-sach-bao-mat" target="_blank">Chính sách bảo mật</a>
                </label>
            </div>
            <?php if (!empty($errors['dong_y_dieu_khoan'])): ?>
            <div class="auth-error mb-2"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors['dong_y_dieu_khoan']) ?></div>
            <?php endif; ?>

            <!-- Submit -->
            <button type="submit" class="auth-btn">
                <span class="btn-text"><i class="fas fa-user-plus me-2"></i>Tạo tài khoản</span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="auth-form-footer mt-3">
            Đã có tài khoản?
            <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap">Đăng nhập ngay</a>
        </div>
        <div class="auth-form-footer" style="margin-top:8px;">
            <a href="<?= URL_ROOT ?>" style="color:#6b7280;font-size:.78rem;">
                <i class="fas fa-home me-1"></i>Về trang chủ
            </a>
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
