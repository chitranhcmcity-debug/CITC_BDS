<?php
/**
 * View: Trang hồ sơ người đăng tin BĐS
 * Route: /author/{userId}
 *
 * Biến nhận từ AuthorController::index():
 *  $profile, $stats, $posts, $pagination,
 *  $filters, $isFollowing, $reviews, $ratingDist,
 *  $seo, $userId, $csrfToken,
 *  $title, $metaDescription, $canonicalUrl, $ogImage, $schemaJson
 */

$ua        = $profile; // shorthand
$star      = (float)($ua->diem_trung_binh ?? 0);
$starFull  = (int)floor($star);
$starHalf  = ($star - $starFull) >= 0.5;
$avatar    = !empty($ua->anh_dai_dien)
    ? URL_ROOT . '/uploads/avatars/' . htmlspecialchars($ua->anh_dai_dien, ENT_QUOTES)
    : 'https://ui-avatars.com/api/?name=' . urlencode($ua->ten ?? 'ND') . '&background=00a8a8&color=fff&size=200';
$loaiLabel = match($ua->loai_tai_khoan ?? 'ca_nhan') {
    'moi_gioi' => 'Môi giới BĐS',
    'cong_ty'  => 'Công ty BĐS',
    default    => 'Cá nhân',
};
$currentUserId = (int)(Session::get('user_id') ?? 0);
?>
<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ',   'item' => URL_ROOT],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'BĐS',         'item' => URL_ROOT . '/du-an'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => ($ua->ten ?? 'Người đăng'), 'item' => $canonicalUrl],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>

<!-- AOS + Google Fonts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<!-- Failsafe: ensure AOS elements are always visible (prevent invisible content) -->
<style>[data-aos]{opacity:1!important;transform:none!important;transition:none!important;}</style>

<style>
/* ====================================================
   AUTHOR PROFILE – Custom Styles
   ==================================================== */
:root {
    --ap:       #00a8a8;
    --ap-accent:#ff6b35;
    --ap-dark:  #1a2332;
    --ap-text:  #4a5568;
    --ap-border:#e2e8f0;
    --ap-bg:    #f7fafc;
    --ap-card:  #ffffff;
    --vip-gold: #f59e0b;
}

.author-page-body { font-family: 'Inter', sans-serif; background: var(--ap-bg); }

/* ── HERO ── */
.author-hero {
    background: linear-gradient(135deg, #1a2332 0%, #2d3748 60%, #1a365d 100%);
    padding: 56px 0 72px;
    position: relative; overflow: hidden;
}
.author-hero::before {
    content: ''; position: absolute; inset: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.025)" d="M0,96L60,112C120,128,240,160,360,154.7C480,149,600,107,720,96C840,85,960,107,1080,117.3C1200,128,1320,128,1380,128L1440,128L1440,320L0,320Z"/></svg>') no-repeat bottom/cover;
}
.author-avatar-wrap { position: relative; display: inline-block; }
.author-avatar {
    width: 110px; height: 110px; border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.2); object-fit: cover;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}
