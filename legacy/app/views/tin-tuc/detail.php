<?php
/**
 * View: Chi tiết bài viết
 * Route: GET /tin-tuc/detail/{slug}
 * Biến: $post, $content, $toc, $tags, $related, $comments, $commentCount,
 *        $sidebar, $seo, $title, $meta_desc, $canonical, $ogImage, $schemaJson,
 *        $hasLiked, $userId, $csrfToken
 */

$p = $post ?? new stdClass();
$userLoggedIn = !empty($userId);

// Avatar tác giả
$authorAvatar = !empty($p->avatar_tac_gia)
    ? URL_ROOT . '/uploads/avatars/' . htmlspecialchars($p->avatar_tac_gia, ENT_QUOTES)
    : 'https://ui-avatars.com/api/?name=' . urlencode($p->ten_tac_gia ?? 'Admin') . '&background=2563eb&color=fff&size=80';

// Ảnh bài viết
$heroImg = !empty($p->anh_thu_nho)
    ? (str_starts_with($p->anh_thu_nho, 'http') ? $p->anh_thu_nho : URL_ROOT . '/public/' . ltrim($p->anh_thu_nho, '/'))
    : 'https://placehold.co/1200x480/1e3a8a/fff?text=Tin+tuc';
?>
<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<!-- SEO -->
<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">
<meta name="description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES) ?>">
<meta property="og:title"       content="<?= htmlspecialchars($title, ENT_QUOTES) ?>">
<meta property="og:description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES) ?>">
<meta property="og:image"       content="<?= htmlspecialchars($ogImage ?? $heroImg, ENT_QUOTES) ?>">
<meta property="og:url"         content="<?= htmlspecialchars($canonical, ENT_QUOTES) ?>">
<meta property="og:type"        content="article">
<meta name="twitter:card"       content="summary_large_image">
<meta name="twitter:title"      content="<?= htmlspecialchars($title, ENT_QUOTES) ?>">
<meta name="twitter:image"      content="<?= htmlspecialchars($ogImage ?? $heroImg, ENT_QUOTES) ?>">
<link rel="alternate" type="application/rss+xml" title="RSS" href="<?= URL_ROOT ?>/tin-tuc/rss">

<!-- Schema.org -->
<script type="application/ld+json"><?= $schemaJson ?? '{}' ?></script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
    {"@type":"ListItem","position":1,"name":"Trang chủ","item":"<?= URL_ROOT ?>"},
    {"@type":"ListItem","position":2,"name":"Tin tức","item":"<?= URL_ROOT ?>/tin-tuc"},
    {"@type":"ListItem","position":3,"name":"<?= htmlspecialchars($p->tieu_de ?? '', ENT_QUOTES) ?>","item":"<?= htmlspecialchars($canonical, ENT_QUOTES) ?>"}
]}</script>

