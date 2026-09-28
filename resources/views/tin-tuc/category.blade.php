<?php
/**
 * View: Danh mục bài viết
 * Route: GET /tin-tuc/category/{slug}
 * Biến: $category, $posts, $pagination, $sidebar, $title, $meta_desc, $canonical
 */
$pageNum    = $pagination['currentPage'] ?? 1;
$totalPages = $pagination['totalPages']  ?? 1;
$total      = $pagination['total']       ?? 0;
$slug       = $category->duong_dan ?? '';
$baseUrl    = URL_ROOT . '/tin-tuc/category/' . $slug;

function cat_img(string $path): string {
    if (!$path) return 'https://placehold.co/400x220/e2e8f0/94a3b8?text=BDS';
    if (str_starts_with($path, 'http')) return $path;
    return URL_ROOT . '/public/' . ltrim($path, '/');
}
?>
@include('layouts.header')
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/news.css?v=<?= filemtime(APP_ROOT.'/public/css/news.css') ?>">

<!-- Breadcrumb -->
<div class="container-xl" style="padding-top:12px;">
    <nav aria-label="breadcrumb" style="font-size:.82rem;">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/tin-tuc">Tin tức</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($category->ten ?? '', ENT_QUOTES) ?></li>
        </ol>
    </nav>
</div>

<!-- Section Hero -->
<div class="section-hero mb-4">
    <div class="container-xl">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center"
                 style="width:56px;height:56px;background:rgba(255,255,255,.15);font-size:1.4rem;">
                <i class="fas fa-folder-open"></i>
            </div>
            <div>
                <h1 class="mb-1"><?= htmlspecialchars($category->ten ?? '', ENT_QUOTES) ?></h1>
                <p class="mb-0 opacity-75" style="font-size:.88rem;">
                    <?= number_format($total) ?> bài viết trong chuyên mục này
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
                <i class="fas fa-newspaper fa-3x mb-3 opacity-25"></i>
                <h5>Chưa có bài viết nào trong danh mục này</h5>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($posts as $p): ?>
                <div class="col-sm-6">
                    <div class="news-card h-100">
                        <div class="news-card-img">
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>">
                                <img src="<?= cat_img($p->anh_thu_nho ?? '') ?>"
                                     alt="<?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="news-card-body">
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($p->duong_dan, ENT_QUOTES) ?>"
                               class="d-block text-decoration-none news-card-title">
                                <?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>
                            </a>
                            <?php if (!empty($p->tom_tat)): ?>
                            <p class="news-card-excerpt"><?= htmlspecialchars($p->tom_tat, ENT_QUOTES) ?></p>
                            <?php endif; ?>
                            <div class="news-card-meta">
                                <span><i class="fas fa-user-pen"></i> <?= htmlspecialchars($p->ten_tac_gia ?? 'Admin', ENT_QUOTES) ?></span>
                                <span><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($p->ngay_tao ?? 'now')) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format((int)($p->luot_xem ?? 0)) ?></span>
                                <?php if (!empty($p->thoi_gian_doc)): ?>
                                <span><i class="fas fa-book-reader"></i> <?= (int)$p->thoi_gian_doc ?>p</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="Phân trang">
                <ul class="pagination justify-content-center news-pagination">
                    <?php if ($pageNum > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $pageNum - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $pageNum - 2); $i <= min($totalPages, $pageNum + 2); $i++): ?>
                    <li class="page-item <?= $i === $pageNum ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($pageNum < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $baseUrl ?>?page=<?= $pageNum + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                    <?php endif; ?>
                </ul>
                <p class="text-center text-muted" style="font-size:.8rem;">Trang <?= $pageNum ?>/<?= $totalPages ?></p>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            @include('tin-tuc.components.sidebar')
        </div>
    </div>
</div>

@include('layouts.footer')
