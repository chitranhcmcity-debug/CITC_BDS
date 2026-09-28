<?php require_once '../app/views/layouts/header.php'; ?>

<?php require dirname(__DIR__, 4) . '/resources/views/trang-chu/components/home-banner.blade.php'; ?>

<?php if (false): ?>
<!-- Hero Section · Regenerative Living -->
<section class="terra-hero text-white position-relative overflow-hidden">
    <!-- Video được phục vụ nội bộ, không làm lộ tài nguyên hoặc phụ thuộc CDN -->
    <div class="terra-portal position-absolute" aria-hidden="true">
        <video id="terraHeroVideo" class="terra-video" autoplay muted loop playsinline preload="metadata"
            poster="https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=1920&q=85">
            <source src="<?= URL_ROOT ?>/public/videos/hero-background.mp4" type="video/mp4">
        </video>
    </div>

    <div class="terra-veil position-absolute w-100 h-100"></div>
    <div class="terra-grain position-absolute w-100 h-100"></div>

    <div class="container terra-hero__inner position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-xl-7 col-lg-8">
                <div class="terra-kicker animation-slide-up">
                    <span></span> Không gian sống tuyển chọn · Giá trị bền vững
                </div>
                <h1 class="terra-title animation-slide-up">
                    <span class="d-block">Kiến tạo</span>
                    <span class="d-block terra-title__soft">chốn an cư.</span>
                </h1>
                <p class="terra-lead animation-slide-up" style="animation-delay:.16s">Khám phá những bất động sản được
                    tuyển chọn kỹ lưỡng — nơi kiến trúc, thiên nhiên và giá trị đầu tư cùng tạo nên một phong cách sống
                    khác biệt.</p>

                <div class="d-flex flex-wrap gap-3 mt-4 animation-slide-up" style="animation-delay:.28s">
                    <a href="<?= URL_ROOT ?>/du-an" class="terra-btn terra-btn--primary">
                        <span>Khám phá bất động sản</span><i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
                <!-- ── Quick Navigation Pills ── -->
                <div class="terra-quick animation-slide-up" style="animation-delay:.4s">
                    <p class="terra-label"><i class="fa-solid fa-compass me-1"></i> Khám phá nhanh</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= URL_ROOT ?>/du-an?type=sale" class="hero-pill">
                            <i class="fa-solid fa-house me-1"></i>Nhà đất bán
                        </a>
                        <a href="<?= URL_ROOT ?>/du-an?type=rent" class="hero-pill">
                            <i class="fa-solid fa-key me-1"></i>Cho thuê
                        </a>
                        <a href="<?= URL_ROOT ?>/du-an/search?q=can+ho+chung+cu" class="hero-pill">
                            <i class="fa-solid fa-building me-1"></i>Căn hộ
                        </a>
                        <a href="<?= URL_ROOT ?>/du-an/search?q=nha+pho" class="hero-pill">
                            <i class="fa-solid fa-store me-1"></i>Nhà phố
                        </a>
                        <a href="<?= URL_ROOT ?>/du-an/search?q=biet+thu" class="hero-pill">
                            <i class="fa-solid fa-umbrella-beach me-1"></i>Biệt thự
                        </a>
                        <a href="<?= URL_ROOT ?>/tin-tuc" class="hero-pill hero-pill--accent">
                            <i class="fa-solid fa-newspaper me-1"></i>Tin tức BĐS
                        </a>
                    </div>
                </div>

                <!-- ── Live Stats Bar ── -->
                <div class="terra-stats d-flex flex-wrap animation-slide-up" style="animation-delay:.52s">
                    <div class="hero-stat">
                        <span class="hero-stat__num"><?= number_format($data['thongKe']['tongTinDang'] ?? 0) ?>+</span>
                        <span class="hero-stat__label">Tin đang đăng</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__num"><?= number_format($data['thongKe']['tinMuaBan'] ?? 0) ?>+</span>
                        <span class="hero-stat__label">Nhà đất bán</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__num"><?= number_format($data['thongKe']['tinChoThue'] ?? 0) ?>+</span>
                        <span class="hero-stat__label">Cho thuê</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__num"><?= number_format($data['thongKe']['tinMoiTuan'] ?? 0) ?>+</span>
                        <span class="hero-stat__label">Tin mới tuần này</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');
