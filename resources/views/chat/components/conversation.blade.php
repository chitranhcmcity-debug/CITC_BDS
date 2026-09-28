<?php
/**
 * Component Hiển thị một cuộc hội thoại trong danh sách sidebar.
 */
$isActive = isset($activeConvId) && (int)$conv->id === (int)$activeConvId;

// Xác định tên đối tác hiển thị
$partnerName = 'Hội thoại hỗ trợ';
if ($conv->type === 'customer_seller') {
    $partnerName = $conv->seller_name ?: 'Người đăng tin';
} elseif ($conv->type === 'customer_cskh') {
    $partnerName = $conv->staff_name ? 'CSKH: ' . $conv->staff_name : 'Tổng đài viên CSKH';
}

$lastActive = date('H:i d/m', strtotime($conv->updated_at));
?>

<div onclick="selectConversation(<?= $conv->id ?>)" 
     class="chat-sidebar-item d-flex align-items-center gap-3 <?= $isActive ? 'active' : '' ?>" 
     id="conv-item-<?= $conv->id ?>">
    <!-- Avatar hoặc biểu tượng đại diện -->
    <div class="position-relative flex-shrink-0">
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-secondary fw-bold" style="width: 44px; height: 44px;">
            <?= mb_strtoupper(mb_substr($partnerName, 0, 1, 'UTF-8')) ?>
        </div>
    </div>

    <!-- Nội dung mô tả -->
    <div class="flex-grow-1 min-w-0 text-start">
        <div class="d-flex justify-content-between align-items-baseline mb-0.5">
            <h6 class="fw-bold mb-0 text-dark small text-truncate"><?= htmlspecialchars($partnerName) ?></h6>
            <span class="text-muted" style="font-size: 0.7rem;"><?= $lastActive ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted small text-truncate d-block" style="max-width: 170px;">
                <?= $conv->title ?: 'Đang chờ hỗ trợ...' ?>
            </span>
            <?php if ($conv->status === 'closed'): ?>
                <span class="badge bg-secondary p-1 small" style="font-size: 0.65rem;">Đã đóng</span>
            <?php endif; ?>
        </div>
    </div>
</div>
