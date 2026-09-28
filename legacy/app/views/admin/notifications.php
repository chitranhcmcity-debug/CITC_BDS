<?php require_once '../app/views/admin/layouts/header.php'; ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/notifications.css?v=<?= filemtime(APP_ROOT . '/public/css/notifications.css') ?>">

<div class="content-wrapper p-3 bg-light">
    <!-- Tiêu đề trang -->
    <section class="content-header mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1"><i class="fa-solid fa-bullhorn text-primary me-2"></i>Quản lý thông báo hệ thống</h1>
                    <p class="text-muted mb-0 small">Gửi thông báo hàng loạt, lên lịch và theo dõi hiệu suất mở thông báo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Thống kê Analytics -->
    <section class="content mb-4">
        <div class="row g-3">
            <div class="col-lg-3 col-6">
                <div class="card admin-stat-card p-3" style="border-left: 4px solid #3b82f6 !important;">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1">Đã gửi thành công</span>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h3>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card admin-stat-card p-3" style="border-left: 4px solid #10b981 !important;">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1">Số lượt đã đọc</span>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['read_count']) ?></h3>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card admin-stat-card p-3" style="border-left: 4px solid #f59e0b !important;">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1">Số lượt chưa đọc</span>
                    <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['unread_count']) ?></h3>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="card admin-stat-card p-3" style="border-left: 4px solid #8b5cf6 !important;">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1">Tỷ lệ xem (Open Rate)</span>
                    <h3 class="fw-bold mb-0 text-primary"><?= $stats['open_rate'] ?>%</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Biểu mẫu gửi thông báo và Lịch sử gửi -->
    <section class="content">
        <div class="row g-4">
            <!-- Gửi thông báo mới -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="card-title fw-bold mb-0 text-dark"><i class="fa-solid fa-paper-plane text-success me-2"></i>Gửi thông báo mới</h5>
                    </div>
                    <div class="card-body">
                        <!-- Flash message -->
                        <?php if (isset($_SESSION['flash_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['flash_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?= URL_ROOT ?>/admin/notifications/send" method="POST">
                            <?= Csrf::field() ?>

                            <!-- Chọn đối tượng nhận -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-bold text-secondary mb-1">Đối tượng nhận thông báo</label>
                                <select name="target_type" id="targetTypeSelect" class="form-select form-select-sm" required>
                                    <option value="all">Gửi toàn bộ thành viên hệ thống (Broadcast)</option>
                                    <option value="role">Gửi theo nhóm vai trò (Role)</option>
                                    <option value="user">Gửi cho một thành viên cụ thể (User ID)</option>
                                </select>
                            </div>

                            <!-- Dropdown chọn vai trò (Ẩn mặc định) -->
                            <div class="mb-3 text-start d-none" id="roleSelectorBlock">
                                <label class="form-label small fw-bold text-secondary mb-1">Chọn vai trò nhận</label>
                                <select name="role_id" class="form-select form-select-sm">
                                    <option value="2">Môi giới / Thành viên (Member)</option>
                                    <option value="1">Quản trị viên (Admin)</option>
                                </select>
                            </div>

                            <!-- Ô nhập mã thành viên (Ẩn mặc định) -->
                            <div class="mb-3 text-start d-none" id="userSelectorBlock">
                                <label class="form-label small fw-bold text-secondary mb-1">Mã thành viên (User ID)</label>
                                <input type="number" name="user_id" class="form-control form-control-sm" placeholder="Nhập ID thành viên nhận...">
                            </div>

                            <!-- Chọn biểu tượng thông báo -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-bold text-secondary mb-1">Biểu tượng (Icon)</label>
                                <select name="icon" class="form-select form-select-sm">
                                    <option value="fa-bullhorn">📢 Loa phát thanh (Mặc định hệ thống)</option>
                                    <option value="fa-bell">🔔 Chuông thông báo</option>
                                    <option value="fa-circle-exclamation">⚠️ Cảnh báo quan trọng</option>
                                    <option value="fa-credit-card">💳 Giao dịch thanh toán</option>
                                    <option value="fa-shield-halved">🛡️ Bảo mật mật khẩu/thiết bị</option>
                                </select>
                            </div>

                            <!-- Tiêu đề -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-bold text-secondary mb-1">Tiêu đề thông báo</label>
                                <input type="text" name="title" class="form-control form-control-sm" placeholder="Nhập tiêu đề thông báo..." required>
                            </div>

                            <!-- Đường dẫn URL đính kèm -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-bold text-secondary mb-1">Đường dẫn chi tiết (URL - Không bắt buộc)</label>
                                <input type="text" name="url" class="form-control form-control-sm" placeholder="Ví dụ: /vi-dien-tu, /nguoi-dung/dashboard...">
                            </div>

                            <!-- Nội dung thông báo -->
                            <div class="mb-3 text-start">
                                <label class="form-label small fw-bold text-secondary mb-1">Nội dung chi tiết</label>
                                <textarea name="content" rows="4" class="form-control form-control-sm" placeholder="Nhập nội dung thông báo gửi đến người dùng..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-sm btn-success w-100 fw-bold py-2 shadow-sm">
                                <i class="fa-solid fa-paper-plane me-1"></i> Phát thông báo ngay
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lịch sử gửi/Thu hồi thông báo -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 pt-3">
                        <h5 class="card-title fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Thông báo đã gửi gần đây</h5>
                    </div>
                    <div class="card-body p-0 mt-2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                                <thead class="table-light text-secondary small fw-bold">
                                    <tr>
                                        <th class="ps-3" style="width: 70px;">ID</th>
                                        <th style="width: 80px;">Đối tượng</th>
                                        <th>Tiêu đề / Nội dung</th>
                                        <th style="width: 120px;">Thời gian</th>
                                        <th class="text-end pe-3" style="width: 100px;">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted small">
                                                Chưa có thông báo nào được phát đi.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent as $notif): ?>
                                            <tr>
                                                <td class="ps-3 fw-semibold text-secondary">#<?= $notif->id ?></td>
                                                <td>
                                                    <?php if ($notif->user_id === null): ?>
                                                        <span class="badge bg-primary">Toàn hệ thống</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">User #<?= $notif->user_id ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-start">
                                                    <strong class="d-block text-dark"><?= htmlspecialchars($notif->title) ?></strong>
                                                    <span class="text-muted text-truncate d-block small" style="max-width: 250px;">
                                                        <?= htmlspecialchars($notif->content) ?>
                                                    </span>
                                                </td>
                                                <td class="small text-muted"><?= date('H:i d/m/Y', strtotime($notif->created_at)) ?></td>
                                                <td class="text-end pe-3">
                                                    <form action="<?= URL_ROOT ?>/admin/notifications/retract/<?= $notif->id ?>" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn thu hồi thông báo này không?');">
                                                        <?= Csrf::field() ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 rounded" title="Thu hồi / Xóa">
                                                            <i class="fa-solid fa-rotate-left"></i> Thu hồi
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Phân trang danh sách -->
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer bg-white border-0 py-3 text-center">
                            <nav>
                                <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                            <a class="page-link rounded-3 border-0" href="?page=<?= $p ?>">
                                                <?= $p ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const targetSelect = document.getElementById("targetTypeSelect");
    const roleBlock = document.getElementById("roleSelectorBlock");
    const userBlock = document.getElementById("userSelectorBlock");

    targetSelect.addEventListener("change", function() {
        roleBlock.classList.add("d-none");
        userBlock.classList.add("d-none");

        if (this.value === 'role') {
            roleBlock.classList.remove("d-none");
        } else if (this.value === 'user') {
            userBlock.classList.remove("d-none");
        }
    });
});
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
