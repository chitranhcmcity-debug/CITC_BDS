<?php
/**
 * View: So sánh bất động sản - So sánh chi tiết lên tới 4 BĐS.
 * URL: /nguoi-dung/compare
 */
require APP_ROOT . '/app/views/layouts/header.php';
?>
<div class="container py-5" style="min-height: 80vh;">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">So sánh bất động sản</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4.5 gap-3 border-bottom pb-3">
        <div>
            <h1 class="h2 fw-extrabold text-dark mb-1"><i class="fa-solid fa-code-compare text-primary me-2"></i>So sánh bất động sản</h1>
            <p class="text-muted mb-0">Đặt lên bàn cân các thông số kỹ thuật, giá cả, pháp lý và thiết kế nội thất.</p>
        </div>
        <?php if (!empty($items)): ?>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-xs" id="btn-clear-all-compare">
                <i class="fa-solid fa-trash-can me-1.5"></i>Xóa tất cả so sánh
            </button>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <?php if (empty($items)): ?>
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body">
                <img src="<?= URL_ROOT ?>/public/images/no-data.png" alt="No data" class="mb-3.5 img-fluid" style="max-height: 160px; filter: grayscale(0.5);">
                <h4 class="fw-bold text-dark mb-2">Bảng so sánh đang trống</h4>
                <p class="text-secondary mb-4 mx-auto" style="max-width: 420px;">
                    Bạn chưa chọn bất kỳ bất động sản nào để đối sánh. Hãy thêm tối đa 4 tin đăng từ kết quả tìm kiếm hoặc chi tiết sản phẩm.
                </p>
                <a href="<?= URL_ROOT ?>/du-an" class="btn btn-primary fw-bold rounded-pill px-4.5 shadow-sm">
                    <i class="fa-solid fa-magnifying-glass me-1.5"></i>Tìm kiếm Bất động sản
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="mb-3 small text-secondary">
            <i class="fa-solid fa-circle-info me-1"></i> Đang hiển thị so sánh đối chiếu giữa <strong><?= count($items) ?></strong> bất động sản (tối đa 4).
        </div>
        
        <!-- Gọi Component Bảng So Sánh -->
        <?php require APP_ROOT . '/app/views/components/compare-table.php'; ?>
        
    <?php endif; ?>
</div>

<!-- Form ẩn xóa tất cả (CSRF Safe) -->
<form id="form-delete-all-compare" action="<?= URL_ROOT ?>/nguoi-dung/removeAllCompare" method="POST" class="d-none">
    <?= Csrf::field() ?>
    <input type="hidden" name="_method" value="DELETE">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Nút dọn sạch so sánh
    const btnClearCompare = document.getElementById('btn-clear-all-compare');
    if (btnClearCompare) {
        btnClearCompare.addEventListener('click', function() {
            Swal.fire({
                title: 'Xóa toàn bộ danh sách so sánh?',
                text: "Hành động này sẽ làm trống bảng so sánh hiện tại.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa sạch',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-all-compare').submit();
                }
            });
        });
    }

    // Hiển thị thông báo Toast nếu có session thành công/thất bại
    <?php if(Session::get('success')): ?>
        Swal.fire({
            title: 'Thành công!',
            text: '<?= Session::get('success') ?>',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        <?php Session::delete('success'); ?>
    <?php endif; ?>

    <?php if(Session::get('error')): ?>
        Swal.fire({
            title: 'Lỗi!',
            text: '<?= Session::get('error') ?>',
            icon: 'error',
            timer: 2500,
            showConfirmButton: false
        });
        <?php Session::delete('error'); ?>
    <?php endif; ?>
});
</script>
<?php
require APP_ROOT . '/app/views/layouts/footer.php';
?>
