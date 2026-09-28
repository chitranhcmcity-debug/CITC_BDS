<?php
/**
 * View: Tin đã lưu (Yêu thích) - Trang hiển thị danh sách tin đã lưu của thành viên.
 * URL: /nguoi-dung/daLuu
 */
@include('layouts.header')
?>
<div class="container py-5" style="min-height: 80vh;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/nguoi-dung/dashboard" class="text-decoration-none">Thành viên</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tin đã lưu</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4.5 gap-3 border-bottom pb-3">
        <div>
            <h1 class="h2 fw-extrabold text-dark mb-1"><i class="fa-solid fa-heart text-danger me-2"></i>Tin đăng đã lưu</h1>
            <p class="text-muted mb-0">Quản lý và lọc danh sách tin đăng bất động sản bạn đang quan tâm.</p>
        </div>
        <?php if (!empty($favorites)): ?>
            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-xs" id="btn-clear-all-fav">
                <i class="fa-solid fa-trash-can me-1.5"></i>Bỏ lưu tất cả
            </button>
        <?php endif; ?>
    </div>

    <!-- Layout Lưới: Trái Sidebar Bộ lọc, Phải là grid danh sách -->
    <div class="row g-4">
        <!-- Sidebar Bộ lọc -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm p-4 sticky-top" style="border-radius: 12px; top: 90px; background: #ffffff;">
                <h5 class="fw-bold mb-3.5"><i class="fa-solid fa-filter text-primary me-2"></i>Bộ lọc tìm kiếm</h5>
                <form action="<?= URL_ROOT ?>/nguoi-dung/daLuu" method="GET" id="filter-form">
                    
                    <!-- Loại giao dịch -->
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold text-secondary">Loại giao dịch</label>
                        <select class="form-select select-flat" name="transaction_type" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="ban" <?= ($filters['transaction_type'] ?? '') === 'ban' ? 'selected' : '' ?>>Mua Bán</option>
                            <option value="cho_thue" <?= ($filters['transaction_type'] ?? '') === 'cho_thue' ? 'selected' : '' ?>>Cho Thuê</option>
                        </select>
                    </div>

                    <!-- Gói tin VIP -->
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold text-secondary">Gói VIP</label>
                        <select class="form-select select-flat" name="vip_level" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="0" <?= (isset($filters['vip_level']) && $filters['vip_level'] === 0) ? 'selected' : '' ?>>Tin Thường</option>
                            <option value="1" <?= ($filters['vip_level'] ?? '') === 1 ? 'selected' : '' ?>>VIP 1</option>
                            <option value="2" <?= ($filters['vip_level'] ?? '') === 2 ? 'selected' : '' ?>>VIP 2</option>
                            <option value="3" <?= ($filters['vip_level'] ?? '') === 3 ? 'selected' : '' ?>>VIP 3</option>
                            <option value="4" <?= ($filters['vip_level'] ?? '') === 4 ? 'selected' : '' ?>>VIP Đặc biệt</option>
                        </select>
                    </div>

                    <!-- Khoảng giá -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary mb-2">Khoảng giá (VNĐ)</label>
                        <div class="d-flex align-items-center gap-1.5 mb-2">
                            <input type="number" class="form-control form-control-sm select-flat" name="price_min" placeholder="Từ" value="<?= htmlspecialchars((string)($filters['price_min'] ?? '')) ?>">
                            <span class="text-muted">-</span>
                            <input type="number" class="form-control form-control-sm select-flat" name="price_max" placeholder="Đến" value="<?= htmlspecialchars((string)($filters['price_max'] ?? '')) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold rounded-pill">Áp dụng giá</button>
                    </div>

                    <a href="<?= URL_ROOT ?>/nguoi-dung/daLuu" class="btn btn-outline-secondary btn-sm w-100 fw-bold rounded-pill">
                        <i class="fa-solid fa-rotate-left me-1"></i>Đặt lại
                    </a>
                </form>
            </div>
        </div>

        <!-- Grid Danh sách tin -->
        <div class="col-lg-9">
            <?php if (empty($favorites)): ?>
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-body">
                        <img src="<?= URL_ROOT ?>/public/images/no-data.png" alt="No data" class="mb-3.5 img-fluid" style="max-height: 160px; filter: grayscale(0.5);">
                        <h4 class="fw-bold text-dark mb-2">Không tìm thấy tin đã lưu</h4>
                        <p class="text-secondary mb-4 mx-auto" style="max-width: 420px;">
                            Bạn chưa lưu tin đăng bất động sản nào hoặc bộ lọc hiện tại không tìm thấy kết quả phù hợp.
                        </p>
                        <a href="<?= URL_ROOT ?>/du-an" class="btn btn-primary fw-bold rounded-pill px-4.5 shadow-sm">
                            <i class="fa-solid fa-magnifying-glass me-1.5"></i>Khám phá Tin đăng ngay
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    <?php foreach ($favorites as $post): ?>
                        <div class="col" id="property-card-wrapper-<?= $post->id ?>">
                            <?php require APP_ROOT . '/app/views/components/property-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Forms ẩn để gửi Request (An toàn CSRF) -->
