<?php
/**
 * View: Change Password - Form đổi mật khẩu hoàn chỉnh có Header/Footer/Sidebar.
 */
require_once APP_ROOT . '/app/views/layouts/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar Menu (Left Column) -->
        <?php require_once APP_ROOT . '/app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Main Content (Right Column) -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Success/Error Messages -->
            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-xs" role="alert" style="border-radius: 12px;">
                    <i class="fa-solid fa-circle-check me-2"></i><strong>Thành công!</strong> <?= Session::get('success'); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-xs" role="alert" style="border-radius: 12px;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Lỗi!</strong> <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form Đổi mật khẩu -->
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <div class="card-body">
                    <h4 class="fw-bold text-dark mb-3"><i class="fa-solid fa-key text-primary me-2"></i>Đổi mật khẩu tài khoản</h4>
                    <p class="text-muted small mb-4">Hãy thay đổi mật khẩu định kỳ để nâng cao tính bảo mật cho tài khoản của bạn.</p>

                    <form action="<?= URL_ROOT ?>/nguoi-dung/change_password" method="POST" id="change-password-form">
                        <?= Csrf::field() ?>

                        <!-- Mật khẩu hiện tại -->
                        <div class="mb-3.5">
                            <label class="form-label fw-bold text-secondary fs-7">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control select-flat" id="current_password" name="current_password" required placeholder="Nhập mật khẩu đang dùng">
                                <button class="btn btn-outline-secondary toggle-pass-visibility select-flat" type="button" data-target="current_password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Mật khẩu mới -->
                        <div class="mb-3.5">
                            <label class="form-label fw-bold text-secondary fs-7">Mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control select-flat" id="new_password" name="new_password" required minlength="6" placeholder="Từ 6 ký tự trở lên">
                                <button class="btn btn-outline-secondary toggle-pass-visibility select-flat" type="button" data-target="new_password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Xác nhận mật khẩu mới -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary fs-7">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control select-flat" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Nhập lại mật khẩu mới">
                                <button class="btn btn-outline-secondary toggle-pass-visibility select-flat" type="button" data-target="confirm_password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tùy chọn đăng xuất thiết bị khác -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="logout_others" id="logout_others" value="1" checked>
                            <label class="form-check-label small text-dark fw-semibold" for="logout_others">
                                Đăng xuất khỏi tất cả các thiết bị khác (Trừ thiết bị hiện tại)
                            </label>
                            <div class="form-text small text-muted">
                                Hệ thống sẽ vô hiệu hóa tất cả các phiên đăng nhập khác của bạn ngay lập tức.
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                                <i class="fa-solid fa-key me-1.5"></i>Cập nhật mật khẩu mới
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Toggle ẩn/hiện mật khẩu
    document.querySelectorAll('.toggle-pass-visibility').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // 2. Kiểm tra mật khẩu khớp nhau ở phía client
    const form = document.getElementById('change-password-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if (newPass !== confirmPass) {
                e.preventDefault();
                Swal.fire({
                    title: 'Lỗi nhập liệu',
                    text: 'Mật khẩu xác nhận không khớp với mật khẩu mới.',
                    icon: 'error'
                });
            }
        });
    }
});
</script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
