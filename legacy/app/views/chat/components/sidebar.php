<?php
/**
 * Component Hiển thị Thông tin Đối tác trò chuyện & Các nút thao tác nhanh.
 */
if (!isset($activeConv) || !$activeConv) {
    return;
}

$partnerName = 'Hội thoại hỗ trợ';
$partnerInfo = 'TimNhaDat.site Support Team';
$partnerIcon = 'fa-headset';

if ($activeConv->type === 'customer_seller') {
    $partnerName = $activeConv->seller_name ?: 'Người đăng tin';
    $partnerInfo = 'Đối tác liên hệ Bất động sản';
    $partnerIcon = 'fa-user-tie';
} elseif ($activeConv->type === 'customer_cskh') {
    $partnerName = $activeConv->staff_name ?: 'Tổng đài viên';
    $partnerInfo = 'Chăm sóc khách hàng hệ thống';
    $partnerIcon = 'fa-headset';
}
?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body text-center p-4">
        <!-- Icon đại diện -->
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary mx-auto mb-3" 
             style="width: 70px; height: 70px; font-size: 2.2rem;">
            <i class="fa-solid <?= $partnerIcon ?>"></i>
        </div>
        
        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($partnerName) ?></h5>
        <p class="text-muted small mb-3"><?= htmlspecialchars($partnerInfo) ?></p>

        <hr class="my-3 text-muted opacity-25">

        <!-- Danh sách thông tin chi tiết -->
        <div class="text-start small mb-4">
            <div class="mb-2">
                <span class="text-secondary fw-bold">Mã hội thoại:</span> 
                <span class="text-dark float-end">#<?= $activeConv->id ?></span>
            </div>
            <div class="mb-2">
                <span class="text-secondary fw-bold">Trạng thái:</span> 
                <span class="badge bg-<?= $activeConv->status === 'closed' ? 'secondary' : 'success' ?> float-end">
                    <?= $activeConv->status === 'closed' ? 'Đã đóng' : 'Đang mở' ?>
                </span>
            </div>
            <div>
                <span class="text-secondary fw-bold">Thời gian tạo:</span> 
                <span class="text-dark float-end"><?= date('H:i d/m/Y', strtotime($activeConv->created_at)) ?></span>
            </div>
        </div>

        <!-- Các hành động thao tác (chỉ hiện đối với Admin/CSKH nếu xem ở giao diện admin) -->
        <?php if (Session::get('user_role_id') === 1 && str_contains($_SERVER['REQUEST_URI'], 'admin/livechat')): ?>
            <div class="d-grid gap-2">
                <?php if ($activeConv->status !== 'closed'): ?>
                    <!-- Nút Chuyển CSKH -->
                    <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#transferChatModal">
                        <i class="fa-solid fa-arrows-spin me-1"></i> Chuyển CSKH
                    </button>
                    <!-- Nút Đóng hội thoại -->
                    <form action="<?= URL_ROOT ?>/admin/livechat/close/<?= $activeConv->id ?>" method="POST" onsubmit="return confirm('Bạn có chắc muốn kết thúc cuộc hỗ trợ này?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold w-100">
                            <i class="fa-solid fa-circle-check me-1"></i> Đóng hội thoại
                        </button>
                    </form>
                <?php endif; ?>
                
                <!-- Nút Xuất lịch sử -->
                <a href="<?= URL_ROOT ?>/admin/livechat/export/<?= $activeConv->id ?>" class="btn btn-sm btn-outline-secondary fw-bold">
                    <i class="fa-solid fa-download me-1"></i> Xuất lịch sử chat
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
