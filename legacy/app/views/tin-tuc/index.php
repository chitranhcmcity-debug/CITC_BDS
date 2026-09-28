<?php
/**
 * View: Danh sách Tin tức
 * Route: GET /tin-tuc
 * Biến: $posts, $featured, $pagination, $filters, $sidebar, $title, $meta_desc, $canonical
 */

$pageNum    = $pagination['currentPage'] ?? 1;
$totalPages = $pagination['totalPages']  ?? 1;
$total      = $pagination['total']       ?? 0;
$baseUrl    = URL_ROOT . '/tin-tuc';

function news_img(string $path, string $fallback = ''): string {
    if (!$path) return $fallback ?: 'https://placehold.co/400x240/e2e8f0/94a3b8?text=Tin+tuc';
    if (str_starts_with($path, 'http')) return $path;
    return URL_ROOT . '/public/' . ltrim($path, '/');
}
function news_date(string $ts): string {
    return date('d/m/Y', strtotime($ts));
}
?>
<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<!-- SEO Extras -->
<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">
<meta name="description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES) ?>">
<meta property="og:title"       content="<?= htmlspecialchars($title, ENT_QUOTES) ?>">
<meta property="og:description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES) ?>">
<meta property="og:type"        content="website">
<link rel="alternate" type="application/rss+xml" title="RSS Tin tức BĐS" href="<?= URL_ROOT ?>/tin-tuc/rss">

<!-- News CSS -->
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/news.css?v=<?= filemtime(APP_ROOT.'/public/css/news.css') ?>">

<!-- Breadcrumb -->
<div class="container-xl" style="padding-top:12px;">
    <nav aria-label="breadcrumb" style="font-size:.82rem;">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>">Trang chủ</a></li>
            <li class="breadcrumb-item active">Tin tức</li>
        </ol>
    </nav>
</div>

<!-- ── PAGE HEADER ── -->
<div class="py-3 mb-4" style="border-bottom:1px solid #e5e7eb;">
    <div class="container-xl">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-800 mb-1" style="color:#111827;">
                    <i class="fas fa-newspaper text-primary me-2"></i>Tin tức Bất động sản
                </h1>
                <p class="mb-0 text-muted" style="font-size:.88rem;">
                    Cập nhật mới nhất về thị trường, pháp lý và xu hướng BĐS Việt Nam
                    <?php if ($total): ?>• <strong><?= number_format($total) ?></strong> bài viết<?php endif; ?>
                </p>
            </div>
            <!-- Search box inline -->
            <div class="position-relative" style="min-width:240px;max-width:320px;">
                <form action="<?= URL_ROOT ?>/tin-tuc/search" method="GET">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control rounded-start-pill"
                               id="searchInput" placeholder="Tìm kiếm..."
                               value="<?= htmlspecialchars($filters['keyword'] ?? '', ENT_QUOTES) ?>">
                        <button class="btn btn-primary rounded-end-pill" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                <div id="searchAutocomplete" class="search-autocomplete"></div>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
            <?php $sorts = ['new'=>'Mới nhất','popular'=>'Xem nhiều','liked'=>'Thích nhất']; ?>
            <?php foreach ($sorts as $key => $label): ?>
            <a href="<?= $baseUrl ?>?sort=<?= $key ?>"
               class="btn btn-sm <?= ($filters['sort'] ?? 'new') === $key ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── MAIN CONTENT ── -->
