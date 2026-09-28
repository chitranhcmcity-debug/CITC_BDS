<?php
/**
 * Component Hiển thị một mục thông báo riêng lẻ.
 */
$icon = $notif->icon ?: 'fa-bell';
$typeClass = 'notif-type-' . $notif->type;
$isRead = (int)$notif->is_read === 1;

// Tính thời gian thân thiện (Ví dụ: "5 phút trước", "2 giờ trước")
$timeAgo = '';
$diff = time() - strtotime($notif->created_at);
if ($diff < 60) {
    $timeAgo = 'Vừa xong';
} elseif ($diff < 3600) {
    $timeAgo = floor($diff / 60) . ' phút trước';
} elseif ($diff < 86400) {
    $timeAgo = floor($diff / 3600) . ' giờ trước';
} else {
    $timeAgo = date('H:i d/m/Y', strtotime($notif->created_at));
}
?>

<div class="notif-item d-flex align-items-start gap-3 <?= !$isRead ? 'unread' : '' ?>" id="notif-row-<?= $notif->id ?>">
    <!-- Icon của thông báo -->
    <div class="notif-icon-box <?= $typeClass ?>">
        <i class="fa-solid <?= htmlspecialchars($icon) ?>"></i>
    </div>

    <!-- Nội dung chính -->
    <div class="flex-grow-1 min-w-0">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <h6 class="fw-bold mb-1 text-dark text-truncate">
                <?php if ($notif->url): ?>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/notifications/<?= $notif->id ?>" class="text-decoration-none text-dark hover-primary">
                        <?= htmlspecialchars($notif->title) ?>
                    </a>
                <?php else: ?>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/notifications/<?= $notif->id ?>" class="text-decoration-none text-dark">
                        <?= htmlspecialchars($notif->title) ?>
                    </a>
                <?php endif; ?>
                <?php if (!$isRead): ?>
                    <span class="notif-dot ms-1 notif-pulse" title="Chưa đọc"></span>
                <?php endif; ?>
            </h6>
            
            <!-- Hành động nhanh -->
            <div class="dropdown notif-actions opacity-75">
                <button class="btn btn-link btn-sm text-muted p-0 border-0" type="button" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-1">
                    <?php if (!$isRead): ?>
                        <li>
                            <button onclick="notifAction(<?= $notif->id ?>, 'read')" class="dropdown-item py-1.5 small fw-semibold text-primary">
                                <i class="fa-regular fa-envelope-open me-2"></i> Đánh dấu đã đọc
                            </button>
                        </li>
                    <?php endif; ?>
                    <li>
                        <button onclick="notifAction(<?= $notif->id ?>, 'delete')" class="dropdown-item py-1.5 small fw-semibold text-danger">
                            <i class="fa-regular fa-trash-can me-2"></i> Xóa thông báo
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <p class="text-secondary small mb-1 text-wrap text-break">
            <?= htmlspecialchars($notif->content) ?>
        </p>
        <span class="text-muted fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.2px;">
            <i class="fa-regular fa-clock me-1"></i> <?= $timeAgo ?>
        </span>
    </div>
</div>
