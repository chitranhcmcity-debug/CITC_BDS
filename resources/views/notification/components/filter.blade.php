<?php
/**
 * Component Bộ lọc thông báo (Tất cả, Chưa đọc, Đã đọc, Thanh toán, Tin đăng, Chat, Bảo mật, Hệ thống).
 */
$currentFilter = $filter ?? 'all';
$filters = [
    'all'        => ['label' => 'Tất cả', 'icon' => 'fa-list'],
    'unread'     => ['label' => 'Chưa đọc', 'icon' => 'fa-envelope'],
    'read'       => ['label' => 'Đã đọc', 'icon' => 'fa-envelope-open'],
    'he_thong'   => ['label' => 'Hệ thống', 'icon' => 'fa-bullhorn'],
    'tin_dang'   => ['label' => 'Tin đăng', 'icon' => 'fa-star'],
    'thanh_toan' => ['label' => 'Thanh toán', 'icon' => 'fa-credit-card'],
    'chat'       => ['label' => 'Chat', 'icon' => 'fa-comments'],
    'bao_mat'    => ['label' => 'Bảo mật', 'icon' => 'fa-shield-halved']
];
?>

<div class="d-flex flex-wrap gap-2 mb-3">
    <?php foreach ($filters as $key => $cfg): ?>
        <a href="?filter=<?= $key ?>&search=<?= urlencode($search ?? '') ?>" 
           class="btn btn-sm notif-filter-btn <?= $currentFilter === $key ? 'active' : '' ?>">
            <i class="fa-solid <?= $cfg['icon'] ?> me-1"></i> <?= $cfg['label'] ?>
        </a>
    <?php endforeach; ?>
</div>
