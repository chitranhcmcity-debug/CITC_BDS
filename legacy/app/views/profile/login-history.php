<?php
/**
 * View: Login History – Hiển thị danh sách lịch sử đăng nhập & Quản lý các phiên hoạt động.
 * @var array $history Danh sách bản ghi lịch sử
 * @var int $currentPage Trang hiện tại
 * @var int $totalPages Tổng số trang
 */
require_once APP_ROOT . '/app/views/layouts/header.php';

// Định dạng trạng thái tiếng Việt
function getStatusLabel(string $status): string {
    switch ($status) {
        case 'success':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5">Thành công</span>';
        case 'failed':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5">Thất bại</span>';
        case 'blocked':
            return '<span class="badge bg-dark-subtle text-dark border border-dark-subtle rounded-pill px-2.5">Bị khóa</span>';
        case 'logout':
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5">Đã đăng xuất</span>';
        default:
            return '<span class="badge bg-light text-dark rounded-pill px-2.5">' . htmlspecialchars($status) . '</span>';
    }
}
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

            <!-- Thiết bị đang hoạt động -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 g-2">
                        <div>
                            <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-shield-halved text-primary me-2"></i>Quản lý phiên đăng nhập</h4>
                            <p class="text-muted small mb-0">Xem các phiên đăng nhập thành công và buộc đăng xuất các thiết bị khác từ xa.</p>
                        </div>
                        
                        <div class="d-flex g-2">
                            <!-- Nút đăng xuất thiết bị khác -->
                            <form action="<?= URL_ROOT ?>/nguoi-dung/logoutAllDevice" method="POST" class="d-inline confirm-submit" data-message="Bạn có chắc chắn muốn đăng xuất tất cả các thiết bị khác không?">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="keep_current" value="1">
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold shadow-xs me-2">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i>Đăng xuất thiết bị khác
                                </button>
                            </form>

                            <!-- Nút đăng xuất tất cả -->
                            <form action="<?= URL_ROOT ?>/nguoi-dung/logoutAllDevice" method="POST" class="d-inline confirm-submit" data-message="Bạn sẽ bị đăng xuất khỏi tất cả các thiết bị bao gồm cả thiết bị hiện tại. Bạn có muốn tiếp tục?">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="keep_current" value="0">
                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold shadow-xs">
                                    <i class="fa-solid fa-power-off me-1"></i>Đăng xuất tất cả
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Thiết bị hiện tại -->
                    <div class="p-3 bg-light-subtle border border-primary-subtle rounded-3 mb-2 d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-desktop fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-0.5">Thiết bị hiện tại (Phiên này)</div>
                            <div class="small text-muted">
                                IP: <strong><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') ?></strong> · 
                                Hệ điều hành: <strong>Windows / Mac</strong> · 
                                Trình duyệt: <strong>Chrome/Safari</strong>
                            </div>
                        </div>
                        <span class="badge bg-primary rounded-pill ms-auto px-2.5">Đang hoạt động</span>
                    </div>
                </div>
            </div>

            <!-- Bảng lịch sử đăng nhập -->
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-check text-primary me-2"></i>Lịch sử đăng nhập</h5>
                    
                    <?php if (empty($history)): ?>
                        <div class="alert alert-light text-center py-4 rounded-3 border">
                            <i class="fa-solid fa-user-clock fs-2 text-muted mb-2"></i>
                            <p class="mb-0 text-muted">Chưa ghi nhận lịch sử đăng nhập nào cho tài khoản này.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light fs-7.5 text-secondary">
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Địa chỉ IP</th>
                                        <th>Hệ điều hành / Trình duyệt</th>
                                        <th>Thiết bị</th>
                                        <th>Địa điểm</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="fs-7.5">
                                    <?php foreach ($history as $row): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= date('d/m/Y H:i:s', strtotime($row->created_at)) ?></td>
                                            <td><code><?= htmlspecialchars($row->ip_address) ?></code></td>
                                            <td>
                                                <div><i class="fa-brands fa-windows text-primary me-1"></i><?= htmlspecialchars($row->os ?: 'Unknown') ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($row->browser ?: 'Unknown') ?></div>
                                            </td>
                                            <td>
                                                <?php if(($row->device_type ?? 'desktop') === 'mobile'): ?>
                                                    <i class="fa-solid fa-mobile-screen-button text-muted me-1"></i> Điện thoại
                                                <?php elseif(($row->device_type ?? 'desktop') === 'tablet'): ?>
                                                    <i class="fa-solid fa-tablet-screen-button text-muted me-1"></i> Máy tính bảng
                                                <?php else: ?>
                                                    <i class="fa-solid fa-desktop text-muted me-1"></i> Máy tính bàn
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                                <?= htmlspecialchars($row->location ?: 'Không rõ') ?>
                                            </td>
                                            <td><?= getStatusLabel($row->status) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Phân trang -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination pagination-sm justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                            <a class="page-link rounded-circle mx-1" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận khi submit form logout từ xa
    document.querySelectorAll('.confirm-submit').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-message');
            Swal.fire({
                title: 'Xác nhận hành động',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
});
</script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
