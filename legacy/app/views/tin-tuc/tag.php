<?php
/**
 * View: Tag bài viết
 * Route: GET /tin-tuc/tag/{slug}
 * Biến: $tag, $posts, $pagination, $sidebar, $title, $meta_desc, $canonical
 */
$pageNum    = $pagination['currentPage'] ?? 1;
$totalPages = $pagination['totalPages']  ?? 1;
$total      = $pagination['total']       ?? 0;
$tagSlug    = $tag->duong_dan ?? '';
$baseUrl    = URL_ROOT . '/tin-tuc/tag/' . $tagSlug;
?>
<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>
<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">
<meta name="description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES) ?>">
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/news.css?v=<?= filemtime(APP_ROOT.'/public/css/news.css') ?>">

<div class="container-xl" style="padding-top:12px;">
    <nav aria-label="breadcrumb" style="font-size:.82rem;">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/tin-tuc">Tin tức</a></li>
            <li class="breadcrumb-item active">#<?= htmlspecialchars($tag->ten ?? '', ENT_QUOTES) ?></li>
        </ol>
    </nav>
</div>

<div class="section-hero mb-4">
    <div class="container-xl">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;background:rgba(255,255,255,.15);font-size:1.4rem;">
                <i class="fas fa-tag"></i>
            </div>
            <div>
                <h1 class="mb-1">#<?= htmlspecialchars($tag->ten ?? '', ENT_QUOTES) ?></h1>
                <p class="mb-0 opacity-75" style="font-size:.88rem;">
                    <?= number_format($total) ?> bài viết với thẻ này
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container-xl pb-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <?php if (empty($posts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-tag fa-3x mb-3 opacity-25"></i>
                <h5>Chưa có bài viết nào với thẻ này</h5>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($posts as $p): ?>
                <div class="col-sm-6">
                    <div class="news-card h-100">
                        <div class="news-card-img">
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>">
                                <?php $img = !empty($p->anh_thu_nho)
                                    ? (str_starts_with($p->anh_thu_nho, 'http') ? $p->anh_thu_nho : URL_ROOT . '/public/' . ltrim($p->anh_thu_nho, '/'))
                                    : 'https://placehold.co/400x220/e2e8f0/94a3b8?text=BDS'; ?>
                                <img src="<?= $img ?>" alt="<?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="news-card-body">
                            <?php if (!empty($p->slug_danh_muc)): ?>
                            <a href="<?= URL_ROOT ?>/tin-tuc/category/<?= htmlspecialchars($p->slug_danh_muc, ENT_QUOTES) ?>"
                               class="news-card-cat"><?= htmlspecialchars($p->ten_danh_muc, ENT_QUOTES) ?></a>
                            <?php endif; ?>
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
                               class="d-block text-decoration-none news-card-title">
                                <?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>
                            </a>
                            <div class="news-card-meta">
                                <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($p->ngay_tao ?? 'now')) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format((int)($p->luot_xem ?? 0)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="Phân trang">
                <ul class="pagination justify-content-center news-pagination">
                    <?php if ($pageNum > 1): ?>
                    <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>?page=<?= $pageNum - 1 ?>"><i class="fas fa-chevron-left"></i></a></li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $pageNum - 2); $i <= min($totalPages, $pageNum + 2); $i++): ?>
                    <li class="page-item <?= $i === $pageNum ? 'active' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($pageNum < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>?page=<?= $pageNum + 1 ?>"><i class="fas fa-chevron-right"></i></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <?php require_once APP_ROOT . '/app/views/tin-tuc/components/sidebar.php'; ?>
        </div>
    </div>
</div>
<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