<form id="form-delete-all-fav" action="<?= URL_ROOT ?>/nguoi-dung/removeAllLuu" method="POST" class="d-none">
    <?= Csrf::field() ?>
    <input type="hidden" name="_method" value="DELETE">
</form>

<form id="form-delete-single-fav" action="" method="POST" class="d-none">
    <?= Csrf::field() ?>
    <input type="hidden" name="_method" value="DELETE">
</form>

<!-- Scripts xử lý xóa / chia sẻ bằng SweetAlert2 -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Nút bỏ lưu nhanh
    document.querySelectorAll('.btn-remove-fav').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-id');
            const cardWrapper = document.getElementById('property-card-wrapper-' + postId);

            Swal.fire({
                title: 'Bỏ lưu tin đăng?',
                text: "Tin đăng này sẽ được xóa khỏi danh sách yêu thích của bạn.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý bỏ lưu',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Gọi API bỏ lưu thông qua POST/DELETE
                    const form = document.getElementById('form-delete-single-fav');
                    form.action = '<?= URL_ROOT ?>/nguoi-dung/removeLuu/' + postId;
                    form.submit();
                }
            });
        });
    });

    // 2. Nút xóa tất cả
    const btnClearAll = document.getElementById('btn-clear-all-fav');
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function() {
            Swal.fire({
                title: 'Xóa toàn bộ tin đã lưu?',
                text: "Hành động này sẽ dọn sạch tất cả tin đăng bất động sản trong danh sách yêu thích của bạn.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa sạch tất cả',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete-all-fav').submit();
                }
            });
        });
    }

    // 3. Nút chia sẻ link nhanh
    document.querySelectorAll('.btn-share-link').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            navigator.clipboard.writeText(url).then(() => {
                Swal.fire({
                    title: 'Đã sao chép liên kết!',
                    text: 'Link chia sẻ chi tiết tin đăng đã được lưu vào khay nhớ tạm.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch(err => {
                Swal.fire({
                    title: 'Sao chép liên kết thất bại',
                    text: url,
                    icon: 'info'
                });
            });
        });
    });

    // Hiển thị Alert nếu có session message trả về từ controller
    <?php if(Session::get('success')): ?>
        Swal.fire({
            title: 'Thành công!',
            text: '<?= Session::get('success') ?>',
            icon: 'success',
            timer: 2500,
            showConfirmButton: false
        });
        <?php Session::delete('success'); ?>
    <?php endif; ?>

    <?php if(Session::get('error')): ?>
        Swal.fire({
            title: 'Thất bại!',
            text: '<?= Session::get('error') ?>',
            icon: 'error',
            timer: 3000,
            showConfirmButton: false
        });
        <?php Session::delete('error'); ?>
    <?php endif; ?>
});
</script>
@include('layouts.footer')