<!-- CSS -->
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/news.css?v=<?= filemtime(APP_ROOT.'/public/css/news.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css">

<!-- Reading Progress Bar -->
<div id="readingProgress"></div>

<!-- Breadcrumb -->
<div class="container-xl" style="padding-top:12px;">
    <nav aria-label="breadcrumb" style="font-size:.82rem;">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/tin-tuc">Tin tức</a></li>
            <?php if (!empty($p->slug_danh_muc)): ?>
            <li class="breadcrumb-item">
                <a href="<?= URL_ROOT ?>/tin-tuc/category/<?= htmlspecialchars($p->slug_danh_muc, ENT_QUOTES) ?>">
                    <?= htmlspecialchars($p->ten_danh_muc ?? '', ENT_QUOTES) ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-truncate" style="max-width:200px;">
                <?= htmlspecialchars($p->tieu_de ?? '', ENT_QUOTES) ?>
            </li>
        </ol>
    </nav>
</div>

<!-- ── MAIN ── -->
<div class="container-xl py-4">
    <div class="row g-4">

        <!-- TOC (Desktop – left sticky) -->
        <?php if (!empty($toc) && count($toc) >= 2): ?>
        <div class="col-lg-2 d-none d-lg-block">
            <div class="toc-card">
                <h6><i class="fas fa-list-ul me-1"></i>Mục lục</h6>
                <ul class="toc-list">
                    <?php foreach ($toc as $item): ?>
                    <li class="<?= $item['level'] === 3 ? 'toc-h3' : '' ?>">
                        <a href="#<?= htmlspecialchars($item['id'], ENT_QUOTES) ?>">
                            <?= htmlspecialchars($item['text'], ENT_QUOTES) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
        <?php else: ?>
        <div class="col-lg-8">
        <?php endif; ?>

            <!-- ARTICLE -->
            <article itemscope itemtype="https://schema.org/NewsArticle">

                <!-- Hero Image -->
                <img src="<?= $heroImg ?>"
                     alt="<?= htmlspecialchars($p->tieu_de ?? '', ENT_QUOTES) ?>"
                     class="article-hero w-100"
                     itemprop="image" loading="eager">

                <!-- Category Badge -->
                <?php if (!empty($p->slug_danh_muc)): ?>
                <a href="<?= URL_ROOT ?>/tin-tuc/category/<?= htmlspecialchars($p->slug_danh_muc, ENT_QUOTES) ?>"
                   class="article-cat-badge mb-3 d-inline-block">
                    <?= htmlspecialchars($p->ten_danh_muc ?? '', ENT_QUOTES) ?>
                </a>
                <?php endif; ?>

                <!-- Title -->
                <h1 itemprop="headline" style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;line-height:1.3;color:#111827;margin-bottom:16px;">
                    <?= htmlspecialchars($p->tieu_de ?? '', ENT_QUOTES) ?>
                </h1>

                <!-- Meta bar -->
                <div class="article-meta-bar">
                    <img src="<?= $authorAvatar ?>" class="article-author-avatar" alt="<?= htmlspecialchars($p->ten_tac_gia ?? '', ENT_QUOTES) ?>">
                    <div>
                        <a href="<?= URL_ROOT ?>/author/<?= (int)($p->tac_gia_id ?? 0) ?>"
                           class="fw-700 text-dark text-decoration-none d-block" style="font-size:.875rem;">
                            <?= htmlspecialchars($p->ten_tac_gia ?? 'Admin', ENT_QUOTES) ?>
                        </a>
                        <span itemprop="datePublished" content="<?= $p->ngay_tao ?? '' ?>" style="font-size:.75rem;color:#9ca3af;">
                            <?= date('d/m/Y H:i', strtotime($p->ngay_tao ?? 'now')) ?>
                        </span>
                    </div>
                    <div class="ms-auto d-flex gap-3 flex-wrap">
                        <?php if (!empty($p->thoi_gian_doc)): ?>
                        <span class="read-time-badge">
                            <i class="fas fa-book-reader me-1"></i><?= (int)$p->thoi_gian_doc ?> phút đọc
                        </span>
                        <?php endif; ?>
                        <span style="font-size:.8rem;"><i class="fas fa-eye me-1 text-primary"></i><?= number_format((int)($p->luot_xem ?? 0)) ?></span>
                        <span style="font-size:.8rem;"><i class="fas fa-comment me-1 text-primary"></i><span id="commentCount"><?= (int)($commentCount ?? 0) ?></span></span>
                        <span style="font-size:.8rem;"><i class="fas fa-share-alt me-1 text-primary"></i><?= number_format((int)($p->luot_chia_se ?? 0)) ?></span>
                        <?php if (!empty($p->ngay_cap_nhat) && $p->ngay_cap_nhat !== $p->ngay_tao): ?>
                        <span style="font-size:.75rem;color:#9ca3af;">
                            <i class="fas fa-sync-alt me-1"></i>Cập nhật: <?= date('d/m/Y', strtotime($p->ngay_cap_nhat)) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TOC Mobile Dropdown -->
                <?php if (!empty($toc) && count($toc) >= 2): ?>
                <div class="d-lg-none mb-4">
                    <div class="accordion" id="tocAccordion">
                        <div class="accordion-item border rounded-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed rounded-3" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#tocBody">
                                    <i class="fas fa-list-ul me-2 text-primary"></i>Mục lục bài viết
                                </button>
                            </h2>
                            <div id="tocBody" class="accordion-collapse collapse">
                                <div class="accordion-body p-2">
                                    <ul class="toc-list">
                                        <?php foreach ($toc as $item): ?>
                                        <li class="<?= $item['level'] === 3 ? 'toc-h3' : '' ?>">
                                            <a href="#<?= htmlspecialchars($item['id'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($item['text'], ENT_QUOTES) ?>
                                            </a>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ARTICLE CONTENT -->
                <div class="article-content" itemprop="articleBody">
                    <?= $content ?? '' ?>
                </div>

                <!-- TAGS -->
                <?php if (!empty($tags)): ?>
                <div class="mt-4 pt-3 border-top">
                    <span class="text-muted me-2" style="font-size:.82rem;font-weight:600;">
                        <i class="fas fa-tags me-1 text-primary"></i>Tags:
                    </span>
                    <div class="tag-cloud d-inline-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                        <a href="<?= URL_ROOT ?>/tin-tuc/tag/<?= htmlspecialchars($tag->duong_dan, ENT_QUOTES) ?>"
                           class="tag-badge">#<?= htmlspecialchars($tag->ten, ENT_QUOTES) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- LIKE / SHARE BAR -->
                <div class="action-bar">
                    <!-- Like -->
                    <button id="likeBtn" class="btn-like <?= $hasLiked ? 'liked' : '' ?>">
                        <i class="like-icon <?= $hasLiked ? 'fas' : 'far' ?> fa-heart"></i>
                        <span class="like-count"><?= number_format((int)($p->luot_thich ?? 0)) ?></span>
                        <span class="ms-1"><?= $hasLiked ? 'Đã thích' : 'Thích' ?></span>
                    </button>

                    <!-- Share -->
                    <button id="share-facebook" class="btn-share-sm fb">
                        <i class="fab fa-facebook-f"></i><span>Facebook</span>
                    </button>
                    <button id="share-twitter" class="btn-share-sm tw">
                        <i class="fab fa-twitter"></i><span>Twitter</span>
                    </button>
                    <button id="share-telegram" class="btn-share-sm tg">
                        <i class="fab fa-telegram-plane"></i><span>Telegram</span>
                    </button>
                    <button id="share-zalo" class="btn-share-sm zl">
                        <i class="fas fa-comment-dots"></i><span>Zalo</span>
                    </button>
                    <button id="share-linkedin" class="btn-share-sm li">
                        <i class="fab fa-linkedin-in"></i><span>LinkedIn</span>
                    </button>
                    <button id="share-copy" class="btn-share-sm btn-copy">
                        <i class="fas fa-link"></i><span>Sao chép</span>
                    </button>
                </div>

                <!-- AUTHOR BIO -->
                <div class="p-3 rounded-3 mb-4 d-flex gap-3 align-items-start"
                     style="background:#f8fafc;border:1px solid #e5e7eb;">
                    <img src="<?= $authorAvatar ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;" alt="">
                    <div>
                        <div class="fw-700" style="font-size:.9rem;"><?= htmlspecialchars($p->ten_tac_gia ?? 'Admin', ENT_QUOTES) ?></div>
                        <?php if (!empty($p->bio_tac_gia)): ?>
                        <p class="mb-1 text-muted" style="font-size:.82rem;"><?= htmlspecialchars($p->bio_tac_gia, ENT_QUOTES) ?></p>
                        <?php endif; ?>
                        <a href="<?= URL_ROOT ?>/author/<?= (int)($p->tac_gia_id ?? 0) ?>"
                           class="btn btn-sm btn-outline-primary rounded-pill mt-1" style="font-size:.75rem;">
                            Xem tất cả bài viết
                        </a>
                    </div>
                </div>

                <!-- RELATED POSTS -->
                <?php if (!empty($related)): ?>
                <div class="mb-5">
                    <h4 class="fw-800 mb-3" style="font-size:1.05rem;">
                        <i class="fas fa-layer-group text-primary me-2"></i>Bài viết liên quan
                    </h4>
                    <div class="row g-3">
                        <?php foreach ($related as $r): ?>
                        <?php
                        $rImg = !empty($r->anh_thu_nho)
                            ? (str_starts_with($r->anh_thu_nho, 'http') ? $r->anh_thu_nho : URL_ROOT . '/public/' . ltrim($r->anh_thu_nho, '/'))
                            : 'https://placehold.co/280x140/e2e8f0/94a3b8?text=BDS';
                        ?>
                        <div class="col-sm-6 col-md-4">
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($r->duong_dan, ENT_QUOTES) ?>"
                               class="related-card">
                                <img src="<?= $rImg ?>" alt="<?= htmlspecialchars($r->tieu_de, ENT_QUOTES) ?>" loading="lazy">
                                <div class="related-card-body">
                                    <div class="related-card-title"><?= htmlspecialchars($r->tieu_de, ENT_QUOTES) ?></div>
                                    <div style="font-size:.72rem;color:#9ca3af;">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('d/m/Y', strtotime($r->ngay_tao ?? 'now')) ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- COMMENTS -->
                <div class="comment-section">
                    <h4>
                        <i class="fas fa-comments text-primary"></i>
                        Bình luận
                        <span class="badge bg-primary ms-1" style="font-size:.75rem;" id="commentCount2"><?= (int)($commentCount ?? 0) ?></span>
                    </h4>

                    <!-- Comment list -->
                    <div id="commentList">
                        <?php if (empty($comments)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-comment-slash fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Chưa có bình luận. Hãy là người đầu tiên!</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($comments as $c): ?>
                        <?php require APP_ROOT . '/app/views/tin-tuc/components/comment.php'; ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Comment form -->
                    <?php if ($userLoggedIn): ?>
                    <div class="comment-form-wrap mt-3">
                        <h6 class="fw-700 mb-3" style="font-size:.875rem;">
                            <i class="fas fa-pen me-2 text-primary"></i>Viết bình luận
                        </h6>
                        <form id="commentForm">
                            <textarea name="noi_dung" class="form-control mb-2" rows="3"
                                      placeholder="Nhập nội dung bình luận..." maxlength="2000" required></textarea>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted" style="font-size:.75rem;">Tối đa 2000 ký tự</span>
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                                    <i class="fas fa-paper-plane me-1"></i>Đăng bình luận
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-light border text-center mt-3 rounded-3">
                        <i class="fas fa-lock text-primary me-2"></i>
                        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap" class="fw-600">Đăng nhập</a>
                        để viết bình luận.
                    </div>
                    <?php endif; ?>
                </div>

            </article>
        </div><!-- /col article -->

        <!-- RIGHT Sidebar -->
        <div class="col-lg-4">
            <?php require_once APP_ROOT . '/app/views/tin-tuc/components/sidebar.php'; ?>
        </div>

    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
// Init highlight.js
document.querySelectorAll('pre code').forEach(el => hljs.highlightElement(el));

// Config
window.NEWS_CONFIG = {
    newsId:           <?= (int)($p->id ?? 0) ?>,
    userId:           <?= (int)($userId ?? 0) ?>,
    csrfToken:        '<?= htmlspecialchars($csrfToken ?? Csrf::token(), ENT_QUOTES) ?>',
    likeUrl:          '<?= URL_ROOT ?>/tin-tuc/like',
    shareUrl:         '<?= URL_ROOT ?>/tin-tuc/share',
    commentUrl:       '<?= URL_ROOT ?>/tin-tuc/comment',
    deleteCommentUrl: '<?= URL_ROOT ?>/tin-tuc/delete-comment',
    siteRoot:         '<?= URL_ROOT ?>',
};
</script>
<script src="<?= URL_ROOT ?>/public/js/news-detail.js?v=<?= filemtime(APP_ROOT.'/public/js/news-detail.js') ?>"></script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
