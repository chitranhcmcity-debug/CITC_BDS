<?php
/**
 * Component: Sidebar Tin tức
 * Biến cần: $sidebar (array từ NewsService::getSidebar())
 * $sidebar['categories'], $sidebar['tags'], $sidebar['latest'], $sidebar['popular'], $sidebar['featured']
 */
$sb = $sidebar ?? [];
$sbCats     = $sb['categories'] ?? [];
$sbTags     = $sb['tags']       ?? [];
$sbLatest   = $sb['latest']     ?? [];
$sbPopular  = $sb['popular']    ?? [];
$sbFeatured = $sb['featured']   ?? [];

// Helper – URL ảnh
function sb_img(string $path): string {
    if (!$path) return 'https://placehold.co/66x50/e2e8f0/94a3b8?text=BDS';
    if (str_starts_with($path, 'http')) return $path;
    return URL_ROOT . '/public/' . ltrim($path, '/');
}
?>

<!-- ===== DANH MỤC ===== -->
<?php if ($sbCats): ?>
<div class="news-sidebar-card">
    <div class="sidebar-card-header">
        <i class="fas fa-folder-open"></i> Danh mục
    </div>
    <?php foreach ($sbCats as $cat): ?>
    <a href="<?= URL_ROOT ?>/tin-tuc/category/<?= htmlspecialchars($cat->duong_dan, ENT_QUOTES) ?>"
       class="sidebar-cat-item">
        <span>
            <i class="fas fa-chevron-right me-1" style="font-size:.65rem;opacity:.5;"></i>
            <?= htmlspecialchars($cat->ten, ENT_QUOTES) ?>
        </span>
        <span class="sidebar-cat-count"><?= (int)($cat->so_bai ?? 0) ?></span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ===== BÀI MỚI ===== -->
<?php if ($sbLatest): ?>
<div class="news-sidebar-card">
    <div class="sidebar-card-header">
        <i class="fas fa-clock"></i> Mới nhất
    </div>
    <?php foreach ($sbLatest as $p): ?>
    <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
       class="sidebar-list-item">
        <img src="<?= sb_img($p->anh_thu_nho ?? '') ?>" class="sidebar-thumb" alt="<?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>">
        <div>
            <div class="sidebar-item-title"><?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?></div>
            <div style="font-size:.7rem;color:#9ca3af;margin-top:3px;">
                <i class="fas fa-calendar-alt me-1"></i>
                <?= date('d/m/Y', strtotime($p->ngay_tao ?? 'now')) ?>
                <?php if (!empty($p->thoi_gian_doc)): ?>
                <span class="ms-2"><i class="fas fa-book-reader me-1"></i><?= (int)$p->thoi_gian_doc ?> phút</span>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ===== BÀI NỔI BẬT ===== -->
<?php if ($sbFeatured): ?>
<div class="news-sidebar-card">
    <div class="sidebar-card-header">
        <i class="fas fa-star"></i> Nổi bật
    </div>
    <?php foreach ($sbFeatured as $p): ?>
    <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
       class="sidebar-list-item">
        <img src="<?= sb_img($p->anh_thu_nho ?? '') ?>" class="sidebar-thumb" alt="<?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>">
        <div>
            <div class="sidebar-item-title"><?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?></div>
            <div style="font-size:.7rem;color:#9ca3af;margin-top:3px;">
                <i class="fas fa-eye me-1"></i><?= number_format((int)($p->luot_xem ?? 0)) ?> lượt xem
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ===== XEM NHIỀU ===== -->
<?php if ($sbPopular): ?>
<div class="news-sidebar-card">
    <div class="sidebar-card-header">
        <i class="fas fa-fire"></i> Xem nhiều
    </div>
    <?php foreach ($sbPopular as $idx => $p): ?>
    <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
       class="sidebar-list-item">
        <div class="d-flex align-items-center justify-content-center rounded-2 fw-900 me-1"
             style="width:28px;height:28px;background:<?= $idx < 3 ? 'var(--news-primary)' : '#e5e7eb' ?>;color:<?= $idx < 3 ? '#fff' : '#6b7280' ?>;font-size:.8rem;font-weight:800;flex-shrink:0;">
            <?= $idx + 1 ?>
        </div>
        <div>
            <div class="sidebar-item-title"><?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?></div>
            <div style="font-size:.7rem;color:#9ca3af;margin-top:3px;">
                <i class="fas fa-eye me-1"></i><?= number_format((int)($p->luot_xem ?? 0)) ?>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ===== TAG CLOUD ===== -->
<?php if ($sbTags): ?>
<div class="news-sidebar-card">
    <div class="sidebar-card-header">
        <i class="fas fa-tags"></i> Từ khóa phổ biến
    </div>
    <div class="p-3">
        <div class="tag-cloud">
            <?php foreach ($sbTags as $tag): ?>
            <a href="<?= URL_ROOT ?>/tin-tuc/tag/<?= htmlspecialchars($tag->duong_dan, ENT_QUOTES) ?>"
               class="tag-badge">
                #<?= htmlspecialchars($tag->ten, ENT_QUOTES) ?>
                <span class="tag-count">(<?= (int)($tag->so_bai ?? 0) ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== RSS ===== -->
<div class="news-sidebar-card">
    <div class="p-3 text-center">
        <a href="<?= URL_ROOT ?>/tin-tuc/rss" class="btn btn-outline-warning btn-sm rounded-pill w-100" target="_blank">
            <i class="fas fa-rss me-2"></i>Đăng ký RSS Feed
        </a>
    </div>
</div>