.terra-hero {
    min-height: 84vh;
    isolation: isolate;
    font-family: Inter, sans-serif;
    background:#293128;
}
.terra-hero__inner { z-index: 4; padding-top: 30px; padding-bottom: 26px; }
.min-vh-75 { min-height: 81vh; }
.terra-portal {
    z-index:1; inset:0; width:100%; height:100%;
    filter:saturate(1.12) contrast(1.01);
}
.terra-video { width:100%; height:100%; object-fit:cover; object-position:center 68%; filter:brightness(1.08) saturate(1.13); transform:scale(1.015); will-change:transform; }
.terra-veil { inset:0; z-index:2; background:linear-gradient(90deg,rgba(10,15,11,.64) 0%,rgba(10,15,11,.44) 34%,rgba(10,15,11,.12) 64%,rgba(10,15,11,.08) 100%),linear-gradient(0deg,rgba(5,9,6,.27),transparent 42%); }
.terra-grain { inset:0; z-index:3; pointer-events:none; opacity:.035; background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E"); }
.terra-kicker { display:inline-flex; align-items:center; gap:10px; padding:9px 15px; border:1px solid rgba(255,255,255,.13); background:rgba(255,255,255,.055); backdrop-filter:blur(18px); border-radius:999px; color:rgba(255,255,255,.74); font-size:.7rem; letter-spacing:.1em; text-transform:uppercase; }
.terra-kicker span { width:8px; height:8px; border-radius:50%; background:#75e7a1; box-shadow:0 0 18px rgba(117,231,161,.8); }
.terra-title { margin:18px 0 12px; color:#fff !important; font-family:'Cormorant Garamond',serif; font-size:clamp(3.4rem,6.6vw,6rem); line-height:.8; letter-spacing:-.055em; font-weight:700; text-shadow:0 5px 25px rgba(0,0,0,.58); }
.terra-title span { color:inherit !important; opacity:1 !important; }
.terra-title__soft { color:rgba(255,255,255,.94) !important; font-style:italic; }
.terra-lead { max-width:650px; margin:20px 0 0; font-size:clamp(.94rem,1.35vw,1.08rem); line-height:1.65; color:rgba(255,255,255,.72); }
.terra-btn { position:relative; overflow:hidden; display:inline-flex; align-items:center; gap:16px; padding:13px 22px 13px 24px; border-radius:999px; color:#fff; font-size:.82rem; font-weight:600; letter-spacing:.06em; text-decoration:none; border:1px solid rgba(255,255,255,.28); transition:.35s cubic-bezier(.16,1,.3,1); backdrop-filter:blur(20px) saturate(1.2); box-shadow:inset 0 1px 0 rgba(255,255,255,.13),0 10px 30px rgba(0,0,0,.16); }
.terra-btn:hover { color:#fff; transform:translateY(-3px); border-color:rgba(255,255,255,.5); box-shadow:inset 0 1px 0 rgba(255,255,255,.2),0 16px 38px rgba(0,0,0,.28); }
.terra-btn--primary { background:linear-gradient(135deg,rgba(63,76,58,.66),rgba(151,112,54,.42)); }
.terra-btn--primary i { display:grid; place-items:center; width:28px; height:28px; border-radius:50%; background:rgba(255,255,255,.13); transition:transform .3s ease; }
.terra-btn--primary:hover i { transform:translateX(3px); }
.terra-btn--glass { background:rgba(255,255,255,.07); }
.terra-quick { margin-top:22px; }
.terra-label { margin:0 0 10px; color:rgba(255,255,255,.45); font-size:.67rem; font-weight:600; letter-spacing:.15em; text-transform:uppercase; }
.hero-pill {
    display: inline-flex;
    align-items: center;
    padding: 7px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    background: rgba(255,255,255,.055);
    border: 1px solid rgba(255,255,255,.14);
    backdrop-filter: blur(16px);
    text-decoration: none;
    transition: all .22s ease;
    letter-spacing: 0.01em;
}
.hero-pill:hover {
    background: rgba(255,255,255,.92);
    color: #174b32;
    border-color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}
.hero-pill--accent {
    background: rgba(185,139,52,.68);
    border-color: rgba(244,197,105,.35);
}
.hero-pill--accent:hover {
    background: #b98732;
    color: #fff;
}

/* ── Stats bar ── */
.terra-stats { gap:0; margin-top:18px; border-top:1px solid rgba(255,255,255,.12); max-width:650px; }
.hero-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 11px 24px 0 0;
    margin-right:24px;
    background:transparent;
    border:0;
    border-radius:0;
    min-width:105px;
    align-items:flex-start;
    transition: transform .2s;
}
.hero-stat:hover { transform: translateY(-3px); }
.hero-stat__num {
    font-size: 1.4rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
}
.hero-stat__label {
    font-size: 0.73rem;
    color: rgba(255,255,255,.5);
    margin-top: 2px;
    text-align: center;
    font-weight: 500;
}
.ls-wide { letter-spacing: 0.08em; }
@media (prefers-reduced-motion: reduce) { .terra-video { transform:scale(1.015) !important; } }
@media (max-width: 991.98px) {
    .terra-hero { min-height:650px; }
    .terra-portal { inset:0; width:100%; height:100%; opacity:1; }
    .terra-video { object-position:62% 70%; filter:brightness(.98) saturate(1.1); }
    .terra-veil { background:linear-gradient(90deg,rgba(6,12,8,.69),rgba(6,12,8,.22)),linear-gradient(0deg,rgba(5,10,7,.4),transparent 64%); }
    .terra-title { font-size:clamp(3.7rem,14vw,6.2rem); }
}
@media (max-width: 575.98px) {
    .terra-hero { min-height:610px; }
    .terra-hero__inner { padding-top:24px; padding-bottom:20px; }
    .terra-kicker { font-size:.58rem; }
    .terra-title { line-height:.84; margin-top:22px; }
    .terra-lead { line-height:1.65; }
    .hero-stat { width:50%; min-width:0; margin:0; padding-right:12px; }
    .terra-stats { row-gap:10px; }
}
</style>

<!-- Bất động sản nổi bật -->
<?php endif; ?>
<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Bất động sản nổi bật</h3>
        <div class="row g-4">
            <?php if (empty($data['featuredProjects'])): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Chưa có bất động sản nổi bật nào được đăng.</p>
                </div>
            <?php else: ?>
                <?php foreach ($data['featuredProjects'] as $project): ?>
                    <div class="col-md-4">
                        <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>" class="text-decoration-none">
                            <div class="card property-card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                <div class="position-relative">
                                    <?php if ($project->active_vip > 0): ?>
                                        <span class="badge position-absolute top-0 start-0 m-2 px-3 py-1 z-index-1 vip-badge">
                                            <i class="fa-solid fa-crown me-1"></i> VIP <?= $project->active_vip ?>
                                        </span>
                                    <?php endif; ?>
                                    <img src="<?= img_url($project->anh_thu_nho ?? '') ?>"
                                        class="card-img-top object-fit-cover" height="220" alt="<?= htmlspecialchars($project->tieu_de) ?>">

                                    <img src="<?= !empty($project->anh_dai_dien) ? URL_ROOT . '/public/uploads/avatars/' . $project->anh_dai_dien : 'https://ui-avatars.com/api/?name=' . urlencode($project->ten_nguoi_dung ?? 'NguoiDung') . '&background=random' ?>"
                                        class="rounded-circle border border-2 border-white position-absolute top-0 end-0 m-2"
                                        width="45" height="45" alt="Avatar">
                                </div>
                                <div class="card-body">
                                    <div class="d-flex gap-3 mb-2">
                                        <?php if (!empty($project->gia)): ?>
                                            <h5 class="text-danger fw-bold mb-0"><?= $project->gia ?></h5>
                                        <?php endif; ?>
                                        <?php if (!empty($project->dien_tich)): ?>
                                            <h5 class="text-danger fw-bold mb-0"><?= $project->dien_tich ?> m²</h5>
                                        <?php endif; ?>
                                    </div>
                                    <p class="card-text fw-semibold mb-3 text-dark"
                                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= $project->tieu_de ?></p>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot"></i>
                                        <?= $project->vi_tri ?? 'Đang cập nhật' ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Bất động sản mới nhất -->
<section class="py-5 bg-white border-top">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Bất động sản mới nhất</h3>
                <p class="text-muted small mb-0">Những tin đăng mới nhất — cập nhật liên tục mỗi ngày</p>
            </div>
            <a href="<?= URL_ROOT ?>/du-an" class="btn-cta-main btn-cta-sm">
                <span class="btn-cta-shimmer"></span>
                <span class="btn-cta-text">Xem tất cả</span>
                <span class="btn-cta-icon-wrap"><i class="fa-solid fa-arrow-right"></i></span>
            </a>
        </div>

        <?php if (empty($data['latestProjects'])): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-regular fa-clock fa-3x mb-3 d-block opacity-25"></i>
                <p>Chưa có tin đăng nào.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($data['latestProjects'] as $project): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 latest-card">
                        <!-- Ảnh -->
                        <div class="position-relative overflow-hidden" style="height:200px;">
                            <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>">
                                <img src="<?= img_url($project->anh_thu_nho ?? '') ?>"
                                    class="w-100 h-100 object-fit-cover latest-img"
                                    alt="<?= htmlspecialchars($project->tieu_de) ?>">
                            </a>
                            <!-- Badges -->
                            <div class="position-absolute top-0 start-0 m-2 d-flex gap-1 flex-wrap">
                                <?php if ($project->active_vip > 0): ?>
                                    <span class="badge vip-badge"><i class="fa-solid fa-crown me-1"></i>VIP <?= $project->active_vip ?></span>
                                <?php endif; ?>
                                <span class="badge <?= strpos(strtolower($project->loai_bat_dong_san ?? ''), 'thuê') !== false ? 'bg-success' : 'bg-danger' ?>">
                                    <?= strpos(strtolower($project->loai_bat_dong_san ?? ''), 'thuê') !== false ? 'Cho thuê' : 'Bán' ?>
                                </span>
                            </div>
                            <!-- Ngày đăng -->
                            <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75 small">
                                <i class="fa-regular fa-clock me-1"></i><?= date('d/m/Y', strtotime($project->ngay_tao)) ?>
                            </span>
                        </div>
                        <!-- Nội dung -->
                        <div class="card-body d-flex flex-column p-3">
                            <!-- Giá & DT -->
                            <div class="d-flex gap-3 mb-2">
                                <?php if (!empty($project->gia)): ?>
                                    <span class="text-danger fw-bold"><i class="fa-solid fa-tag me-1 small"></i><?= htmlspecialchars($project->gia) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($project->dien_tich)): ?>
                                    <span class="text-muted fw-semibold small"><i class="fa-solid fa-vector-square me-1"></i><?= htmlspecialchars($project->dien_tich) ?> m²</span>
                                <?php endif; ?>
                            </div>
                            <!-- Tiêu đề -->
                            <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>"
                                class="text-dark text-decoration-none fw-semibold mb-2"
                                style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:44px;">
                                <?= htmlspecialchars($project->tieu_de) ?>
                            </a>
                            <!-- Địa chỉ -->
                            <p class="text-muted small mb-3 text-truncate">
                                <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                <?= htmlspecialchars($project->vi_tri ?? 'Đang cập nhật') ?>
                            </p>
                            <!-- Footer: avatar + SĐT -->
                            <div class="d-flex align-items-center justify-content-between mt-auto pt-2 border-top">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= !empty($project->anh_dai_dien)
                                        ? URL_ROOT . '/public/uploads/avatars/' . $project->anh_dai_dien
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($project->ten_nguoi_dung ?? 'User') . '&background=random&size=32' ?>"
                                        class="rounded-circle" width="28" height="28" alt="Avatar">
                                    <span class="small text-muted fw-medium"><?= htmlspecialchars($project->ten_nguoi_dung ?? 'Ẩn danh') ?></span>
                                </div>
                                <?php $phone = $project->dien_thoai ?? ''; ?>
                                <?php if ($phone): ?>
                                <a href="tel:<?= $phone ?>" class="btn btn-sm btn-outline-success rounded-pill px-3" style="font-size:0.78rem;" data-track-post="<?= (int)$project->id ?>" data-track-type="call">
                                    <i class="fa-solid fa-phone me-1"></i><?= $phone ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Xem thêm CTA -->
            <div class="text-center mt-5 cta-reveal">
                <a href="<?= URL_ROOT ?>/du-an" class="btn-cta-main">
                    <span class="btn-cta-shimmer"></span>
                    <span class="btn-cta-text">
                        &gt;&gt;&gt; Xem tất cả BDS &gt;&gt;&gt;
                    </span>
                    <span class="btn-cta-icon-wrap">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.latest-card { transition: transform .32s cubic-bezier(0.22, 1, 0.36, 1), box-shadow .32s ease, border-color .32s ease; }
.latest-card:hover { transform: translateY(-9px) !important; box-shadow: 0 22px 52px rgba(15,23,42,.16), 0 8px 18px rgba(220,53,69,.08) !important; border-color: rgba(220,53,69,.28) !important; }
.latest-img { transition: transform .42s ease, filter .42s ease; }
.latest-card:hover .latest-img { transform: scale(1.06); filter: saturate(1.12) contrast(1.04); }

/* ── CTA Button – Teal Luxury ── */
.btn-cta-main {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 10px 20px;
    background: transparent;
    color: #0dbfaa;
    font-weight: 800;
    font-size: 0.92rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    text-decoration: none;
    border: none;
    box-shadow: none;
    transition: color 0.3s ease, transform 0.3s ease;
}
.btn-cta-main:hover {
    background: transparent;
    color: #0a7a6e;
    transform: translateY(-2px);
    box-shadow: none;
}

/* Icon vòng tròn bên phải */
.btn-cta-icon-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px; height: 24px;
    border-radius: 50%;
    border: 1.5px solid #0dbfaa;
    flex-shrink: 0;
    color: #0dbfaa;
    transition: background 0.3s ease,
                border-color 0.3s ease,
                color 0.3s ease,
                transform 0.3s ease;
}
.btn-cta-main:hover .btn-cta-icon-wrap {
    background: #0dbfaa;
    border-color: #0dbfaa;
    color: #ffffff;
    transform: translateX(5px);
}
.btn-cta-icon-wrap i { font-size: 0.72rem; }


/* ── Ánh xanh ngọc chạy liên tục ── */
.btn-cta-shimmer {
    position: absolute;
    top: 0;
    left: -80%;
    width: 45%; height: 100%;
    background: linear-gradient(110deg,
        transparent   0%,
        rgba(45,212,191,0.18) 40%,   /* xanh ngọc teal nhạt */
        rgba(99,255,230,0.32) 50%,   /* đỉnh sáng hơn */
        rgba(45,212,191,0.18) 60%,
        transparent  100%);
    transform: skewX(-18deg);
    animation: teal-sweep 2.4s ease-in-out infinite;
    pointer-events: none;
}
@keyframes teal-sweep {
    0%   { left: -80%; }
    100% { left: 130%; }
}


/* Đường kẻ vàng đồng trang trí phía dưới nút */
.cta-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.cta-reveal::after {
    content: '';
    display: block;
    width: 40px;
    height: 1px;
    background: #0dbfaa;
    margin: 18px auto 0;
    opacity: 0;
    transform: scaleX(0);
    transition: opacity 0.5s ease 0.4s, transform 0.5s ease 0.4s;
}
.cta-reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}
.cta-reveal.is-visible::after {
    opacity: 1;
    transform: scaleX(1);
}

/* Biến thể nhỏ – dùng inline cạnh tiêu đề section */
.btn-cta-sm {
    padding: 6px 20px;
    font-size: 0.75rem;
    letter-spacing: 0.14em;
    gap: 10px;
}
.btn-cta-sm .btn-cta-icon-wrap {
    width: 18px; height: 18px;
}
.btn-cta-sm .btn-cta-icon-wrap i { font-size: 0.6rem; }

/* Hiệu ứng tương tác cho khối tin tức trang chủ */
.home-news-card {
    display: block;
    transition: transform .28s ease;
}
.home-news-media {
    transition: box-shadow .28s ease;
}
.home-news-media img {
    transition: transform .38s cubic-bezier(.2, .7, .2, 1), filter .38s ease;
}
.home-news-title {
    transition: color .22s ease;
}
@media (hover: hover) and (pointer: fine) {
    .home-news-card:hover {
        transform: translateY(-5px);
    }
    .home-news-card:hover .home-news-media {
        box-shadow: 0 14px 30px rgba(15, 23, 42, .16) !important;
    }
    .home-news-card:hover .home-news-media img {
        transform: scale(1.055);
        filter: saturate(1.08) contrast(1.03);
    }
    .home-news-card:hover .home-news-title {
        color: #dc3545;
    }
}
.home-news-card:focus-visible {
    outline: 3px solid rgba(220, 53, 69, .35);
    outline-offset: 5px;
    border-radius: .5rem;
}
@media (prefers-reduced-motion: reduce) {
    .home-news-card,
    .home-news-media,
    .home-news-media img,
    .home-news-title {
        transition: none;
    }
}

</style>

<!-- Tin tức bất động sản -->
<section class="py-5">

    <div class="container">
        <div class="d-flex align-items-center mb-4">
            <h3 class="fw-bold mb-0 me-3">Tin tức bất động sản</h3>
            <div class="text-muted small d-none d-md-block border-start ps-3">
                <a href="<?= URL_ROOT ?>/tin-tuc" class="text-dark text-decoration-none me-3">Xem tất cả tin tức</a>
            </div>
        </div>

        <?php if (empty($data['featuredNews']) && empty($data['latestNews'])): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-regular fa-newspaper fa-3x mb-3 d-block"></i>
                <p>Chưa có bài viết nào được đăng. <a href="<?= URL_ROOT ?>/admin/tin-tuc/create">Thêm bài viết đầu tiên</a>
                </p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Bài nổi bật bên trái -->
                <div class="col-lg-6">
                    <?php if (!empty($data['featuredNews'])):
                        $fn = $data['featuredNews']; ?>
                        <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= $fn->duong_dan ?>" class="home-news-card text-decoration-none text-dark">
                            <div class="home-news-media position-relative rounded-3 overflow-hidden mb-3 shadow-sm">
                                <img src="<?= img_url($fn->anh_thu_nho ?? '') ?>"
                                    class="w-100 object-fit-cover" height="320" alt="<?= htmlspecialchars($fn->tieu_de) ?>">
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2"><i
                                        class="fa-solid fa-star me-1"></i>Nổi bật</span>
                            </div>
                            <h4 class="home-news-title fw-bold"><?= htmlspecialchars($fn->tieu_de) ?></h4>
                            <?php if (!empty($fn->tom_tat)): ?>
                                <p class="text-muted small"
                                    style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                    <?= htmlspecialchars($fn->tom_tat) ?></p>
                            <?php endif; ?>
                            <small class="text-muted"><i
                                    class="fa-regular fa-clock me-1"></i><?= date('d/m/Y', strtotime($fn->ngay_tao)) ?></small>
                        </a>
                    <?php elseif (!empty($data['latestNews'])):
                        $fn = $data['latestNews'][0]; ?>
                        <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= $fn->duong_dan ?>" class="home-news-card text-decoration-none text-dark">
                            <div class="home-news-media rounded-3 overflow-hidden mb-3 shadow-sm">
                                <img src="<?= img_url($fn->anh_thu_nho ?? '') ?>"
                                    class="w-100 object-fit-cover" height="320"
                                    alt="<?= htmlspecialchars($fn->tieu_de) ?>">
                            </div>
                            <h4 class="home-news-title fw-bold"><?= htmlspecialchars($fn->tieu_de) ?></h4>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Danh sách bài nhỏ bên phải -->
                <div class="col-lg-6">
                    <div class="row g-3">
                        <?php
                        // Nếu đã có featured news thì dùng latestNews bình thường, lấy tối đa 4
                        $sideNews = $data['latestNews'] ?? [];
                        // Loại trừ bài featured khỏi sidebar
                        if (!empty($data['featuredNews'])) {
                            $featuredId = $data['featuredNews']->id;
                            $sideNews = array_filter($sideNews, fn($n) => $n->id != $featuredId);
                        }
                        $sideNews = array_slice(array_values($sideNews), 0, 4);

                        if (empty($sideNews)): ?>
                            <div class="col-12 text-muted small">Chưa có bài viết nào khác.</div>
                        <?php else: ?>
                            <?php foreach ($sideNews as $news): ?>
                                <div class="col-6">
                                    <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= $news->duong_dan ?>"
                                        class="home-news-card text-decoration-none text-dark">
                                        <div class="home-news-media rounded-3 overflow-hidden mb-2 shadow-sm">
                                            <img src="<?= img_url($news->anh_thu_nho ?? '') ?>"
                                                class="w-100 object-fit-cover" height="130"
                                                alt="<?= htmlspecialchars($news->tieu_de) ?>">
                                        </div>
                                        <h6 class="home-news-title small fw-semibold"
                                            style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                            <?= htmlspecialchars($news->tieu_de) ?></h6>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($news->ngay_tao)) ?></small>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= URL_ROOT ?>/tin-tuc" class="btn-cta-main">
                <span class="btn-cta-shimmer"></span>
                <span class="btn-cta-text">Xem tất cả tin tức</span>
                <span class="btn-cta-icon-wrap"><i class="fa-solid fa-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<!-- Dự án nổi bật -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">Dự án nổi bật</h3>
            <span class="text-muted small"><i class="fa-solid fa-chart-line me-1 text-danger"></i>Ưu tiên theo SEO &
                VIP</span>
        </div>
        <div class="row g-3 mb-4">
            <?php if (empty($data['topSeoProjects'])): ?>
                <div class="col-12 text-center py-5 text-muted">Chưa có dự án nổi bật nào.</div>
            <?php else: ?>
                <?php foreach ($data['topSeoProjects'] as $project): ?>
                    <div class="col-md-3 col-6">
                        <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>" class="text-decoration-none">
                            <div
                                class="card project-card border-0 rounded-4 overflow-hidden text-white position-relative shadow-sm hover-zoom">
                                <img src="<?= img_url($project->anh_thu_nho ?? '') ?>"
                                    class="card-img object-fit-cover" height="250"
                                    alt="<?= htmlspecialchars($project->tieu_de) ?>">
                                <div class="card-img-overlay d-flex flex-column justify-content-between p-0">
                                    <!-- Badges top -->
                                    <div class="p-2 d-flex gap-1">
                                        <?php if ($project->active_vip > 0): ?>
                                            <span class="badge vip-badge"><i class="fa-solid fa-crown me-1"></i>VIP
                                                <?= $project->active_vip ?></span>
                                        <?php endif; ?>
                                        <?php if ($project->noi_bat): ?>
                                            <span class="badge bg-danger"><i class="fa-solid fa-star me-1"></i>Nổi bật</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Title bottom -->
                                    <div class="p-3 w-100"
                                        style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                                        <h6 class="mb-1 text-white fw-bold"
                                            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= htmlspecialchars($project->tieu_de) ?></h6>
                                        <small class="text-white-50"><i
                                                class="fa-solid fa-eye me-1"></i><?= number_format($project->luot_xem) ?> lượt
                                            xem</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <a href="<?= URL_ROOT ?>/du-an" class="btn-cta-main">
                <span class="btn-cta-shimmer"></span>
                <span class="btn-cta-text">Xem tất cả</span>
                <span class="btn-cta-icon-wrap"><i class="fa-solid fa-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>


