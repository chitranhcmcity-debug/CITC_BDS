<?php
/**
 * View: Tìm kiếm Tin tức
 * Route: GET /tin-tuc/search?q={keyword}
 * Biến: $keyword, $posts, $total, $pagination, $sidebar, $title
 */
$pageNum    = $pagination['currentPage'] ?? 1;
$totalPages = $pagination['totalPages']  ?? 1;
$kw         = $keyword ?? '';
$baseUrl    = URL_ROOT . '/tin-tuc/search?q=' . urlencode($kw);
?>
@include('layouts.header')
<meta name="robots" content="noindex">
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/news.css?v=<?= filemtime(APP_ROOT.'/public/css/news.css') ?>">

<div class="container-xl pb-5">

    <!-- Search Hero Form -->
    <div class="search-hero-form mb-5">
        <h1 class="text-white fw-800 mb-2" style="font-size:clamp(1.2rem,3vw,1.8rem);">
            <i class="fas fa-search me-2"></i>Tìm kiếm Tin tức
        </h1>
        <p class="text-white opacity-75 mb-4" style="font-size:.9rem;">
            Tìm theo tiêu đề, nội dung, danh mục hoặc tag
        </p>
        <div class="search-input-wrap mx-auto">
            <form action="<?= URL_ROOT ?>/tin-tuc/search" method="GET" style="position:relative;">
                <input type="text" name="q" id="searchInput" class="form-control"
                       placeholder="Nhập từ khóa tìm kiếm..."
                       value="<?= htmlspecialchars($kw, ENT_QUOTES) ?>"
                       autocomplete="off">
                <button type="submit">
                    <i class="fas fa-search me-1"></i>Tìm
                </button>
                <div id="searchAutocomplete" class="search-autocomplete"></div>
            </form>
        </div>
    </div>

    <?php if ($kw): ?>

    <div class="row g-4">
        <div class="col-lg-8">

            <!-- Result header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 style="font-size:1rem;font-weight:700;color:#111827;">
                        <?php if ($total > 0): ?>
                        Tìm thấy <strong class="text-primary"><?= number_format($total) ?></strong>
                        kết quả cho "<em><?= htmlspecialchars($kw, ENT_QUOTES) ?></em>"
                        <?php else: ?>
                        Không tìm thấy kết quả cho "<em><?= htmlspecialchars($kw, ENT_QUOTES) ?></em>"
                        <?php endif; ?>
                    </h2>
                </div>
            </div>

            <?php if (empty($posts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 opacity-25"></i>
                <h4>Không có kết quả</h4>
                <p>Thử tìm kiếm với từ khóa khác hoặc kiểm tra chính tả.</p>
                <a href="<?= URL_ROOT ?>/tin-tuc" class="btn btn-outline-primary rounded-pill">
                    <i class="fas fa-arrow-left me-2"></i>Xem tất cả tin tức
                </a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($posts as $p): ?>
                <?php
                $img = !empty($p->anh_thu_nho)
                    ? (str_starts_with($p->anh_thu_nho, 'http') ? $p->anh_thu_nho : URL_ROOT . '/public/' . ltrim($p->anh_thu_nho, '/'))
                    : 'https://placehold.co/400x220/e2e8f0/94a3b8?text=BDS';
                ?>
                <div class="col-12">
                    <div class="d-flex gap-3 p-3 rounded-3 border bg-white" style="transition:box-shadow .2s;"
                         onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.08)'"
                         onmouseout="this.style.boxShadow=''">
                        <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
                           style="flex-shrink:0;">
                            <img src="<?= $img ?>" style="width:120px;height:80px;object-fit:cover;border-radius:8px;"
                                 alt="<?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>" loading="lazy">
                        </a>
                        <div class="flex-grow-1">
                            <?php if (!empty($p->slug_danh_muc)): ?>
                            <a href="<?= URL_ROOT ?>/tin-tuc/category/<?= htmlspecialchars($p->slug_danh_muc, ENT_QUOTES) ?>"
                               class="news-card-cat mb-1"><?= htmlspecialchars($p->ten_danh_muc, ENT_QUOTES) ?></a>
                            <?php endif; ?>
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
                               class="d-block fw-700 mb-1 text-decoration-none text-dark"
                               style="font-size:.95rem;line-height:1.4;"
                               onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color=''">
                                <?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>
                            </a>
                            <?php if (!empty($p->tom_tat)): ?>
                            <p class="text-muted mb-1" style="font-size:.82rem;-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                                <?= htmlspecialchars($p->tom_tat, ENT_QUOTES) ?>
                            </p>
                            <?php endif; ?>
                            <div class="news-card-meta mt-0">
                                <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($p->ngay_tao ?? 'now')) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format((int)($p->luot_xem ?? 0)) ?></span>
                                <?php if (!empty($p->thoi_gian_doc)): ?>
                                <span><i class="fas fa-book-reader"></i> <?= (int)$p->thoi_gian_doc ?> phút</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4"><ul class="pagination justify-content-center news-pagination">
                <?php if ($pageNum > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>&page=<?= $pageNum - 1 ?>"><i class="fas fa-chevron-left"></i></a></li>
                <?php endif; ?>
                <?php for ($i = max(1, $pageNum - 2); $i <= min($totalPages, $pageNum + 2); $i++): ?>
                <li class="page-item <?= $i === $pageNum ? 'active' : '' ?>"><a class="page-link" href="<?= $baseUrl ?>&page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <?php if ($pageNum < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="<?= $baseUrl ?>&page=<?= $pageNum + 1 ?>"><i class="fas fa-chevron-right"></i></a></li>
                <?php endif; ?>
            </ul></nav>
            <?php endif; ?>
            <?php endif; ?>

        </div>
        <div class="col-lg-4">
            @include('tin-tuc.components.sidebar')
        </div>
    </div>

    <?php else: ?>
    <!-- No keyword yet - show popular categories and tags -->
    <div class="row g-4">
        <div class="col-lg-8">
            <h5 class="fw-700 mb-3">Danh mục phổ biến</h5>
            <div class="tag-cloud mb-4">
                <?php foreach ($sidebar['categories'] ?? [] as $c): ?>
                <a href="<?= URL_ROOT ?>/tin-tuc/category/<?= htmlspecialchars($c->duong_dan, ENT_QUOTES) ?>"
                   class="tag-badge" style="padding:8px 16px;font-size:.88rem;">
                    <i class="fas fa-folder-open me-1 text-primary"></i>
                    <?= htmlspecialchars($c->ten, ENT_QUOTES) ?>
                    <span class="tag-count ms-1">(<?= (int)($c->so_bai ?? 0) ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>
            <h5 class="fw-700 mb-3">Tags phổ biến</h5>
            <div class="tag-cloud">
                <?php foreach ($sidebar['tags'] ?? [] as $t): ?>
                <a href="<?= URL_ROOT ?>/tin-tuc/tag/<?= htmlspecialchars($t->duong_dan, ENT_QUOTES) ?>"
                   class="tag-badge">#<?= htmlspecialchars($t->ten, ENT_QUOTES) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-lg-4">
            @include('tin-tuc.components.sidebar')
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
window.NEWS_CONFIG = {
    siteRoot:   '<?= URL_ROOT ?>',
    csrfToken:  '<?= Csrf::token() ?>',
    commentUrl: '', likeUrl: '', shareUrl: '',
    userId:     0, newsId: 0,
};
</script>
<script src="<?= URL_ROOT ?>/public/js/news-detail.js?v=<?= filemtime(APP_ROOT.'/public/js/news-detail.js') ?>"></script>
@include('layouts.footer')
