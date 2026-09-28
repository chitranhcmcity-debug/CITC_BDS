<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="content-wrapper p-3 bg-light">
    <!-- Tiêu đề trang -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1"><i class="fa-solid fa-users text-primary me-2"></i>Quản lý người dùng</h1>
                    <p class="text-muted mb-0 small">Giám sát, phân quyền, cộng/trừ số dư ví, khóa tài khoản thành viên hệ thống.</p>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="<?= URL_ROOT ?>/admin/nguoi-dung/create" class="btn btn-primary fw-bold px-3 shadow-sm btn-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> Thêm người dùng mới
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Thống kê Analytics -->
    <section class="content mb-4 text-start">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Tổng số thành viên</span>
                    <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Đăng ký hôm nay</span>
                    <h4 class="fw-bold mb-0 text-primary"><?= number_format($stats['new_today']) ?></h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Đang hoạt động</span>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['active']) ?></h4>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Tài khoản bị khóa</span>
                    <h4 class="fw-bold mb-0 text-danger"><?= number_format($stats['locked']) ?></h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Danh sách người dùng -->
    <section class="content text-start">
        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-body">
                <!-- Form lọc tinh gọn với Bộ lọc nâng cao -->
                <form method="GET" action="" class="mb-4" style="font-size: 0.85rem;">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Từ khóa tìm kiếm</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Mã ID, Họ tên, Email, SĐT..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Vai trò</label>
                            <select name="role_id" class="form-select form-select-sm">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r->id ?>" <?= ($filters['role_id'] ?? '') == $r->id ? 'selected' : '' ?>><?= htmlspecialchars($r->ten) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Trạng thái</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- Tất cả --</option>
                                <option value="hoat_dong" <?= ($filters['status'] ?? '') === 'hoat_dong' ? 'selected' : '' ?>>Đang hoạt động</option>
                                <option value="da_khoa" <?= ($filters['status'] ?? '') === 'da_khoa' ? 'selected' : '' ?>>Đã khóa</option>
                                <option value="ngung_hoat_dong" <?= ($filters['status'] ?? '') === 'ngung_hoat_dong' ? 'selected' : '' ?>>Ngừng hoạt động</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-2" data-bs-toggle="collapse" data-bs-target="#advancedFilter" aria-expanded="false" title="Bộ lọc nâng cao">
                                <i class="fa-solid fa-sliders"></i>
                            </button>
                            <a href="<?= URL_ROOT ?>/admin/nguoi-dung" class="btn btn-sm btn-light border px-2 text-secondary" title="Reset"><i class="fa-solid fa-arrow-rotate-left"></i></a>
                        </div>
                    </div>

                    <!-- Bộ lọc nâng cao (Collapse) -->
                    <div class="collapse <?= ((isset($filters['email_verified']) && $filters['email_verified'] !== '') || (isset($filters['phone_verified']) && $filters['phone_verified'] !== '') || !empty($filters['min_balance'])) ? 'show' : '' ?>" id="advancedFilter">
                        <div class="row g-2 mt-2 pt-2 border-top">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Xác thực Email</label>
                                <select name="email_verified" class="form-select form-select-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="1" <?= ($filters['email_verified'] ?? '') === '1' ? 'selected' : '' ?>>Đã xác thực</option>
                                    <option value="0" <?= ($filters['email_verified'] ?? '') === '0' ? 'selected' : '' ?>>Chưa xác thực</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Xác thực SĐT</label>
                                <select name="phone_verified" class="form-select form-select-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="1" <?= ($filters['phone_verified'] ?? '') === '1' ? 'selected' : '' ?>>Đã xác thực</option>
                                    <option value="0" <?= ($filters['phone_verified'] ?? '') === '0' ? 'selected' : '' ?>>Chưa xác thực</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary mb-1">Số dư ví tối thiểu</label>
                                <input type="number" name="min_balance" class="form-control form-control-sm" placeholder="Ví dụ: 100000" value="<?= htmlspecialchars($filters['min_balance'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Bảng người dùng -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-secondary small fw-bold">
                            <tr>
                                <th class="ps-3" style="width: 70px;">ID</th>
                                <th style="width: 60px;">Avatar</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Vai trò</th>
                                <th>Số dư ví</th>
                                <th>Số tin đăng</th>
                                <th>Ngày tham gia</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-3" style="width: 100px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted small">Không tìm thấy người dùng nào khớp với bộ lọc tìm kiếm.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $user): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">#<?= $user->id ?></td>
                                        <td>
                                            <?php if (!empty($user->anh_dai_dien)): ?>
                                                <img src="<?= URL_ROOT ?>/public/uploads/avatars/<?= $user->anh_dai_dien ?>" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                                    <?= mb_strtoupper(mb_substr($user->ten, 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= URL_ROOT ?>/admin/nguoi-dung/detail/<?= $user->id ?>" class="text-dark fw-bold text-decoration-none">
                                                <?= htmlspecialchars($user->ten) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user->email) ?>
                                            <?php if ((int)$user->da_xac_thuc === 1): ?>
                                                <i class="fa-solid fa-circle-check text-success ms-1" title="Email đã xác thực"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user->dien_thoai ?: 'Chưa cập nhật') ?>
                                            <?php if ((int)$user->phone_verified === 1): ?>
                                                <i class="fa-solid fa-circle-check text-success ms-1" title="SĐT đã xác thực"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border"><?= htmlspecialchars($user->role_name ?: 'Thành viên') ?></span>
                                        </td>
                                        <td class="fw-bold text-dark"><?= number_format($user->so_du) ?> đ</td>
                                        <td class="fw-bold text-secondary text-center">
                                            <?= (int)($user->post_count ?? 0) ?>
                                        </td>
                                        <td>
                                            <span class="text-secondary small"><?= date('d/m/Y', strtotime($user->ngay_tao)) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $isLocked = !empty($user->locked_until) && strtotime($user->locked_until) > time();
                                            if ($isLocked) {
                                                echo '<span class="badge bg-danger">Đã khóa</span>';
                                            } elseif ($user->trang_thai === 'hoat_dong') {
                                                echo '<span class="badge bg-success">Hoạt động</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Ngừng hoạt động</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="<?= URL_ROOT ?>/admin/nguoi-dung/detail/<?= $user->id ?>" class="btn btn-xs btn-outline-info p-1 rounded" title="Xem chi tiết">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="<?= URL_ROOT ?>/admin/nguoi-dung/update/<?= $user->id ?>" class="btn btn-xs btn-outline-primary p-1 rounded" title="Chỉnh sửa">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="<?= URL_ROOT ?>/admin/nguoi-dung/delete/<?= $user->id ?>" method="POST" class="d-inline user-delete-form"
                                                      data-user-name="<?= htmlspecialchars($user->ten, ENT_QUOTES) ?>"
                                                      data-user-email="<?= htmlspecialchars($user->email, ENT_QUOTES) ?>">
                                                    <?= Csrf::field() ?>
                                                    <button type="submit" class="btn btn-xs btn-outline-danger p-1 rounded" title="Xóa tài khoản">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if ($totalPages > 1): ?>
                    <?php
                    $pageNumbers = [1, $totalPages];
                    for ($candidate = max(1, $page - 2); $candidate <= min($totalPages, $page + 2); $candidate++) {
                        $pageNumbers[] = $candidate;
                    }
                    $pageNumbers = array_values(array_unique($pageNumbers));
                    sort($pageNumbers);
                    $previousPage = null;
                    ?>
                    <nav class="mt-3">
                        <ul class="pagination pagination-sm justify-content-center flex-wrap gap-1">
                            <?php foreach ($pageNumbers as $i): ?>
                                <?php if ($previousPage !== null && $i > $previousPage + 1): ?>
                                    <li class="page-item disabled"><span class="page-link border-0">…</span></li>
                                <?php endif; ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                                <?php $previousPage = $i; ?>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-body p-4 p-md-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                     style="width: 72px; height: 72px; background: #fff1f2; color: #dc3545;">
                    <i class="fa-solid fa-trash-can fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2" id="deleteUserModalLabel">Xóa tài khoản?</h4>
                <p class="text-muted mb-3">Bạn đang chuẩn bị xóa vĩnh viễn tài khoản:</p>
                <div class="rounded-3 border bg-light p-3 mb-3 text-start">
                    <div class="fw-bold text-dark" id="deleteUserName"></div>
                    <div class="small text-muted" id="deleteUserEmail"></div>
                </div>
                <div class="alert alert-danger border-0 small text-start mb-4">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Dữ liệu liên quan của tài khoản cũng sẽ bị xóa và không thể khôi phục.
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light border fw-semibold px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="button" class="btn btn-danger fw-semibold px-4" id="confirmDeleteUser">
                        <i class="fa-solid fa-trash-can me-2"></i>Xóa tài khoản
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('deleteUserModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const confirmButton = document.getElementById('confirmDeleteUser');
    let pendingForm = null;

    document.querySelectorAll('.user-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1') return;
            event.preventDefault();
            pendingForm = form;
            document.getElementById('deleteUserName').textContent = form.dataset.userName || 'Người dùng';
            document.getElementById('deleteUserEmail').textContent = form.dataset.userEmail || '';
            modal.show();
        });
    });

    confirmButton.addEventListener('click', function () {
        if (!pendingForm) return;
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xóa...';
        pendingForm.dataset.confirmed = '1';
        pendingForm.submit();
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        pendingForm = null;
        confirmButton.disabled = false;
        confirmButton.innerHTML = '<i class="fa-solid fa-trash-can me-2"></i>Xóa tài khoản';
    });
});
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