<!-- Link Directory -->
<section class="py-5 bg-white border-top">
    <div class="container pb-4">

        <?php
        // Cac nhom danh muc duong link (SEO directory)
        $muaBan = [
            'Mua bán căn hộ chung cư'      => 'sale&loai_bds=can-ho-chung-cu',
            'Mua bán nhà biệt thự liền kề'  => 'sale&loai_bds=biet-thu-lien-ke',
            'Mua bán nhà mặt phố'           => 'sale&loai_bds=nha-mat-pho',
            'Mua bán nhà riêng'             => 'sale&loai_bds=nha-rieng',
            'Mua bán trang trại khu nghỉ dưỡng' => 'sale&loai_bds=nghi-duong',
            'Mua bán kho nhà xưởng'         => 'sale&loai_bds=kho-xuong',
            'Mua bán đất nền dự án'         => 'sale&loai_bds=dat-nen',
            'Mua bán đất'                   => 'sale&loai_bds=dat',
        ];
        $choThue = [
            'Cho thuê căn hộ chung cư'      => 'rent&loai_bds=can-ho-chung-cu',
            'Cho thuê nhà riêng'             => 'rent&loai_bds=nha-rieng',
            'Cho thuê nhà mặt phố'           => 'rent&loai_bds=nha-mat-pho',
            'Cho thuê nhà trọ, phòng trọ'    => 'rent&loai_bds=phong-tro',
        ];
        ?>

        <!-- Mua bán -->
        <h5 class="fw-bold mb-3">Mua bán nhà đất</h5>
        <div class="row text-primary small mb-4 gy-2">
            <?php foreach ($muaBan as $label => $query): ?>
            <div class="col-md-3">
                <a href="<?= URL_ROOT ?>/du-an/search?type=<?= urlencode(strtok($query, '&')) ?>&q=<?= urlencode($label) ?>"
                   class="text-decoration-none text-primary">
                    <?= $label ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Cho thuê -->
        <h5 class="fw-bold mb-3">Cho thuê nhà đất</h5>
        <div class="row text-primary small mb-4 gy-2">
            <?php foreach ($choThue as $label => $query): ?>
            <div class="col-md-3">
                <a href="<?= URL_ROOT ?>/du-an/search?type=<?= urlencode(strtok($query, '&')) ?>&q=<?= urlencode($label) ?>"
                   class="text-decoration-none text-primary">
                    <?= $label ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Theo Tỉnh/TP – dùng cityCounts đã được tính sẵn từ Controller (DuAnController / TrangChuController) -->
        <h5 class="fw-bold mb-3">Mua bán nhà đất theo Tỉnh/TP</h5>
        <div class="row text-primary small mb-2 gy-2">
            <?php
            $cities = [
                'Hà Nội', 'Hồ Chí Minh', 'Hòa Bình', 'Đà Nẵng',
                'Hải Phòng', 'Bình Dương', 'Khánh Hòa', 'Tuyên Quang',
                'Điện Biên', 'Vĩnh Phúc', 'Bắc Ninh', 'Đồng Nai',
            ];
            // Dung data duoc tinh san tu Controller (tranh query DB trong View)
            $cityCounts = $data['thongKe']['cityCounts'] ?? [];

            foreach ($cities as $city):
                $count = $cityCounts[$city] ?? 0;
            ?>
            <div class="col-lg-2 col-md-3 col-6">
                <a href="<?= URL_ROOT ?>/du-an/search?q=<?= urlencode($city) ?>"
                   class="text-decoration-none text-primary fw-medium">
                    <?= $city ?> <span class="text-muted">(<?= $count ?>)</span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4 pt-3 border-top small text-muted">
            Đăng tin bất động sản miễn phí - Trang đăng tin nhà đất miễn phí - Rao vặt nhà đất - Đăng tin bán nhà
        </div>
    </div>
</section>


<?php require_once '../app/views/layouts/footer.php'; ?>

<script>
// Parallax nhẹ giống mẫu gốc; dừng khi người dùng bật chế độ giảm chuyển động.
(function () {
    const video = document.getElementById('terraHeroVideo');
    if (!video || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    let frame = 0;
    const update = function () {
        const progress = Math.min(Math.max(window.scrollY / (window.innerHeight * 1.1), 0), 1);
        video.style.transform = 'scale(' + (1.015 + progress * .07) + ') translateY(' + (progress * 8) + 'px)';
        frame = 0;
    };
    window.addEventListener('scroll', function () {
        if (!frame) frame = window.requestAnimationFrame(update);
    }, { passive: true });
    window.addEventListener('resize', update);
    video.play().catch(function () { /* Trình duyệt sẽ hiển thị poster nếu chặn autoplay. */ });
    update();
})();

// Scroll reveal: kích hoạt .is-visible khi phần tử .cta-reveal vào viewport
(function () {
    const targets = document.querySelectorAll('.cta-reveal');
    if (!targets.length) return;

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // chỉ chạy 1 lần
            }
        });
    }, { threshold: 0.25 });

    targets.forEach(function (el) { observer.observe(el); });
})();
</script>
