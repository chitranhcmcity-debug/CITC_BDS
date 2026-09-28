<?php
/**
 * View: Chi tiết Bất động sản
 * URL: /du-an/detail/{slug}
 *
 * Biến nhận từ Controller:
 *   $project         – object tin đăng (đã JOIN user, category)
 *   $images          – array ảnh gallery
 *   $amenities       – array tiện ích lân cận
 *   $videoEmbed      – ?array ['type', 'src']
 *   $mapData         – array ['lat', 'lng', 'title', 'address', 'hasMap']
 *   $breadcrumb      – array [['label', 'url'], ...]
 *   $isSaved         – bool
 *   $related         – array tin liên quan (tối đa 12)
 *   $sameAuthorPosts – array tin cùng người đăng
 *   $shareUrl        – string URL đầy đủ
 *   $reportReasons   – array ['key' => 'label']
 *   $metaDescription – string SEO description
 *   $canonicalUrl    – string canonical URL
 */

// SEO & Meta
$seoTitle       = $title       ?? (SITE_NAME . ' - ' . ($project->tieu_de ?? ''));
$seoDesc        = $metaDescription ?? mb_substr(strip_tags($project->mo_ta ?? $project->noi_dung ?? ''), 0, 160);
$seoImage       = img_url($project->anh_thu_nho ?? '');
$canonical      = $canonicalUrl ?? (URL_ROOT . '/du-an/detail/' . ($project->duong_dan ?? ''));
$postId         = (int)$project->id;
$phone          = preg_replace('/[^0-9+]/', '', (string)($project->dien_thoai ?? ''));
$zaloUrl        = 'https://zalo.me/' . $phone;
$hasVideo       = !empty($videoEmbed);
$has360         = !empty($project->link_tour_360);
$hasGallery     = !empty($images);
$allImages      = array_merge(
    $project->anh_thu_nho ? [['duong_dan_anh' => $project->anh_thu_nho]] : [],
    $images
);

// Format giá
function formatGia(string $raw): string {
    if (!$raw) return 'Thỏa thuận';
    // Nếu là số nguyên
    $num = filter_var($raw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if (is_numeric($num)) {
        $n = (float)$num;
        if ($n >= 1e9)  return number_format($n / 1e9, 2, '.', ',') . ' tỷ';
        if ($n >= 1e6)  return number_format($n / 1e6, 0, '.', ',') . ' triệu';
    }
    return htmlspecialchars($raw, ENT_QUOTES);
}

// JSON-LD Schema.org RealEstateListing
$schemaLD = json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'RealEstateListing',
    'name'        => $project->tieu_de ?? '',
    'description' => $seoDesc,
    'url'         => $canonical,
    'image'       => $seoImage,
    'datePosted'  => $project->ngay_tao ?? '',
    'price'       => $project->gia ?? 'Thỏa thuận',
    'priceCurrency' => 'VND',
    'floorSize'   => ['@type' => 'QuantitativeValue', 'value' => $project->dien_tich ?? '', 'unitCode' => 'MTK'],
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $project->vi_tri ?? '',
        'addressLocality' => $project->tinh_thanh ?? '',
        'addressCountry'  => 'VN',
    ],
    'author' => [
        '@type' => 'Person',
        'name'  => $project->ten_nguoi_dung ?? 'Người đăng tin',
    ],
    'numberOfRooms'    => (string)($project->so_phong_ngu ?? ''),
    'numberOfBathroomsTotal' => (string)($project->so_phong_wc ?? ''),
    'breadcrumb' => [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array_values(array_map(
            fn($i, $c) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['label'], 'item' => $c['url'] ?? $canonical],
            array_keys($breadcrumb),
            $breadcrumb
        )),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<?php require_once '../app/views/layouts/header.php'; ?>

<!-- ============================================================
     SEO: Extra Meta (Open Graph, Twitter Card, Canonical)
     ============================================================ -->
<?php /* Inject vào <head> thông qua ob_start nếu header hỗ trợ,
         hoặc đặt thẳng đây vì header.php đã in <head> rồi –
         các tag dưới đây sẽ hoạt động nếu header chưa đóng </head>. */ ?>
<link rel="canonical" href="<?= $canonical ?>">
<meta name="description"       content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
<!-- Open Graph -->
<meta property="og:type"       content="website">
<meta property="og:url"        content="<?= $canonical ?>">
<meta property="og:title"      content="<?= htmlspecialchars($seoTitle, ENT_QUOTES) ?>">
<meta property="og:description"content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
<meta property="og:image"      content="<?= htmlspecialchars($seoImage, ENT_QUOTES) ?>">
<!-- Twitter Card -->
<meta name="twitter:card"      content="summary_large_image">
<meta name="twitter:title"     content="<?= htmlspecialchars($seoTitle, ENT_QUOTES) ?>">
<meta name="twitter:description"content="<?= htmlspecialchars($seoDesc, ENT_QUOTES) ?>">
<meta name="twitter:image"     content="<?= htmlspecialchars($seoImage, ENT_QUOTES) ?>">
<!-- JSON-LD Schema.org -->
<script type="application/ld+json"><?= $schemaLD ?></script>

<!-- ============================================================
     LIBRARIES: SwiperJS, LightGallery, AOS
     ============================================================ -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lightgallery-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

<!-- ============================================================
     PAGE STYLES
     ============================================================ -->
<style>
/* ---- Root Variables ---- */
:root {
    --primary:    #E74C3C;
    --primary-dk: #C0392B;
    --accent:     #00a8a8;
    --accent-dk:  #007a7a;
    --text-main:  #1a1a2e;
    --text-muted: #6c757d;
    --bg-light:   #f8f9fc;
    --card-radius: 14px;
    --shadow-sm:  0 2px 12px rgba(0,0,0,.07);
    --shadow-md:  0 6px 24px rgba(0,0,0,.10);
}

