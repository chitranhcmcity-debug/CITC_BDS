@include('admin.layouts.header')

<div class="content-wrapper p-3 bg-light">
    <!-- Nút quay lại và Tiêu đề -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 mb-2">
                    <a href="<?= URL_ROOT ?>/admin/nguoi-dung/detail/<?= $user->id ?>" class="text-decoration-none text-secondary small fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại chi tiết tài khoản
                    </a>
                </div>
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1">Chỉnh sửa tài khoản #<?= $user->id ?></h1>
                    <p class="text-muted mb-0 small">Admin cập nhật thông tin hồ sơ của người dùng.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Biểu mẫu cập nhật -->
    <section class="content text-start">
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <form action="<?= URL_ROOT ?>/admin/nguoi-dung/update/<?= $user->id ?>" method="POST" class="card-body">
                <?= Csrf::field() ?>

                <div class="row g-3">
                    <!-- Tên & Email -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Họ và tên</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($user->ten) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Địa chỉ Email</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="<?= htmlspecialchars($user->email) ?>" required>
                    </div>

                    <!-- SĐT & Vai trò -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars($user->dien_thoai ?: '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Vai trò quyền hạn</label>
                        <select name="role_id" class="form-select form-select-sm" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r->id ?>" <?= (int)$user->ma_vai_tro === (int)$r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->ten) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ngày sinh, Giới tính, Nghề nghiệp -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Ngày sinh (YYYY-MM-DD)</label>
                        <input type="date" name="dob" class="form-control form-control-sm" value="<?= htmlspecialchars($user->ngay_sinh ?: '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Giới tính</label>
                        <select name="gender" class="form-select form-select-sm">
                            <option value="nam" <?= $user->gioi_tinh === 'nam' ? 'selected' : '' ?>>Nam</option>
                            <option value="nu" <?= $user->gioi_tinh === 'nu' ? 'selected' : '' ?>>Nữ</option>
                            <option value="khac" <?= $user->gioi_tinh === 'khac' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Nghề nghiệp</label>
                        <input type="text" name="job" class="form-control form-control-sm" value="<?= htmlspecialchars($user->nghe_nghiep ?: '') ?>">
                    </div>

                    <!-- Địa chỉ, Khu vực hoạt động -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Địa chỉ cư trú</label>
                        <input type="text" name="address" class="form-control form-control-sm" value="<?= htmlspecialchars($user->dia_chi ?: '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Khu vực môi giới chính</label>
                        <input type="text" name="region" class="form-control form-control-sm" value="<?= htmlspecialchars($user->khu_vuc_hoat_dong ?: '') ?>">
                    </div>

                    <!-- Trạng thái tài khoản -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Trạng thái hoạt động</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="hoat_dong" <?= $user->trang_thai === 'hoat_dong' ? 'selected' : '' ?>>Hoạt động bình thường</option>
                            <option value="ngung_hoat_dong" <?= $user->trang_thai === 'ngung_hoat_dong' ? 'selected' : '' ?>>Ngừng hoạt động / khóa</option>
                        </select>
                    </div>

                    <!-- Xác thực Email / Phone -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Trạng thái xác thực Email</label>
                        <select name="email_verified" class="form-select form-select-sm">
                            <option value="0" <?= (int)$user->da_xac_thuc === 0 ? 'selected' : '' ?>>Chưa xác thực</option>
                            <option value="1" <?= (int)$user->da_xac_thuc === 1 ? 'selected' : '' ?>>Đã xác thực</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Trạng thái xác thực Số điện thoại</label>
                        <select name="phone_verified" class="form-select form-select-sm">
                            <option value="0" <?= (int)$user->phone_verified === 0 ? 'selected' : '' ?>>Chưa xác thực</option>
                            <option value="1" <?= (int)$user->phone_verified === 1 ? 'selected' : '' ?>>Đã xác thực</option>
                        </select>
                    </div>

                    <!-- Mô tả bản thân -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary mb-1">Giới thiệu bản thân / Tiếu sử môi giới</label>
                        <textarea name="bio" rows="4" class="form-control form-control-sm text-secondary" style="font-size: 0.88rem;"><?= htmlspecialchars($user->mo_ta_ca_nhan ?: '') ?></textarea>
                    </div>
                </div>

                <!-- Submit -->
                <div class="border-top pt-3 mt-4 text-end">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-4"><i class="fa-solid fa-save me-1"></i>Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </section>
</div>

@include('admin.layouts.footer')