<div class="container-xl pb-5">
    <div class="row g-4">

        <!-- LEFT: Articles -->
        <div class="col-lg-8">

            <!-- HERO FEATURED POST (trang 1, không filter) -->
            <?php if ($featured && $pageNum === 1 && empty($filters['keyword']) && empty($filters['category_id'])): ?>
            <div class="mb-4" data-aos="fade-up">
                <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($featured->duong_dan, ENT_QUOTES) ?>"
                   class="news-hero-card d-block">
                    <img src="<?= news_img($featured->anh_thu_nho ?? '') ?>"
                         alt="<?= htmlspecialchars($featured->tieu_de, ENT_QUOTES) ?>"
                         loading="lazy">
                    <div class="news-hero-overlay"></div>
                    <div class="news-hero-body">
                        <?php if (!empty($featured->ten_danh_muc)): ?>
                        <span class="badge mb-2" style="background:rgba(37,99,235,.8);font-size:.72rem;">
                            <?= htmlspecialchars($featured->ten_danh_muc, ENT_QUOTES) ?>
                        </span>
                        <?php endif; ?>
                        <div class="news-hero-title"><?= htmlspecialchars($featured->tieu_de, ENT_QUOTES) ?></div>
                        <div class="mt-2 d-flex gap-3" style="font-size:.78rem;opacity:.85;">
                            <span><i class="fas fa-calendar me-1"></i><?= news_date($featured->ngay_tao ?? 'now') ?></span>
                            <span><i class="fas fa-eye me-1"></i><?= number_format((int)($featured->luot_xem ?? 0)) ?></span>
                            <?php if (!empty($featured->thoi_gian_doc)): ?>
                            <span><i class="fas fa-book-reader me-1"></i><?= (int)$featured->thoi_gian_doc ?> phút đọc</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- GRID BÀI VIẾT -->
            <?php
            $grid = $posts;
            // Bỏ bài hero nếu đang ở trang 1
            if ($featured && $pageNum === 1 && empty($filters['keyword']) && empty($filters['category_id'])) {
                $grid = array_filter($grid, fn($p) => $p->id !== $featured->id);
            }
            $grid = array_values($grid);
            ?>

            <?php if (empty($grid)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-newspaper fa-3x mb-3 opacity-25"></i>
                <h5>Chưa có bài viết nào</h5>
                <p>Thử tìm kiếm với từ khóa khác.</p>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($grid as $p): ?>
                <div class="col-sm-6">
                    <div class="news-card h-100">
                        <div class="news-card-img">
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>">
                                <img src="<?= news_img($p->anh_thu_nho ?? '') ?>"
                                     alt="<?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>"
                                     loading="lazy">
                            </a>
                            <?php if (!empty($p->noi_bat)): ?>
                            <span class="position-absolute top-0 start-0 m-2 badge"
                                  style="background:var(--news-accent);font-size:.65rem;">
                                <i class="fas fa-star me-1"></i>Nổi bật
                            </span>
                            <?php endif; ?>
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
                            <?php if (!empty($p->tom_tat)): ?>
                            <p class="news-card-excerpt"><?= htmlspecialchars($p->tom_tat, ENT_QUOTES) ?></p>
                            <?php endif; ?>
                            <div class="news-card-meta">
                                <span><i class="fas fa-user-pen"></i> <?= htmlspecialchars($p->ten_tac_gia ?? 'Admin', ENT_QUOTES) ?></span>
                                <span><i class="fas fa-calendar-alt"></i> <?= news_date($p->ngay_tao ?? 'now') ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format((int)($p->luot_xem ?? 0)) ?></span>
                                <?php if (!empty($p->luot_binh_luan)): ?>
                                <span><i class="fas fa-comment"></i> <?= (int)$p->luot_binh_luan ?></span>
                                <?php endif; ?>
                                <?php if (!empty($p->thoi_gian_doc)): ?>
                                <span><i class="fas fa-book-reader"></i> <?= (int)$p->thoi_gian_doc ?>p</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="Phân trang tin tức">
                <ul class="pagination justify-content-center news-pagination">
                    <?php if ($pageNum > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pageNum - 1])) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $pageNum - 2); $i <= min($totalPages, $pageNum + 2); $i++): ?>
                    <li class="page-item <?= $i === $pageNum ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($pageNum < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $pageNum + 1])) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <p class="text-center text-muted" style="font-size:.8rem;">
                    Trang <?= $pageNum ?>/<?= $totalPages ?> · <?= number_format($total) ?> bài viết
                </p>
            </nav>
            <?php endif; ?>
            <?php endif; ?>

        </div><!-- /col-lg-8 -->

        <!-- RIGHT: Sidebar -->
        <div class="col-lg-4">
            <?php require_once APP_ROOT . '/app/views/tin-tuc/components/sidebar.php'; ?>
        </div>

    </div>
</div>

<!-- JS autocomplete -->
<script>
window.NEWS_CONFIG = {
    siteRoot:   '<?= URL_ROOT ?>',
    commentUrl: '<?= URL_ROOT ?>/tin-tuc/comment',
    likeUrl:    '<?= URL_ROOT ?>/tin-tuc/like',
    shareUrl:   '<?= URL_ROOT ?>/tin-tuc/share',
    csrfToken:  '<?= Csrf::token() ?>',
    userId:     <?= (int)(Session::get('user_id') ?? 0) ?>,
    newsId:     0,
};
</script>
<script src="<?= URL_ROOT ?>/public/js/news-detail.js?v=<?= filemtime(APP_ROOT.'/public/js/news-detail.js') ?>"></script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