/* ---- Layout ---- */
.detail-wrap { background:#fff; min-height:100vh; }
.detail-main  { padding: 0 0 60px; }

/* ---- Breadcrumb ---- */
.breadcrumb-bar { background: var(--bg-light); border-bottom: 1px solid #e9ecef; padding: 10px 0; font-size:.875rem; }
.breadcrumb-bar a { color: var(--text-muted); text-decoration:none; }
.breadcrumb-bar a:hover { color: var(--primary); }
.breadcrumb-bar .separator { margin: 0 6px; color: #ccc; }

/* ---- Gallery ---- */
.gallery-wrap { position:relative; border-radius: var(--card-radius); overflow:hidden; box-shadow: var(--shadow-md); background:#000; }
.swiper-main-img { width:100%; height:460px; object-fit:cover; display:block; }
.gallery-count-badge {
    position:absolute; bottom:12px; right:12px; z-index:10;
    background:rgba(0,0,0,.55); color:#fff; font-size:.8rem;
    padding:4px 10px; border-radius:20px; backdrop-filter:blur(4px);
    pointer-events:none;
}
.swiper-thumbs-wrap { margin-top:8px; }
.swiper-thumbs-wrap .swiper-slide { width:80px !important; height:58px; cursor:pointer; border-radius:6px; overflow:hidden; opacity:.6; transition:opacity .2s, border .2s; border:2px solid transparent; }
.swiper-thumbs-wrap .swiper-slide-thumb-active { opacity:1; border-color: var(--accent); }
.swiper-thumbs-wrap img { width:100%; height:100%; object-fit:cover; }

/* Fullscreen lightbox trigger */
.btn-gallery-full {
    position:absolute; top:12px; right:12px; z-index:10;
    background:rgba(0,0,0,.5); color:#fff; border:none; border-radius:8px;
    padding:6px 12px; font-size:.8rem; cursor:pointer; backdrop-filter:blur(4px);
    transition:.2s;
}
.btn-gallery-full:hover { background:rgba(0,0,0,.75); }

/* ---- Video / 360 tabs ---- */
.media-tabs .nav-link { color: var(--text-muted); font-size:.875rem; padding:6px 14px; border-radius:20px; }
.media-tabs .nav-link.active { background: var(--accent); color:#fff; }
.media-embed { position:relative; padding-top:56.25%; background:#000; border-radius:10px; overflow:hidden; }
.media-embed iframe, .media-embed video { position:absolute; top:0; left:0; width:100%; height:100%; border:none; }

/* ---- Property title ---- */
.prop-title { font-size:1.55rem; font-weight:800; color: var(--text-main); line-height:1.35; }
.prop-badge { display:inline-flex; align-items:center; gap:5px; font-size:.78rem; font-weight:600; padding:3px 10px; border-radius:20px; }
.badge-vip    { background:linear-gradient(135deg,#f7b733,#fc4a1a); color:#fff; }
.badge-type   { background:#e3f2fd; color:#1565c0; }
.badge-status { background:#e8f5e9; color:#2e7d32; }

/* ---- Stats bar ---- */
.stats-bar { display:flex; flex-wrap:wrap; gap:16px; align-items:center; background:var(--bg-light); border-radius:10px; padding:12px 18px; }
.stat-item  { display:flex; align-items:center; gap:6px; font-size:.875rem; color: var(--text-muted); }
.stat-item .val { font-weight:700; color: var(--text-main); }
.stat-item.price { font-size:1.4rem; font-weight:800; color: var(--primary); }

/* ---- Section titles ---- */
.section-title {
    font-size:1.1rem; font-weight:700; color: var(--text-main);
    padding-bottom:8px; margin-bottom:18px;
    border-bottom:2px solid var(--accent);
    display:flex; align-items:center; gap:8px;
}
.section-title i { color: var(--accent); }

/* ---- Detail table ---- */
.detail-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.detail-cell { background:var(--bg-light); border-radius:10px; padding:12px 16px; display:flex; flex-direction:column; }
.detail-cell .label { font-size:.78rem; color: var(--text-muted); margin-bottom:3px; text-transform:uppercase; letter-spacing:.5px; }
.detail-cell .value { font-size:.95rem; font-weight:700; color: var(--text-main); display:flex; align-items:center; gap:6px; }
.detail-cell .value i { color: var(--accent); font-size:.85rem; }

/* ---- Description ---- */
.desc-content { font-size:.97rem; color:#444; line-height:1.75; }

/* ---- Amenities ---- */
.amenity-item { display:flex; align-items:center; gap:10px; background:var(--bg-light); border-radius:10px; padding:10px 14px; }
.amenity-icon { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.85rem; color:#fff; }

/* ---- Map ---- */
#propertyMap { height:360px; border-radius: var(--card-radius); overflow:hidden; background:#e9ecef; }

/* ---- Seller card ---- */
.seller-card { background:#fff; border-radius: var(--card-radius); border:1px solid #e9ecef; overflow:hidden; box-shadow: var(--shadow-sm); }
.seller-header { background:linear-gradient(135deg, var(--accent), #0070a8); padding:20px; text-align:center; color:#fff; }
.seller-avatar { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid rgba(255,255,255,.4); margin:0 auto 10px; display:block; }
.seller-name { font-size:1.1rem; font-weight:700; margin:0; }
.seller-meta { font-size:.8rem; opacity:.85; }
.verified-badge { display:inline-flex; align-items:center; gap:4px; background:rgba(255,255,255,.2); padding:2px 8px; border-radius:12px; font-size:.75rem; }
.seller-body { padding:18px; }
.seller-stat { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #f0f0f0; font-size:.875rem; }
.seller-stat:last-child { border:none; }
.seller-stat .label { color: var(--text-muted); }
.seller-stat .val   { font-weight:700; color: var(--text-main); }

/* ---- Action buttons ---- */
.btn-call  { background:var(--accent);     color:#fff; border:none; transition:.2s; }
.btn-call:hover  { background:var(--accent-dk); color:#fff; transform:translateY(-1px); }
.btn-zalo  { background:#0088cc;           color:#fff; border:none; transition:.2s; }
.btn-zalo:hover  { background:#006fa3; color:#fff; transform:translateY(-1px); }
.btn-save  { transition:.2s; }
.btn-save:hover  { transform:translateY(-1px); }
.action-btn { border-radius:10px; padding:11px 14px; font-weight:600; font-size:.9rem; display:flex; align-items:center; justify-content:center; gap:7px; width:100%; }

/* ---- Share row ---- */
.share-row { display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
.share-btn { width:36px; height:36px; border-radius:50%; border:2px solid; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s; background:#fff; font-size:.85rem; }
.share-btn:hover { transform:scale(1.12); }

/* ---- Related grid ---- */
.related-card { border-radius: var(--card-radius); overflow:hidden; background:#fff; border:1px solid #e9ecef; transition:.2s; box-shadow: var(--shadow-sm); height:100%; }
.related-card:hover { transform:translateY(-3px); box-shadow: var(--shadow-md); }
.related-card-img { width:100%; height:160px; object-fit:cover; display:block; }
.related-card-body { padding:12px 14px; }
.related-card-title { font-size:.9rem; font-weight:700; color: var(--text-main); margin-bottom:6px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.related-card-price { font-size:1rem; font-weight:800; color: var(--primary); }
.related-card-meta  { font-size:.78rem; color: var(--text-muted); display:flex; align-items:center; gap:4px; }

/* ---- Sticky sidebar ---- */
.sidebar-sticky { position:sticky; top:80px; }

/* ---- Quick form ---- */
.quick-form .form-control, .quick-form .form-select { border-radius:8px; font-size:.9rem; }
.quick-form .btn-submit { background:linear-gradient(135deg, var(--accent), #0070a8); border:none; color:#fff; border-radius:8px; font-weight:700; height:44px; transition:.2s; }
.quick-form .btn-submit:hover { filter:brightness(1.08); transform:translateY(-1px); }

/* ---- Responsive ---- */
@media (max-width:768px) {
    .swiper-main-img { height:240px; }
    .detail-grid { grid-template-columns:1fr; }
    .prop-title { font-size:1.2rem; }
    .stat-item.price { font-size:1.15rem; }
    #propertyMap { height:260px; }
}
@media (max-width:576px) {
    .swiper-main-img { height:200px; }
}
</style>

<!-- ============================================================
     BREADCRUMB
     ============================================================ -->
<nav class="breadcrumb-bar" aria-label="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
    <div class="container">
        <?php foreach ($breadcrumb as $i => $crumb): ?>
            <?php if ($i > 0): ?><span class="separator">›</span><?php endif; ?>
            <?php if ($crumb['url']): ?>
                <a href="<?= $crumb['url'] ?>" itemprop="item">
                    <span itemprop="name"><?= $crumb['label'] ?></span>
                </a>
            <?php else: ?>
                <span class="fw-600" itemprop="name"><?= $crumb['label'] ?></span>
            <?php endif; ?>
            <meta itemprop="position" content="<?= $i + 1 ?>">
        <?php endforeach; ?>
    </div>
</nav>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<div class="detail-wrap">
<div class="container detail-main mt-4">
<div class="row g-4">

    <!-- ========================================================
         LEFT COLUMN – Nội dung chính
         ======================================================== -->
    <div class="col-lg-8">

        <!-- GALLERY -->
        <section data-aos="fade-up" id="gallerySection">
            <?php
            $galleryCount = count($allImages);
            ?>
            <!-- LightGallery container (hidden links) -->
            <div id="galleryLightbox" class="d-none">
                <?php foreach ($allImages as $img): ?>
                    <a href="<?= img_url($img['duong_dan_anh'] ?? $img) ?>" class="lg-item">
                        <img src="<?= img_url($img['duong_dan_anh'] ?? $img) ?>" alt="Gallery">
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="gallery-wrap">
                <!-- SwiperJS Main -->
                <div id="swiperMain" class="swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($allImages as $idx => $img): ?>
                            <div class="swiper-slide">
                                <img data-src="<?= img_url($img['duong_dan_anh'] ?? $img) ?>"
                                     src="<?= $idx === 0 ? img_url($img['duong_dan_anh'] ?? $img) : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' ?>"
                                     class="swiper-main-img swiper-lazy"
                                     alt="Ảnh BĐS <?= $idx + 1 ?>"
                                     onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800'">
                                <div class="swiper-lazy-preloader"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                    <!-- Count badge -->
                    <?php if ($galleryCount > 1): ?>
                        <div class="gallery-count-badge">
                            <i class="fa-regular fa-image me-1"></i><?= $galleryCount ?> ảnh
                        </div>
                    <?php endif; ?>
                    <!-- Fullscreen button (triggers LightGallery) -->
                    <button class="btn-gallery-full" id="btnGalleryFull" title="Xem toàn màn hình">
                        <i class="fa-solid fa-expand me-1"></i>Toàn màn hình
                    </button>
                </div>

                <!-- SwiperJS Thumbnails -->
                <?php if ($galleryCount > 1): ?>
                    <div id="swiperThumbs" class="swiper swiper-thumbs-wrap">
                        <div class="swiper-wrapper">
                            <?php foreach ($allImages as $img): ?>
                                <div class="swiper-slide">
                                    <img src="<?= img_url($img['duong_dan_anh'] ?? $img) ?>" alt="thumb"
                                         onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=80'">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- VIDEO & 360° -->
        <?php if ($hasVideo || $has360): ?>
        <section class="mt-4" data-aos="fade-up" data-aos-delay="50">
            <div class="section-title"><i class="fa-solid fa-play-circle"></i>Video & Tham quan</div>
            <ul class="nav media-tabs gap-2 mb-3" role="tablist">
                <?php if ($hasVideo): ?>
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabVideo" role="tab">
                            <i class="fa-brands fa-youtube me-1"></i>Video
                        </button>
                    </li>
                <?php endif; ?>
                <?php if ($has360): ?>
                    <li class="nav-item">
                        <button class="nav-link <?= !$hasVideo ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab360" role="tab">
                            <i class="fa-solid fa-street-view me-1"></i>360°
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="tab-content">
                <?php if ($hasVideo): ?>
                    <div class="tab-pane fade show active" id="tabVideo">
                        <div class="media-embed">
                            <?php if ($videoEmbed['type'] === 'youtube' || $videoEmbed['type'] === 'iframe'): ?>
                                <iframe src="<?= htmlspecialchars($videoEmbed['src'], ENT_QUOTES) ?>" allowfullscreen loading="lazy" title="Video BĐS"></iframe>
                            <?php elseif ($videoEmbed['type'] === 'mp4'): ?>
                                <video controls preload="metadata">
                                    <source src="<?= htmlspecialchars($videoEmbed['src'], ENT_QUOTES) ?>" type="video/mp4">
                                </video>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($has360): ?>
                    <div class="tab-pane fade <?= !$hasVideo ? 'show active' : '' ?>" id="tab360">
                        <div class="media-embed">
                            <iframe src="<?= htmlspecialchars($project->link_tour_360, ENT_QUOTES) ?>" allowfullscreen loading="lazy" title="Tour 360°"></iframe>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- TIÊU ĐỀ + THỐNG KÊ -->
        <section class="mt-4" data-aos="fade-up" data-aos-delay="60">
            <?php if (Session::get('msg')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= Session::get('msg') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php Session::delete('msg'); ?>
            <?php endif; ?>

            <!-- Badges -->
            <div class="d-flex flex-wrap gap-2 mb-2">
                <?php if (($project->goi_vip ?? 0) > 0): ?>
                    <span class="prop-badge badge-vip">
                        <i class="fa-solid fa-star"></i>VIP<?= $project->goi_vip ?>
                    </span>
                <?php endif; ?>
                <?php if ($project->loai_bat_dong_san): ?>
                    <span class="prop-badge badge-type">
                        <i class="fa-solid fa-home"></i><?= htmlspecialchars($project->loai_bat_dong_san, ENT_QUOTES) ?>
                    </span>
                <?php endif; ?>
                <?php if ($project->chinh_chu): ?>
                    <span class="prop-badge" style="background:#fce4ec;color:#c62828;">
                        <i class="fa-solid fa-user-check"></i>Chính chủ
                    </span>
                <?php endif; ?>
                <?php if ($project->phap_ly): ?>
                    <span class="prop-badge" style="background:#f3e5f5;color:#6a1b9a;">
                        <i class="fa-solid fa-file-contract"></i><?= match($project->phap_ly) {
                            'so_do'   => 'Sổ đỏ',
                            'so_hong' => 'Sổ hồng',
                            'giay_tay'=> 'Giấy tay',
                            default   => htmlspecialchars($project->phap_ly ?? '', ENT_QUOTES)
                        } ?>
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="prop-title"><?= htmlspecialchars($project->tieu_de ?? '', ENT_QUOTES) ?></h1>

            <!-- Địa chỉ -->
            <?php if ($project->vi_tri): ?>
                <p class="text-muted mb-3" style="font-size:.9rem;">
                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                    <?= htmlspecialchars($project->vi_tri, ENT_QUOTES) ?>
                </p>
            <?php endif; ?>

            <!-- Stats bar -->
            <div class="stats-bar">
                <div class="stat-item price">
                    <i class="fa-solid fa-tag"></i>
                    <span class="val"><?= formatGia((string)($project->gia ?? '')) ?></span>
                </div>
                <?php if ($project->dien_tich): ?>
                    <div class="stat-item">
                        <i class="fa-solid fa-vector-square"></i>
                        <span class="val"><?= htmlspecialchars($project->dien_tich, ENT_QUOTES) ?>m²</span>
                    </div>
                <?php endif; ?>
                <div class="stat-item">
                    <i class="fa-regular fa-clock"></i>
                    <span><?= date('d/m/Y', strtotime($project->ngay_tao)) ?></span>
                </div>
                <div class="stat-item">
                    <i class="fa-solid fa-hashtag"></i>
                    <span>Mã tin: <span class="val"><?= str_pad($project->id, 6, '0', STR_PAD_LEFT) ?></span></span>
                </div>
                <div class="stat-item ms-auto">
                    <i class="fa-regular fa-eye"></i>
                    <span id="viewCount" class="val"><?= number_format((int)($project->luot_xem ?? 0)) ?></span>
                    <span>lượt xem</span>
                </div>
                <div class="stat-item">
                    <i class="fa-regular fa-heart"></i>
                    <span id="saveCount" class="val"><?= number_format((int)($project->tong_luot_luu ?? 0)) ?></span>
                    <span>lưu</span>
                </div>
                <div class="stat-item">
                    <i class="fa-solid fa-share-nodes"></i>
                    <span id="shareCount" class="val"><?= number_format((int)($project->luot_chia_se ?? 0)) ?></span>
                    <span>chia sẻ</span>
                </div>
            </div>
        </section>

        <!-- THÔNG TIN CHI TIẾT -->
        <section class="mt-4" data-aos="fade-up" data-aos-delay="80">
            <div class="section-title"><i class="fa-solid fa-list-ul"></i>Thông tin chi tiết</div>
            <div class="detail-grid">
                <?php
                $details = [
                    ['label' => 'Giá',          'icon' => 'fa-tag',          'value' => formatGia((string)($project->gia ?? ''))],
                    ['label' => 'Diện tích',     'icon' => 'fa-vector-square','value' => $project->dien_tich ? htmlspecialchars($project->dien_tich).'m²' : '—'],
                    ['label' => 'Mặt tiền',      'icon' => 'fa-road',         'value' => !empty($project->mat_tien) ? htmlspecialchars($project->mat_tien).'m' : '—'],
                    ['label' => 'Đường trước nhà','icon'=> 'fa-map-road',     'value' => !empty($project->duong_truoc_nha) ? htmlspecialchars($project->duong_truoc_nha).'m' : '—'],
                    ['label' => 'Hướng nhà',     'icon' => 'fa-compass',      'value' => !empty($project->huong_nha) ? htmlspecialchars($project->huong_nha) : '—'],
                    ['label' => 'Hướng ban công','icon' => 'fa-wind',         'value' => !empty($project->huong_ban_cong) ? htmlspecialchars($project->huong_ban_cong) : '—'],
                    ['label' => 'Số tầng',       'icon' => 'fa-building',     'value' => !empty($project->so_tang) ? htmlspecialchars($project->so_tang) : '—'],
                    ['label' => 'Phòng ngủ',     'icon' => 'fa-bed',          'value' => !empty($project->so_phong_ngu) ? htmlspecialchars($project->so_phong_ngu) : '—'],
                    ['label' => 'Phòng WC',      'icon' => 'fa-bath',         'value' => !empty($project->so_phong_wc) ? htmlspecialchars($project->so_phong_wc) : '—'],
                    ['label' => 'Nội thất',      'icon' => 'fa-couch',        'value' => !empty($project->noi_that) ? htmlspecialchars($project->noi_that) : '—'],
                    ['label' => 'Pháp lý',       'icon' => 'fa-file-contract','value' => !empty($project->phap_ly) ? match($project->phap_ly){'so_do'=>'Sổ đỏ','so_hong'=>'Sổ hồng','giay_tay'=>'Giấy tay','cho_so'=>'Chờ sổ',default=>htmlspecialchars($project->phap_ly)} : '—'],
                    ['label' => 'Năm xây dựng',  'icon' => 'fa-calendar',     'value' => !empty($project->nam_xay_dung) ? htmlspecialchars($project->nam_xay_dung) : '—'],
                ];
                foreach ($details as $d): ?>
                    <div class="detail-cell">
                        <span class="label"><?= $d['label'] ?></span>
                        <span class="value"><i class="fa-solid <?= $d['icon'] ?>"></i><?= $d['value'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- MÔ TẢ -->
        <?php $descText = $project->mo_ta ?: ($project->noi_dung ?? ''); ?>
        <?php if ($descText): ?>
        <section class="mt-4" data-aos="fade-up" data-aos-delay="90">
            <div class="section-title"><i class="fa-solid fa-align-left"></i>Mô tả chi tiết</div>
            <div id="descContent" class="desc-content"><?= nl2br(htmlspecialchars($descText, ENT_QUOTES)) ?></div>
            <div class="mt-3 d-flex gap-2">
                <button id="btnDescMore" class="btn btn-sm btn-outline-secondary rounded-pill">
                    <i class="fa-solid fa-chevron-down me-1"></i>Xem thêm
                </button>
                <button id="btnDescLess" class="btn btn-sm btn-outline-secondary rounded-pill d-none">
                    <i class="fa-solid fa-chevron-up me-1"></i>Thu gọn
                </button>
            </div>
        </section>
        <?php endif; ?>

        <!-- TIỆN ÍCH LÂN CẬN -->
        <?php if (!empty($amenities)): ?>
        <section class="mt-4" data-aos="fade-up" data-aos-delay="100">
            <div class="section-title"><i class="fa-solid fa-location-pin-lock"></i>Tiện ích lân cận</div>
            <div class="row g-2">
                <?php foreach ($amenities as $am): ?>
                    <div class="col-sm-6 col-md-4">
                        <div class="amenity-item">
                            <div class="amenity-icon" style="background:<?= htmlspecialchars($am['color'] ?? '#6c757d', ENT_QUOTES) ?>;">
                                <i class="fa-solid <?= htmlspecialchars($am['icon'] ?? 'fa-map-marker-alt', ENT_QUOTES) ?>"></i>
                            </div>
                            <div>
                                <div style="font-size:.88rem;font-weight:600;"><?= $am['ten'] ?></div>
                                <?php if (!empty($am['khoang_cach'])): ?>
                                    <div style="font-size:.78rem;color:var(--text-muted);"><?= htmlspecialchars($am['khoang_cach'], ENT_QUOTES) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- BẢN ĐỒ -->
        <section class="mt-4" id="mapSection" data-aos="fade-up" data-aos-delay="110">
            <div class="section-title d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-map"></i>Bản đồ vị trí</span>
                <?php if ($mapData['hasMap']): ?>
                    <button id="btnStreetView" class="btn btn-sm btn-outline-secondary rounded-pill">
                        <i class="fa-solid fa-street-view me-1"></i>Street View
                    </button>
                <?php endif; ?>
            </div>
            <?php if ($mapData['hasMap']): ?>
                <div id="propertyMap"
                     data-lat="<?= $mapData['lat'] ?>"
                     data-lng="<?= $mapData['lng'] ?>"
                     data-title="<?= $mapData['title'] ?>"
                     data-address="<?= $mapData['address'] ?>">
                </div>
            <?php else: ?>
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height:200px;">
                    <div class="text-center text-muted">
                        <i class="fa-solid fa-map-location-dot fa-3x mb-2 d-block"></i>
                        <div>
                            <?php if ($project->vi_tri): ?>
                                <strong>Địa chỉ:</strong> <?= htmlspecialchars($project->vi_tri, ENT_QUOTES) ?>
                            <?php else: ?>
                                Chưa có tọa độ bản đồ.
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- TỪ KHÓA TÌM KIẾM -->
        <section class="mt-4" data-aos="fade-up">
            <div class="d-flex flex-wrap gap-2">
                <?php
                $keywords = array_filter([
                    $project->loai_bat_dong_san ?? '',
                    $project->tinh_thanh ?? '',
                    trim(($project->loai_bat_dong_san ?? '') . ' ' . ($project->tinh_thanh ?? '')),
                ]);
                foreach (array_unique($keywords) as $kw):
                    if (!trim($kw)) continue;
                ?>
                    <a href="<?= URL_ROOT ?>/du-an/search?q=<?= urlencode(trim($kw)) ?>"
                       class="badge bg-light text-secondary border text-decoration-none px-3 py-2 rounded-pill"
                       style="font-size:.82rem;">
                        <i class="fa-solid fa-hashtag me-1"></i><?= htmlspecialchars(trim($kw), ENT_QUOTES) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- TIN LIÊN QUAN -->
        <?php if (!empty($related)): ?>
        <section class="mt-5" data-aos="fade-up">
            <div class="section-title">
                <i class="fa-solid fa-fire-flame-curved text-danger"></i>Bất động sản liên quan
            </div>
            <div class="row g-3">
                <?php foreach ($related as $r): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="<?= URL_ROOT ?>/du-an/detail/<?= htmlspecialchars($r->duong_dan, ENT_QUOTES) ?>"
                           class="text-decoration-none" data-aos="zoom-in">
                            <div class="related-card">
                                <img src="<?= img_url($r->anh_thu_nho ?? '') ?>"
                                     alt="<?= htmlspecialchars($r->tieu_de ?? '', ENT_QUOTES) ?>"
                                     class="related-card-img"
                                     loading="lazy"
                                     onerror="this.src='https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400'">
                                <div class="related-card-body">
                                    <div class="related-card-title"><?= htmlspecialchars($r->tieu_de ?? '', ENT_QUOTES) ?></div>
                                    <div class="related-card-price"><?= formatGia((string)($r->gia ?? '')) ?></div>
                                    <div class="related-card-meta mt-1">
                                        <i class="fa-solid fa-vector-square"></i>
                                        <?= htmlspecialchars($r->dien_tich ?? '—', ENT_QUOTES) ?>m²
                                        <span class="mx-1">·</span>
                                        <i class="fa-solid fa-location-dot"></i>
                                        <?= htmlspecialchars(explode(',', $r->vi_tri ?? '')[0], ENT_QUOTES) ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- ========================================================
         RIGHT SIDEBAR
         ======================================================== -->
    <div class="col-lg-4">
    <div class="sidebar-sticky">

        <!-- SELLER CARD -->
        <div class="seller-card mb-3" data-aos="fade-left">
            <div class="seller-header">
                <?php
                $avatar = !empty($project->avatar_nguoi_dung)
                    ? URL_ROOT . '/uploads/avatars/' . $project->avatar_nguoi_dung
                    : 'https://ui-avatars.com/api/?name=' . urlencode($project->ten_nguoi_dung ?? 'ND') . '&background=00a8a8&color=fff&size=80';
                ?>
                <img src="<?= $avatar ?>" class="seller-avatar" alt="Avatar người đăng">
                <p class="seller-name"><?= htmlspecialchars($project->ten_nguoi_dung ?? 'Người dùng', ENT_QUOTES) ?></p>
                <p class="seller-meta mb-1">
                    <span class="verified-badge">
                        <i class="fa-solid fa-circle-check"></i>Đã xác thực
                    </span>
                </p>
            </div>
            <div class="seller-body">
                <div class="seller-stat">
                    <span class="label"><i class="fa-solid fa-list me-1"></i>Số tin đăng</span>
                    <span class="val"><?= number_format((int)($project->so_tin_dang ?? 0)) ?> tin</span>
                </div>
                <div class="seller-stat">
                    <span class="label"><i class="fa-regular fa-calendar me-1"></i>Tham gia</span>
                    <span class="val"><?= !empty($project->ngay_tham_gia) ? date('m/Y', strtotime($project->ngay_tham_gia)) : '—' ?></span>
                </div>
                <div class="seller-stat">
                    <span class="label"><i class="fa-solid fa-star me-1 text-warning"></i>Đánh giá</span>
                    <span class="val text-end">
                        <span id="propertyRatingStars" class="d-inline-flex gap-1" role="radiogroup" aria-label="Đánh giá tin đăng">
                            <?php for($star=1;$star<=5;$star++): ?>
                                <button type="button" class="rating-star border-0 bg-transparent p-0 <?= $star<=($userRating?:round($ratingSummary['average']))?'text-warning':'text-secondary' ?>" data-stars="<?=$star?>" aria-label="<?=$star?> sao" style="font-size:1.05rem;line-height:1;cursor:pointer"><i class="fa-solid fa-star"></i></button>
                            <?php endfor; ?>
                        </span>
                        <small id="propertyRatingText" class="d-block text-muted mt-1"><?=number_format((float)$ratingSummary['average'],1)?>/5 · <?=number_format((int)$ratingSummary['total'])?> lượt</small>
                    </span>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="px-3 pb-3 d-flex flex-column gap-2">
                <?php if ($phone): ?>
                    <?php if (Session::get('user_id')): ?>
                        <button id="btnPhoneReveal" class="action-btn btn-call">
                            <i class="fa-solid fa-phone"></i>Hiện số điện thoại
                        </button>
                        <a id="btnPhoneCall" href="tel:<?= $phone ?>"
                           class="action-btn btn-call d-none text-decoration-none">
                            <i class="fa-solid fa-phone-volume"></i>Gọi: <?= htmlspecialchars($project->dien_thoai ?? $phone, ENT_QUOTES) ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap" class="action-btn btn-call text-decoration-none" style="background:#5645d4;">
                            <i class="fa-solid fa-lock"></i>Đăng nhập để xem SĐT
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <button class="action-btn btn-zalo" data-analytics-event="zalo"
                        data-url="<?= htmlspecialchars($zaloUrl, ENT_QUOTES) ?>">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a1/Zalo_Logo.svg/512px-Zalo_Logo.svg.png"
                         width="18" class="bg-white rounded-circle" alt="Zalo">Chat Zalo
                </button>

                <?php if (Session::get('user_id')): ?>
                    <button class="action-btn btn btn-outline-primary" data-chat-seller>
                        <i class="fa-regular fa-comments"></i>Chat với người bán
                    </button>
                <?php endif; ?>

                <!-- LƯU TIN -->
                <form action="<?= URL_ROOT ?>/nguoi-dung/toggleLuu/<?= $project->id ?>" method="POST">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($project->duong_dan, ENT_QUOTES) ?>">
                    <?php if (Session::get('user_id')): ?>
                        <button type="submit" class="action-btn btn-save <?= $isSaved ? 'btn btn-danger' : 'btn btn-outline-danger' ?> w-100">
                            <i class="<?= $isSaved ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                            <?= $isSaved ? 'Đã lưu tin (Bỏ lưu)' : 'Lưu tin BĐS' ?>
                        </button>
                    <?php else: ?>
                        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap" class="action-btn btn btn-outline-danger w-100 text-decoration-none">
                            <i class="fa-regular fa-heart"></i>Đăng nhập để lưu tin
                        </a>
                    <?php endif; ?>
                </form>

                <!-- SO SÁNH -->
                <button id="btnCompare" class="action-btn btn btn-outline-warning"
                        data-post-id="<?= $project->id ?>">
                    <i class="fa-solid fa-scale-balanced"></i>So sánh BĐS
                </button>
            </div>

            <!-- CHIA SẺ -->
            <div class="px-3 pb-3">
                <div class="share-row">
                    <span style="font-size:.85rem;color:var(--text-muted);font-weight:600;">Chia sẻ:</span>
                    <!-- Facebook -->
                    <button class="share-btn" style="color:#1877f2;border-color:#1877f2;"
                            data-share-platform="facebook"
                            data-share-url="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
                            title="Facebook"><i class="fa-brands fa-facebook-f"></i></button>
                    <!-- Messenger -->
                    <button class="share-btn" style="color:#0084ff;border-color:#0084ff;"
                            data-share-platform="messenger"
                            data-share-url="fb-messenger://share/?link=<?= urlencode($shareUrl) ?>"
                            title="Messenger"><i class="fa-brands fa-facebook-messenger"></i></button>
                    <!-- Zalo -->
                    <button class="share-btn" style="color:#0068ff;border-color:#0068ff;"
                            data-share-platform="zalo"
                            data-share-url="https://zalo.me/share?url=<?= urlencode($shareUrl) ?>"
                            title="Zalo"><i class="fa-solid fa-comment-dots"></i></button>
                    <!-- Telegram -->
                    <button class="share-btn" style="color:#229ed9;border-color:#229ed9;"
                            data-share-platform="telegram"
                            data-share-url="https://t.me/share/url?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($project->tieu_de) ?>"
                            title="Telegram"><i class="fa-brands fa-telegram"></i></button>
                    <!-- Copy link -->
                    <button class="share-btn" style="color:#6c757d;border-color:#6c757d;"
                            data-share-platform="copy"
                            title="Copy link"><i class="fa-solid fa-link"></i></button>

                    <!-- Báo cáo vi phạm -->
                    <button class="ms-auto btn btn-sm btn-link text-danger p-0 text-decoration-none"
                            data-bs-toggle="modal" data-bs-target="#reportModal"
                            title="Báo cáo vi phạm" style="font-size:.8rem;">
                        <i class="fa-solid fa-flag me-1"></i>Báo cáo
                    </button>
                </div>
            </div>

            <!-- Xem tất cả tin của người đăng -->
            <div class="px-3 pb-3">
                <a href="<?= URL_ROOT ?>/du-an/author/<?= $project->ma_nguoi_dung ?>"
                   class="btn btn-sm btn-outline-secondary w-100 rounded-pill">
                    <i class="fa-solid fa-angles-right me-1"></i>Xem tất cả tin của người đăng
                </a>
            </div>
        </div>

        <!-- NHẬN TƯ VẤN NHANH -->
        <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden" data-aos="fade-left" data-aos-delay="50">
            <div style="background:linear-gradient(135deg,var(--accent),#0070a8);height:4px;"></div>
            <div class="card-body p-3">
                <h5 class="fw-700 mb-1 d-flex align-items-center gap-2" style="color:var(--text-main);">
                    <i class="fa-solid fa-headset text-primary"></i>Nhận tư vấn nhanh
                </h5>
                <p class="text-muted small mb-3">Để lại thông tin, chúng tôi liên hệ lại ngay.</p>

                <?php if (Session::get('contact_success')): ?>
                    <div class="alert alert-success py-2 small"><i class="fa-solid fa-circle-check me-1"></i>
                        <?= Session::get('contact_success') ?></div>
                    <?php Session::delete('contact_success'); ?>
                <?php endif; ?>

                <form action="<?= URL_ROOT ?>/contact" method="POST" class="quick-form">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="project_id"   value="<?= $project->id ?>">
                    <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($shareUrl, ENT_QUOTES) ?>">

                    <div class="mb-2">
                        <input type="text" name="name" class="form-control" placeholder="Họ và tên *" required
                               value="<?= htmlspecialchars(Session::get('user_name') ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-2">
                        <input type="tel" name="phone" class="form-control" placeholder="Số điện thoại *" required
                               value="<?= htmlspecialchars(Session::get('user_phone') ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-2">
                        <input type="email" name="email" class="form-control" placeholder="Email (tùy chọn)"
                               value="<?= htmlspecialchars(Session::get('user_email') ?? '', ENT_QUOTES) ?>">
                    </div>
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="2"
                                  placeholder="Lời nhắn..."
                                  style="resize:none;"><?= 'Tôi quan tâm đến tin "' . htmlspecialchars($project->tieu_de ?? '', ENT_QUOTES) . '". Vui lòng tư vấn giúp tôi.' ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit btn w-100">
                        <i class="fa-regular fa-paper-plane me-2"></i>Gửi yêu cầu tư vấn
                    </button>
                </form>
            </div>
        </div>

    </div><!-- /sidebar-sticky -->
    </div><!-- /col-lg-4 -->

</div><!-- /row -->
</div><!-- /container -->
</div><!-- /detail-wrap -->

<!-- ============================================================
     MODAL: BÁO CÁO VI PHẠM
     ============================================================ -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700" id="reportModalLabel">
                    <i class="fa-solid fa-flag text-danger me-2"></i>Báo cáo vi phạm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Chọn lý do phù hợp để chúng tôi xem xét tin đăng này.</p>
                <form id="reportForm" action="<?= URL_ROOT ?>/du-an/report/<?= $project->id ?>" method="POST" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-600">Lý do báo cáo <span class="text-danger">*</span></label>
                        <select name="ly_do" class="form-select" required>
                            <option value="">-- Chọn lý do --</option>
                            <?php foreach ($reportReasons as $key => $label): ?>
                                <option value="<?= $key ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Mô tả chi tiết</label>
                        <textarea name="mo_ta" class="form-control" rows="3" placeholder="Mô tả thêm về vi phạm (tùy chọn)..." style="resize:none;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600" for="reportEvidence">Ảnh minh chứng <span class="text-muted fw-normal">(tùy chọn)</span></label>
                        <input id="reportEvidence" type="file" name="minh_chung[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                        <div class="form-text">Tối đa 3 ảnh JPG, PNG hoặc WebP; không quá 5 MB mỗi ảnh.</div>
                        <div id="reportEvidencePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                    <?php if (!Session::get('user_id')): ?>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <input type="text" name="ho_ten" class="form-control" placeholder="Họ tên (tùy chọn)">
                            </div>
                            <div class="col-6">
                                <input type="email" name="email" class="form-control" placeholder="Email (tùy chọn)">
                            </div>
                        </div>
                    <?php endif; ?>
                    <div id="reportFeedback"></div>
                    <button type="submit" class="btn btn-danger w-100 fw-700">
                        <i class="fa-solid fa-paper-plane me-1"></i>Gửi báo cáo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     ANALYTICS CONFIG + SCRIPTS
     ============================================================ -->
<script>
window.PostAnalyticsConfig = {
    postId:   <?= $postId ?>,
    endpoint: '<?= URL_ROOT ?>/analytics',
    csrf:     '<?= Csrf::token() ?>',
    shareUrl: '<?= htmlspecialchars($shareUrl, ENT_QUOTES, 'UTF-8') ?>',
};
window.GMAP_KEY = '<?= defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '' ?>';
</script>

<!-- SwiperJS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- LightGallery -->
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/lightgallery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/zoom/lg-zoom.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/thumbnail/lg-thumbnail.min.js"></script>
<!-- AOS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<!-- Property Detail Module -->
<script src="<?= URL_ROOT ?>/public/js/post-analytics.js?v=<?= filemtime(APP_ROOT . '/public/js/post-analytics.js') ?>"></script>
<script src="<?= URL_ROOT ?>/public/js/property-detail.js?v=<?= filemtime(APP_ROOT . '/public/js/property-detail.js') ?>"></script>

<script>
// Fullscreen gallery trigger
document.getElementById('btnGalleryFull')?.addEventListener('click', () => {
    document.querySelector('#galleryLightbox .lg-item')?.click();
});

// Lightgallery init
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lightGallery !== 'undefined') {
        lightGallery(document.getElementById('galleryLightbox'), {
            selector: '.lg-item',
            plugins: [lgZoom, lgThumbnail],
            speed: 400,
            thumbnail: true,
            showZoomInOutIcons: true,
            actualSize: false,
        });
    }
});

const ratingButtons=[...document.querySelectorAll('.rating-star')];
ratingButtons.forEach(button=>button.addEventListener('click',async()=>{
    const stars=Number(button.dataset.stars);const body=new FormData();
    body.append('_csrf_token',<?=json_encode(Csrf::token())?>);body.append('stars',String(stars));
    ratingButtons.forEach(item=>item.disabled=true);
    try{
        const response=await fetch(<?=json_encode(URL_ROOT.'/du-an/rate/'.(int)$project->id)?>,{method:'POST',body,headers:{'X-Requested-With':'XMLHttpRequest'}});
        const data=await response.json();
        if(!response.ok||!data.success)throw new Error(data.message||'Không thể lưu đánh giá.');
        ratingButtons.forEach(item=>item.classList.toggle('text-warning',Number(item.dataset.stars)<=stars));
        ratingButtons.forEach(item=>item.classList.toggle('text-secondary',Number(item.dataset.stars)>stars));
        document.getElementById('propertyRatingText').textContent=`${Number(data.rating.average).toFixed(1)}/5 · ${data.rating.total} lượt`;
        if(window.AdminDialog)AdminDialog.alert(data.message,{type:'success'});
    }catch(error){
        if(window.AdminDialog)AdminDialog.alert(error.message,{type:'warning'});else alert(error.message);
    }finally{ratingButtons.forEach(item=>item.disabled=false);}
}));
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
