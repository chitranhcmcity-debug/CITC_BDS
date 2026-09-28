@include('admin.layouts.header')

<div class="content-wrapper p-3 bg-light">
    <!-- Nút quay lại và Tiêu đề -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 mb-2">
                    <a href="<?= URL_ROOT ?>/admin/nguoi-dung" class="text-decoration-none text-secondary small fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                    </a>
                </div>
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1">Thêm người dùng mới</h1>
                    <p class="text-muted mb-0 small">Admin khởi tạo tài khoản thành viên hệ thống mới.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Biểu mẫu tạo mới -->
    <section class="content text-start">
        <?php if (isset($_SESSION['flash_errors'])): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                <ul class="mb-0 ps-3">
                    <?php foreach ($_SESSION['flash_errors'] as $err): ?>
                        <li><?= $err ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php unset($_SESSION['flash_errors']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php
        $old = $_SESSION['flash_old'] ?? [];
        unset($_SESSION['flash_old']);
        ?>

        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <form action="<?= URL_ROOT ?>/admin/nguoi-dung/create" method="POST" class="card-body">
                <?= Csrf::field() ?>

                <div class="row g-3">
                    <!-- Tên & Email -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Họ và tên</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Địa chỉ Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                    </div>

                    <!-- Mật khẩu & Số điện thoại -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Mật khẩu tài khoản (từ 6 ký tự)</label>
                        <input type="password" name="password" class="form-control form-control-sm" required minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                    </div>

                    <!-- Vai trò & Trạng thái -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Vai trò quyền hạn</label>
                        <select name="role_id" class="form-select form-select-sm" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->id ?>" <?= (int)($old['role_id'] ?? 3) === (int)$r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->ten) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Trạng thái ban đầu</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="hoat_dong">Hoạt động bình thường</option>
                            <option value="ngung_hoat_dong">Tạm ngừng hoạt động</option>
                        </select>
                    </div>

                    <!-- Xác thực Email / Phone -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Đánh dấu xác thực Email</label>
                        <select name="email_verified" class="form-select form-select-sm">
                            <option value="0">Chưa xác thực</option>
                            <option value="1">Đã xác thực</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Đánh dấu xác thực Số điện thoại</label>
                        <select name="phone_verified" class="form-select form-select-sm">
                            <option value="0">Chưa xác thực</option>
                            <option value="1">Đã xác thực</option>
                        </select>
                    </div>
                </div>

                <!-- Submit -->
                <div class="border-top pt-3 mt-4 text-end">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-4"><i class="fa-solid fa-plus-circle me-1"></i>Tạo tài khoản</button>
                </div>
            </form>
        </div>
    </section>
</div>

@include('admin.layouts.footer')