.author-verified {
    position: absolute; bottom: 4px; right: 4px;
    width: 26px; height: 26px; background: var(--ap);
    border-radius: 50%; border: 2px solid white;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; color: white;
}
.author-name { font-size: clamp(1.3rem,3vw,1.9rem); font-weight: 800; color: #fff; letter-spacing: -0.02em; }
.author-type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(0,168,168,0.2); border: 1px solid rgba(0,168,168,0.4);
    color: #5ee7e7; padding: 3px 12px; border-radius: 20px;
    font-size: 0.76rem; font-weight: 600;
}
.stat-pill {
    display: inline-flex; flex-direction: column; align-items: center;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    backdrop-filter: blur(8px);
    border-radius: 14px; padding: 10px 18px; min-width: 82px;
}
.stat-pill .sv { font-size: 1.25rem; font-weight: 700; color: #fff; line-height: 1; }
.stat-pill .sl { font-size: 0.65rem; color: rgba(255,255,255,0.55); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }

/* ── BUTTONS ── */
.btn-call {
    background: linear-gradient(135deg, #22c55e, #16a34a); color: white; border: none;
    border-radius: 50px; padding: 9px 22px; font-weight: 600; font-size: 0.875rem;
    box-shadow: 0 4px 15px rgba(34,197,94,0.3); transition: all .25s;
}
.btn-call:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 20px rgba(34,197,94,0.4); }
.btn-zalo {
    background: linear-gradient(135deg, #0068ff, #0052cc); color: white; border: none;
    border-radius: 50px; padding: 9px 22px; font-weight: 600; font-size: 0.875rem;
    box-shadow: 0 4px 15px rgba(0,104,255,0.3); transition: all .25s;
}
.btn-zalo:hover { transform: translateY(-2px); color: white; }
.btn-follow-main {
    border-radius: 50px; padding: 9px 22px; font-weight: 600; font-size: 0.875rem;
    transition: all .25s;
}
.btn-follow-main.following {
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.25);
    color: rgba(255,255,255,0.65);
}
.btn-follow-main:not(.following) {
    background: white; color: var(--ap-dark); border: none;
    box-shadow: 0 4px 15px rgba(255,255,255,0.15);
}
.btn-follow-main:not(.following):hover { transform: translateY(-2px); }

/* ── CARDS & LAYOUT ── */
.content-card {
    background: var(--ap-card); border-radius: 18px;
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
    border: 1px solid var(--ap-border); overflow: hidden;
}
.content-card-header {
    padding: 18px 22px; border-bottom: 1px solid var(--ap-border);
    font-weight: 700; font-size: 0.95rem; color: var(--ap-dark);
    display: flex; align-items: center; gap: 8px;
}
.content-card-header i { color: var(--ap); }

/* ── FILTER BAR ── */
.filter-bar {
    background: white; border: 1px solid var(--ap-border);
    border-radius: 14px; padding: 14px 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.filter-bar .form-select, .filter-bar .form-control {
    border-radius: 10px; border-color: var(--ap-border); font-size: 0.85rem;
}
.filter-tab-btn {
    border: 1px solid transparent; border-radius: 10px;
    padding: 6px 14px; font-size: 0.83rem; font-weight: 500;
    color: var(--ap-text); background: transparent; cursor: pointer; transition: all .2s;
}
.filter-tab-btn.active {
    background: var(--ap); color: white; border-color: var(--ap);
}
.btn-filter-apply {
    background: var(--ap); color: white; border: none;
    border-radius: 10px; padding: 7px 18px; font-weight: 600; font-size: 0.85rem;
}

/* ── PROPERTY CARD ── */
.prop-card {
    background: white; border-radius: 14px; overflow: hidden;
    border: 1px solid var(--ap-border); transition: transform .25s, box-shadow .25s;
    position: relative;
}
.prop-card:hover { transform: translateY(-4px); box-shadow: 0 12px 36px rgba(0,0,0,0.11); }
.prop-card-img { height: 195px; overflow: hidden; position: relative; }
.prop-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
.prop-card:hover .prop-card-img img { transform: scale(1.06); }
.badge-vip {
    position: absolute; top: 9px; left: 9px;
    background: var(--vip-gold); color: white; font-size: 0.68rem;
    font-weight: 700; padding: 3px 9px; border-radius: 20px; letter-spacing: 0.04em;
}
.badge-cat {
    position: absolute; top: 9px; right: 9px;
    background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
    color: white; font-size: 0.68rem; padding: 3px 9px; border-radius: 20px;
}
.prop-price { font-size: 1.05rem; font-weight: 700; color: var(--ap-accent); }

/* ── STATS ROW ── */
.stat-item { text-align: center; padding: 14px 6px; border-right: 1px solid var(--ap-border); }
.stat-item:last-child { border-right: none; }
.stat-item .sn { font-size: 1.4rem; font-weight: 800; color: var(--ap-dark); display: block; line-height: 1; }
.stat-item .sl { font-size: 0.68rem; color: var(--ap-text); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 3px; }

/* ── RATING ── */
.stars-display { color: var(--vip-gold); font-size: 0.88rem; }
.rating-bar { background: #e2e8f0; border-radius: 4px; height: 7px; }
.rating-bar-fill { background: var(--vip-gold); border-radius: 4px; height: 100%; }

/* ── REVIEW CARD ── */
.review-card {
    background: #f8fafc; border-radius: 12px; padding: 14px;
    border-left: 3px solid var(--ap);
}
.review-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }

/* ── SIDEBAR ── */
.sidebar-contact {
    background: white; border-radius: 18px;
    border: 1px solid var(--ap-border);
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    padding: 22px; position: sticky; top: 80px;
}

/* ── PAGINATION ── */
.page-link { border-radius: 8px !important; margin: 0 2px; color: var(--ap); }
.page-item.active .page-link { background: var(--ap); border-color: var(--ap); color: white; }

/* ── TOAST ── */
.author-toast {
    position: fixed; bottom: 22px; right: 22px;
    background: var(--ap-dark); color: white;
    padding: 11px 18px; border-radius: 12px; font-size: 0.85rem;
    z-index: 9999; box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    transform: translateY(100px); opacity: 0;
    transition: all .35s cubic-bezier(0.25,1,0.5,1);
    pointer-events: none;
}
.author-toast.show { transform: translateY(0); opacity: 1; pointer-events: auto; }
.author-toast.success { border-left: 4px solid #22c55e; }
.author-toast.error   { border-left: 4px solid #ef4444; }

/* ── RESPONSIVE ── */
@media (max-width: 991px) {
    .author-hero { padding: 32px 0 52px; }
    .author-avatar { width: 85px; height: 85px; }
}
@media (max-width: 575px) {
    .stat-pill { padding: 8px 12px; min-width: 68px; }
    .stat-pill .sv { font-size: 1.1rem; }
    .prop-card-img { height: 160px; }
}
</style>

<!-- TOAST -->
<div id="authorToast" class="author-toast" role="alert" aria-live="polite"></div>

<!-- BREADCRUMB -->
<div class="bg-white border-bottom py-2">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="font-size:.82rem">
                <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/du-an">Bất động sản</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($ua->ten ?? 'Người đăng', ENT_QUOTES) ?>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- ====================================================
     HERO SECTION
     ==================================================== -->
<section class="author-hero" id="author-hero">
    <div class="container">
        <div class="row align-items-center gy-4">
            <!-- Avatar + Info -->
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <div class="author-avatar-wrap" data-aos="zoom-in">
                        <img src="<?= $avatar ?>"
                             alt="<?= htmlspecialchars($ua->ten ?? '', ENT_QUOTES) ?>"
                             class="author-avatar" loading="eager">
                        <?php if (!empty($ua->da_xac_thuc)): ?>
                        <div class="author-verified" title="Đã xác thực"><i class="fas fa-check fa-xs"></i></div>
                        <?php endif; ?>
                    </div>

                    <div data-aos="fade-right" data-aos-delay="80">
                        <p class="author-type-badge mb-2">
                            <i class="fas fa-user-tie fa-xs"></i>
                            <?= htmlspecialchars($loaiLabel, ENT_QUOTES) ?>
                        </p>
                        <h1 class="author-name mb-1">
                            <?= htmlspecialchars($ua->ten ?? 'Người đăng', ENT_QUOTES) ?>
                            <?php if (!empty($ua->da_xac_thuc)): ?>
                            <i class="fas fa-circle-check text-info" style="font-size:.85rem;vertical-align:middle" title="Đã xác thực"></i>
                            <?php endif; ?>
                        </h1>
                        <!-- Rating -->
                        <?php if ($star > 0): ?>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="stars-display">
                                <?php for ($i=1;$i<=5;$i++):
                                    echo $i<=$starFull ? '<i class="fas fa-star"></i>'
                                       : ($i==$starFull+1&&$starHalf ? '<i class="fas fa-star-half-alt"></i>' : '<i class="far fa-star"></i>');
                                endfor; ?>
                            </div>
                            <span class="text-white fw-semibold"><?= number_format($star,1) ?></span>
                            <span class="text-white-50 small">(<?= number_format($ua->so_danh_gia ?? 0) ?> đánh giá)</span>
                        </div>
                        <?php endif; ?>
                        <!-- Location + Meta -->
                        <div class="d-flex flex-wrap gap-3 text-white-50 small">
                            <?php if (!empty($ua->khu_vuc_hoat_dong)): ?>
                            <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ua->khu_vuc_hoat_dong, ENT_QUOTES) ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-calendar-alt me-1"></i>Tham gia <?= date('m/Y', strtotime($ua->ngay_tao ?? 'now')) ?></span>
                            <?php if (!empty($ua->ty_le_phan_hoi)): ?>
                            <span><i class="fas fa-reply me-1"></i>Phản hồi <?= $ua->ty_le_phan_hoi ?>% · <?= $ua->gio_phan_hoi_tb ?? '?' ?>h</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stats Pills -->
                <div class="d-flex flex-wrap gap-3 mt-4" data-aos="fade-up" data-aos-delay="120">
                    <div class="stat-pill">
                        <span class="sv"><?= number_format($ua->tong_tin_dang ?? 0) ?></span>
                        <span class="sl">Tin đăng</span>
                    </div>
                    <div class="stat-pill">
                        <span class="sv"><?= number_format($ua->tin_vip ?? 0) ?></span>
                        <span class="sl">Tin VIP</span>
                    </div>
                    <div class="stat-pill">
                        <span class="sv"><?= number_format((int)($ua->tong_luot_xem ?? 0)) ?></span>
                        <span class="sl">Lượt xem</span>
                    </div>
                    <div class="stat-pill">
                        <span class="sv" id="followerCount"><?= number_format($ua->so_follower ?? 0) ?></span>
                        <span class="sl">Theo dõi</span>
                    </div>
                </div>
            </div>

            <!-- Buttons + Description -->
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="160">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <?php if (!empty($ua->dien_thoai)): ?>
                    <button class="btn btn-call phone-reveal-btn" id="heroPhoneBtn"
                            data-phone="<?= htmlspecialchars($ua->dien_thoai, ENT_QUOTES) ?>"
                            data-author="<?= $userId ?>">
                        <i class="fas fa-phone me-2"></i><span class="phone-text">Xem SĐT</span>
                    </button>
                    <?php endif; ?>
                    <?php if (!empty($ua->zalo)): ?>
                    <a href="https://zalo.me/<?= htmlspecialchars($ua->zalo, ENT_QUOTES) ?>"
                       target="_blank" rel="noopener"
                       class="btn btn-zalo" data-author="<?= $userId ?>">
                        <i class="fas fa-comment-dots me-2"></i>Zalo
                    </a>
                    <?php endif; ?>
                    <?php if ($currentUserId && $currentUserId !== $userId): ?>
                    <button class="btn btn-follow-main <?= $isFollowing ? 'following' : '' ?>"
                            id="followBtn"
                            data-author-id="<?= $userId ?>"
                            data-following="<?= $isFollowing ? '1' : '0' ?>">
                        <i class="fas <?= $isFollowing ? 'fa-user-minus' : 'fa-user-plus' ?> me-2"></i>
                        <span><?= $isFollowing ? 'Đang theo dõi' : 'Theo dõi' ?></span>
                    </button>
                    <?php elseif (!$currentUserId): ?>
                    <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap" class="btn btn-follow-main">
                        <i class="fas fa-user-plus me-2"></i>Theo dõi
                    </a>
                    <?php endif; ?>
                </div>
                <?php if (!empty($ua->mo_ta_ca_nhan)): ?>
                <div class="mt-3 p-3 rounded-3" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);">
                    <p class="mb-0 small" style="color:rgba(255,255,255,0.7)">
                        <?= nl2br(htmlspecialchars($ua->mo_ta_ca_nhan, ENT_QUOTES)) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ====================================================
     MAIN CONTENT
     ==================================================== -->
<div class="container py-5">
    <div class="row g-4">
        <!-- ===== MAIN COLUMN ===== -->
        <div class="col-lg-8">

            <!-- STATS PANEL -->
            <div class="content-card mb-4" data-aos="fade-up">
                <div class="content-card-header">
                    <i class="fas fa-chart-bar"></i> Thống kê hoạt động
                </div>
                <div class="row g-0">
                    <div class="col stat-item">
                        <span class="sn"><?= number_format($stats['tong_tin'] ?? 0) ?></span>
                        <span class="sl">Tổng tin</span>
                    </div>
                    <div class="col stat-item">
                        <span class="sn text-success"><?= number_format($stats['dang_hien_thi'] ?? 0) ?></span>
                        <span class="sl">Đang hiển thị</span>
                    </div>
                    <div class="col stat-item">
                        <span class="sn" style="color:var(--ap-accent)"><?= number_format($stats['da_ban'] ?? 0) ?></span>
                        <span class="sl">Đã bán</span>
                    </div>
                    <div class="col stat-item">
                        <span class="sn text-info"><?= number_format($stats['da_cho_thue'] ?? 0) ?></span>
                        <span class="sl">Cho thuê</span>
                    </div>
                    <div class="col stat-item">
                        <span class="sn" style="color:var(--vip-gold)"><?= number_format($stats['tin_vip'] ?? 0) ?></span>
                        <span class="sl">VIP</span>
                    </div>
                    <div class="col stat-item">
                        <span class="sn"><?= number_format($stats['tong_luot_xem'] ?? 0) ?></span>
                        <span class="sl">Lượt xem</span>
                    </div>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div class="filter-bar mb-4" data-aos="fade-up" data-aos-delay="40">
                <form id="filterForm" method="GET" action="">
                    <input type="hidden" name="page" value="1">
                    <input type="hidden" name="type" id="typeInput" value="<?= htmlspecialchars($filters['type'] ?? '', ENT_QUOTES) ?>">

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <!-- Type Tabs -->
                        <div class="d-flex gap-1">
                            <button type="button" class="filter-tab-btn <?= empty($filters['type']) ? 'active' : '' ?>" data-type="">Tất cả</button>
                            <button type="button" class="filter-tab-btn <?= ($filters['type'] ?? '') === 'sale' ? 'active' : '' ?>" data-type="sale">
                                <i class="fas fa-home me-1"></i>Bán
                            </button>
                            <button type="button" class="filter-tab-btn <?= ($filters['type'] ?? '') === 'rent' ? 'active' : '' ?>" data-type="rent">
                                <i class="fas fa-key me-1"></i>Cho thuê
                            </button>
                        </div>
                        <!-- Sort -->
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Sắp xếp:</span>
                            <select name="sort" id="sortSelect" class="form-select form-select-sm" style="width:auto">
                                <option value="new"        <?= ($filters['sort'] ?? 'new') === 'new'        ? 'selected' : '' ?>>Mới nhất</option>
                                <option value="price_asc"  <?= ($filters['sort'] ?? '') === 'price_asc'     ? 'selected' : '' ?>>Giá tăng</option>
                                <option value="price_desc" <?= ($filters['sort'] ?? '') === 'price_desc'    ? 'selected' : '' ?>>Giá giảm</option>
                                <option value="area_desc"  <?= ($filters['sort'] ?? '') === 'area_desc'     ? 'selected' : '' ?>>Diện tích</option>
                                <option value="views"      <?= ($filters['sort'] ?? '') === 'views'         ? 'selected' : '' ?>>Lượt xem</option>
                                <option value="vip"        <?= ($filters['sort'] ?? '') === 'vip'           ? 'selected' : '' ?>>VIP trước</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3">
                            <label class="form-label small mb-1">Loại BĐS</label>
                            <select name="loai" class="form-select form-select-sm">
                                <option value="">Tất cả loại</option>
                                <option value="nha-pho"   <?= ($filters['loai'] ?? '') === 'nha-pho'   ? 'selected' : '' ?>>Nhà phố</option>
                                <option value="can-ho"    <?= ($filters['loai'] ?? '') === 'can-ho'    ? 'selected' : '' ?>>Căn hộ</option>
                                <option value="dat-nen"   <?= ($filters['loai'] ?? '') === 'dat-nen'   ? 'selected' : '' ?>>Đất nền</option>
                                <option value="biet-thu"  <?= ($filters['loai'] ?? '') === 'biet-thu'  ? 'selected' : '' ?>>Biệt thự</option>
                                <option value="van-phong" <?= ($filters['loai'] ?? '') === 'van-phong' ? 'selected' : '' ?>>Văn phòng</option>
                                <option value="mat-bang"  <?= ($filters['loai'] ?? '') === 'mat-bang'  ? 'selected' : '' ?>>Mặt bằng</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Giá tối thiểu</label>
                            <select name="gia_min" class="form-select form-select-sm">
                                <option value="">Từ giá</option>
                                <option value="500000000"  <?= ($filters['gia_min'] ?? '') == 500000000   ? 'selected' : '' ?>>500 triệu</option>
                                <option value="1000000000" <?= ($filters['gia_min'] ?? '') == 1000000000  ? 'selected' : '' ?>>1 tỷ</option>
                                <option value="3000000000" <?= ($filters['gia_min'] ?? '') == 3000000000  ? 'selected' : '' ?>>3 tỷ</option>
                                <option value="5000000000" <?= ($filters['gia_min'] ?? '') == 5000000000  ? 'selected' : '' ?>>5 tỷ</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Giá tối đa</label>
                            <select name="gia_max" class="form-select form-select-sm">
                                <option value="">Đến giá</option>
                                <option value="1000000000"  <?= ($filters['gia_max'] ?? '') == 1000000000  ? 'selected' : '' ?>>1 tỷ</option>
                                <option value="3000000000"  <?= ($filters['gia_max'] ?? '') == 3000000000  ? 'selected' : '' ?>>3 tỷ</option>
                                <option value="5000000000"  <?= ($filters['gia_max'] ?? '') == 5000000000  ? 'selected' : '' ?>>5 tỷ</option>
                                <option value="10000000000" <?= ($filters['gia_max'] ?? '') == 10000000000 ? 'selected' : '' ?>>10 tỷ</option>
                                <option value="20000000000" <?= ($filters['gia_max'] ?? '') == 20000000000 ? 'selected' : '' ?>>20 tỷ</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label small mb-1">Diện tích</label>
                            <select name="dt_min" class="form-select form-select-sm">
                                <option value="">Từ DT</option>
                                <option value="30"  <?= ($filters['dt_min'] ?? '') == 30  ? 'selected' : '' ?>>30m²</option>
                                <option value="50"  <?= ($filters['dt_min'] ?? '') == 50  ? 'selected' : '' ?>>50m²</option>
                                <option value="100" <?= ($filters['dt_min'] ?? '') == 100 ? 'selected' : '' ?>>100m²</option>
                                <option value="200" <?= ($filters['dt_min'] ?? '') == 200 ? 'selected' : '' ?>>200m²</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <div class="form-check mt-1" style="white-space:nowrap">
                                <input class="form-check-input" type="checkbox" name="vip_only" value="1"
                                       id="vipOnly" <?= !empty($filters['vip_only']) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="vipOnly">VIP</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-filter-apply">
                                    <i class="fas fa-search me-1"></i>Lọc
                                </button>
                                <a href="?page=1" class="btn btn-outline-secondary btn-sm rounded-3" title="Xóa bộ lọc">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- PROPERTY GRID -->
            <div id="propertyGrid">
                <?php if (empty($posts)): ?>
                <div class="text-center py-5 content-card" data-aos="fade-up">
                    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">Không có tin đăng nào</h5>
                    <p class="text-muted small">Thử điều chỉnh bộ lọc hoặc xem tất cả tin.</p>
                </div>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($posts as $i => $post):
                        $postImg = img_url($post->anh_thu_nho ?? '');
                        $vip     = (int)($post->active_vip ?? 0);
                    ?>
                    <div class="col-12 col-sm-6" data-aos="fade-up" data-aos-delay="<?= min($i * 40, 280) ?>">
                        <div class="prop-card h-100">
                            <div class="prop-card-img">
                                <a href="<?= URL_ROOT ?>/du-an/detail/<?= htmlspecialchars($post->duong_dan, ENT_QUOTES) ?>">
                                    <img src="<?= $postImg ?: 'https://placehold.co/400x195/e2e8f0/94a3b8?text=BDS' ?>"
                                         alt="<?= htmlspecialchars($post->tieu_de, ENT_QUOTES) ?>" loading="lazy">
                                </a>
                                <?php if ($vip > 0): ?>
                                <div class="badge-vip"><i class="fas fa-star me-1" style="font-size:.6rem"></i>VIP <?= $vip ?></div>
                                <?php endif; ?>
                                <div class="badge-cat"><?= htmlspecialchars($post->ten_danh_muc ?? '', ENT_QUOTES) ?></div>
                            </div>
                            <div class="p-3">
                                <div class="prop-price mb-1"><?= htmlspecialchars($post->gia ?? 'Thỏa thuận', ENT_QUOTES) ?></div>
                                <a href="<?= URL_ROOT ?>/du-an/detail/<?= htmlspecialchars($post->duong_dan, ENT_QUOTES) ?>"
                                   class="d-block text-dark fw-semibold text-decoration-none mb-2"
                                   style="font-size:.875rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4">
                                    <?= htmlspecialchars($post->tieu_de, ENT_QUOTES) ?>
                                </a>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted" style="font-size:.82rem">
                                        <i class="fas fa-ruler-combined me-1"></i><?= htmlspecialchars($post->dien_tich ?? '?', ENT_QUOTES) ?> m²
                                    </span>
                                    <span style="font-size:.73rem;color:#94a3b8">
                                        <i class="fas fa-eye me-1"></i><?= number_format($post->luot_xem ?? 0) ?>
                                    </span>
                                </div>
                                <p style="font-size:.78rem;color:#64748b;margin:0">
                                    <i class="fas fa-map-marker-alt me-1 text-danger" style="font-size:.68rem"></i>
                                    <?= htmlspecialchars($post->vi_tri ?? '', ENT_QUOTES) ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted" style="font-size:.73rem"><?= date('d/m/Y', strtotime($post->ngay_tao ?? 'now')) ?></small>
                                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= htmlspecialchars($post->duong_dan, ENT_QUOTES) ?>"
                                       class="btn btn-sm text-white"
                                       style="background:var(--ap);border-radius:8px;font-size:.73rem;padding:4px 12px">
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- PAGINATION -->
                <?php if ($pagination['totalPages'] > 1): ?>
                <nav class="mt-4" aria-label="Phân trang">
                    <ul class="pagination justify-content-center flex-wrap gap-1">
                        <?php if ($pagination['hasPrev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['prevPage'] ?>&<?= http_build_query(array_filter($filters)) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif;
                        $ps = max(1, $pagination['currentPage']-2);
                        $pe = min($pagination['totalPages'], $pagination['currentPage']+2);
                        if ($ps>1): ?><li class="page-item"><a class="page-link" href="?page=1&<?= http_build_query(array_filter($filters)) ?>">1</a></li>
                        <?php if ($ps>2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                        <?php endif;
                        for ($p=$ps; $p<=$pe; $p++): ?>
                        <li class="page-item <?= $p===$pagination['currentPage'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor;
                        if ($pe<$pagination['totalPages']):
                            if ($pe<$pagination['totalPages']-1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['totalPages'] ?>&<?= http_build_query(array_filter($filters)) ?>"><?= $pagination['totalPages'] ?></a>
                        </li>
                        <?php endif; ?>
                        <?php if ($pagination['hasNext']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $pagination['nextPage'] ?>&<?= http_build_query(array_filter($filters)) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <p class="text-center text-muted small mt-2">
                        Trang <?= $pagination['currentPage'] ?>/<?= $pagination['totalPages'] ?> · <?= number_format($pagination['total']) ?> tin
                    </p>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- REVIEWS -->
            <?php if (!empty($reviews)): ?>
            <div class="content-card mt-4" data-aos="fade-up">
                <div class="content-card-header">
                    <i class="fas fa-star"></i>
                    Đánh giá người đăng
                    <span class="badge ms-auto rounded-3" style="background:var(--ap)"><?= $ua->so_danh_gia ?? 0 ?></span>
                </div>
                <div class="p-4">
                    <!-- Summary -->
                    <div class="row align-items-center mb-4">
                        <div class="col-md-3 text-center">
                            <div style="font-size:3rem;font-weight:800;color:var(--ap-dark);line-height:1"><?= number_format($star,1) ?></div>
                            <div class="stars-display my-1">
                                <?php for ($i=1;$i<=5;$i++): echo $i<=$starFull ? '<i class="fas fa-star"></i>' : ($i==$starFull+1&&$starHalf ? '<i class="fas fa-star-half-alt"></i>' : '<i class="far fa-star"></i>'); endfor; ?>
                            </div>
                            <small class="text-muted"><?= number_format($ua->so_danh_gia ?? 0) ?> đánh giá</small>
                        </div>
                        <div class="col-md-9">
                            <?php foreach (array_reverse([1,2,3,4,5]) as $s):
                                $cnt = (int)($ratingDist[(string)$s] ?? 0);
                                $tot = max(1,(int)($ua->so_danh_gia ?? 1));
                                $pct = round($cnt/$tot*100); ?>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="small fw-semibold" style="width:36px"><?= $s ?>⭐</span>
                                <div class="rating-bar flex-grow-1"><div class="rating-bar-fill" style="width:<?= $pct ?>%"></div></div>
                                <span class="small text-muted" style="width:28px"><?= $cnt ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- List -->
                    <div id="reviewList" class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $rev): ?>
                        <div class="review-card">
                            <div class="d-flex align-items-start gap-3">
                                <img src="<?= !empty($rev->avatar_reviewer) ? URL_ROOT.'/uploads/avatars/'.htmlspecialchars($rev->avatar_reviewer,ENT_QUOTES) : 'https://ui-avatars.com/api/?name='.urlencode($rev->ten_reviewer??'U').'&size=38&background=e2e8f0&color=64748b' ?>"
                                     class="review-avatar" alt="">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong class="small"><?= htmlspecialchars($rev->ten_reviewer ?? 'Ẩn danh', ENT_QUOTES) ?></strong>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($rev->ngay_tao??'now')) ?></small>
                                    </div>
                                    <div class="stars-display my-1" style="font-size:.73rem">
                                        <?php for ($i=1;$i<=5;$i++): echo '<i class="'.($i<=$rev->so_sao?'fas':'far').' fa-star"></i>'; endfor; ?>
                                    </div>
                                    <?php if (!empty($rev->nhan_xet)): ?>
                                    <p class="mb-0 small text-muted"><?= htmlspecialchars($rev->nhan_xet, ENT_QUOTES) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Write review -->
                    <?php if ($currentUserId && $currentUserId !== $userId): ?>
                    <div class="mt-4 pt-4 border-top" id="reviewForm">
                        <h6 class="fw-bold mb-3">Viết đánh giá</h6>
                        <div class="mb-3">
                            <div class="d-flex gap-2 fs-4" id="starPicker">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                <i class="far fa-star" data-star="<?= $i ?>" style="cursor:pointer;color:var(--vip-gold);transition:transform .15s"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" id="reviewStar" value="0">
                            <small class="text-danger d-none" id="starError">Vui lòng chọn số sao.</small>
                        </div>
                        <div class="mb-3">
                            <textarea id="reviewText" class="form-control" rows="3"
                                      placeholder="Chia sẻ trải nghiệm làm việc với người đăng này..." maxlength="500"
                                      style="border-radius:10px"></textarea>
                            <small class="text-muted"><span id="reviewCharCount">0</span>/500</small>
                        </div>
                        <button type="button" id="submitReviewBtn"
                                class="btn text-white" style="background:var(--ap);border-radius:10px"
                                data-author-id="<?= $userId ?>">
                            <i class="fas fa-paper-plane me-2"></i>Gửi đánh giá
                        </button>
                    </div>
                    <?php elseif (!$currentUserId): ?>
                    <div class="mt-4 pt-4 border-top text-center text-muted small">
                        <a href="<?= URL_ROOT ?>/nguoi-dung/dang-nhap">Đăng nhập</a> để viết đánh giá.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===== SIDEBAR ===== -->
        <div class="col-lg-4">
            <div class="sidebar-contact" data-aos="fade-left">
                <!-- Mini profile -->
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <img src="<?= $avatar ?>" alt="" class="rounded-circle" width="52" height="52" style="object-fit:cover">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($ua->ten ?? '', ENT_QUOTES) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($loaiLabel, ENT_QUOTES) ?></small>
                    </div>
                </div>
                <!-- Contact buttons -->
                <div class="d-grid gap-2 mb-4">
                    <?php if (!empty($ua->dien_thoai)): ?>
                    <button class="btn btn-call phone-reveal-btn" data-phone="<?= htmlspecialchars($ua->dien_thoai, ENT_QUOTES) ?>" data-author="<?= $userId ?>">
                        <i class="fas fa-phone me-2"></i><span class="phone-text">Xem số điện thoại</span>
                    </button>
                    <?php endif; ?>
                    <?php if (!empty($ua->zalo)): ?>
                    <a href="https://zalo.me/<?= htmlspecialchars($ua->zalo, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="btn btn-zalo w-100">
                        <i class="fas fa-comment-dots me-2"></i>Nhắn Zalo
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($ua->hien_thi_email) && !empty($ua->email)): ?>
                    <a href="mailto:<?= htmlspecialchars($ua->email, ENT_QUOTES) ?>" class="btn btn-outline-secondary rounded-pill small">
                        <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($ua->email, ENT_QUOTES) ?>
                    </a>
                    <?php endif; ?>
                </div>
                <!-- Stats list -->
                <ul class="list-unstyled small text-muted mb-4">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Tổng tin đăng</span><strong class="text-dark"><?= number_format($ua->tong_tin_dang ?? 0) ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Người theo dõi</span><strong class="text-dark" id="sideFollowerCount"><?= number_format($ua->so_follower ?? 0) ?></strong>
                    </li>
                    <?php if ($star > 0): ?>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Đánh giá</span><strong class="text-dark"><?= number_format($star,1) ?>⭐ (<?= $ua->so_danh_gia ?? 0 ?>)</strong>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($ua->ty_le_phan_hoi)): ?>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>Tỷ lệ phản hồi</span><strong class="text-success"><?= $ua->ty_le_phan_hoi ?>%</strong>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($ua->gio_phan_hoi_tb)): ?>
                    <li class="d-flex justify-content-between py-2">
                        <span>Phản hồi trong</span><strong class="text-dark"><?= $ua->gio_phan_hoi_tb ?> giờ</strong>
                    </li>
                    <?php endif; ?>
                </ul>
                <!-- Share -->
                <p class="small fw-semibold mb-2 text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em">Chia sẻ</p>
                <div class="d-flex gap-2 flex-wrap mb-4">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener"
                       class="btn btn-sm rounded-3" style="background:#1877f2;color:white;font-size:.75rem">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://t.me/share/url?url=<?= urlencode($canonicalUrl) ?>&text=<?= urlencode($title) ?>" target="_blank" rel="noopener"
                       class="btn btn-sm rounded-3" style="background:#0088cc;color:white;font-size:.75rem">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-secondary rounded-3" id="copyProfileLink" data-url="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES) ?>">
                        <i class="fas fa-link"></i> Copy
                    </button>
                </div>
                <div class="pt-3 border-top text-center">
                    <small class="text-muted">
                        <i class="fas fa-flag me-1"></i>
                        <a href="mailto:support@timnhadat.site?subject=Báo+cáo+người+dùng+#<?= $userId ?>" class="text-muted">Báo cáo tài khoản</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.min.js"></script>
<script src="<?= URL_ROOT ?>/public/js/author-profile.js?v=<?= time() ?>"></script>
<script>
// Remove AOS disable override once JS loaded, then init
document.querySelectorAll('[data-aos]').forEach(el => {
    el.style.opacity = ''; el.style.transform = ''; el.style.transition = '';
});
if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 600, once: true, offset: 50 });
}
// Config
window.AUTHOR_CONFIG = {
    authorId:     <?= (int)$userId ?>,
    currentUserId:<?= (int)$currentUserId ?>,
    csrfToken:    <?= json_encode($csrfToken ?? '') ?>,
    followUrl:    '<?= URL_ROOT ?>/author/follow',
    unfollowUrl:  '<?= URL_ROOT ?>/author/unfollow',
    reviewUrl:    '<?= URL_ROOT ?>/author/review',
    siteRoot:     '<?= URL_ROOT ?>',
};
</script>
