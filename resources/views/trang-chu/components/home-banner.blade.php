<?php
$homeStats = $data['thongKe'] ?? [];
$bannerImages = $data['bannerImages'] ?? [];
$poster = !empty($bannerImages) ? img_url($bannerImages[0]->anh_thu_nho ?? '') : '';
$videoPath = function_exists('public_path')
    ? public_path('videos/hero-background.mp4')
    : dirname(__DIR__, 4) . '/public/videos/hero-background.mp4';
$videoVersion = is_file($videoPath) ? (string) filemtime($videoPath) : '1';
?>

<section class="home-banner" aria-labelledby="home-banner-title">
    <div class="home-banner__glow home-banner__glow--left"></div>
    <div class="home-banner__glow home-banner__glow--right"></div>

    <div class="home-banner__grid">
        <article class="home-banner__metric home-banner__metric--one">
            <span class="home-banner__metric-label">Nguồn cung toàn hệ thống</span>
            <strong><?= number_format($homeStats['tongTinDang'] ?? 0) ?>+</strong>
            <span>Tin đăng đang kết nối người mua với không gian phù hợp.</span>
        </article>
        <article class="home-banner__metric home-banner__metric--two">
            <span class="home-banner__metric-label">Bất động sản bán</span>
            <strong><?= number_format($homeStats['tinMuaBan'] ?? 0) ?>+</strong>
            <span>Nhà phố, căn hộ và biệt thự được cập nhật liên tục.</span>
        </article>
        <article class="home-banner__metric home-banner__metric--three">
            <span class="home-banner__metric-label">Bất động sản cho thuê</span>
            <strong><?= number_format($homeStats['tinChoThue'] ?? 0) ?>+</strong>
            <span>Lựa chọn linh hoạt cho nhu cầu ở và kinh doanh.</span>
        </article>
        <article class="home-banner__metric home-banner__metric--four">
            <span class="home-banner__metric-label">Cập nhật tuần này</span>
            <strong><?= number_format($homeStats['tinMoiTuan'] ?? 0) ?>+</strong>
            <span>Nguồn tin mới giúp bạn không bỏ lỡ cơ hội tốt.</span>
        </article>

        <div class="home-banner__core">
            <div class="home-banner__media">
                <video id="homeBannerVideo" autoplay muted loop playsinline preload="metadata"
                    <?php if ($poster !== ''): ?>poster="<?= htmlspecialchars($poster, ENT_QUOTES, 'UTF-8') ?>"<?php endif; ?>>
                    <source src="<?= URL_ROOT ?>/public/videos/hero-background.mp4?v=<?= $videoVersion ?>" type="video/mp4">
                </video>
                <div class="home-banner__veil"></div>
                <div class="home-banner__frame"></div>

                <div class="home-banner__content">
                    <span class="home-banner__eyebrow">Không gian sống · Giá trị bền vững</span>
                    <h1 id="home-banner-title">Tìm nơi<br>bạn thuộc về.</h1>
                    <p>Khám phá bất động sản được chọn lọc cho một chuẩn sống hiện đại, kết nối đúng nhu cầu và đúng thời điểm.</p>

                    <nav class="home-banner__chips" aria-label="Khám phá nhanh">
                        <a href="<?= URL_ROOT ?>/du-an?type=sale"><i class="fa-solid fa-house"></i> Nhà đất bán</a>
                        <a href="<?= URL_ROOT ?>/du-an?type=rent"><i class="fa-solid fa-key"></i> Cho thuê</a>
                        <a href="<?= URL_ROOT ?>/du-an/search?q=can+ho+chung+cu"><i class="fa-solid fa-building"></i> Căn hộ</a>
                    </nav>

                    <div class="home-banner__actions">
                        <a class="home-banner__primary" href="<?= URL_ROOT ?>/du-an">
                            Khám phá bất động sản <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <a class="home-banner__secondary" href="<?= URL_ROOT ?>/tin-tuc">Góc thị trường</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.home-banner {
    --banner-amber: #c9954e;
    position: relative;
    min-height: min(900px, calc(100svh - 76px));
    overflow: hidden;
    padding: 28px 14px;
    color: #fff;
    background: radial-gradient(circle at 14% 14%, rgba(255,255,255,.94), transparent 28%), radial-gradient(circle at 88% 18%, rgba(214,174,112,.32), transparent 27%), linear-gradient(145deg, #f4efe7 0%, #dfd4c5 52%, #cbbba8 100%);
    isolation: isolate;
}
.home-banner::before {
    content: ""; position: absolute; inset: 0; z-index: -1; opacity: .28;
    background-image: linear-gradient(rgba(91,70,45,.09) 1px, transparent 1px), linear-gradient(90deg, rgba(91,70,45,.09) 1px, transparent 1px);
    background-size: 120px 120px;
    mask-image: radial-gradient(circle at center, #000 20%, transparent 82%);
}
.home-banner__grid { position: relative; width: min(1540px, 100%); min-height: min(844px, calc(100svh - 132px)); margin: 0 auto; display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); align-items: center; }
.home-banner__core { grid-column: 3 / span 8; position: relative; z-index: 2; padding: 18px; border: 1px solid rgba(255,255,255,.78); border-radius: 38px; background: linear-gradient(145deg, rgba(255,255,255,.66), rgba(205,165,108,.22)); box-shadow: 0 32px 90px rgba(81,57,30,.24), inset 0 1px 0 rgba(255,255,255,.9); }
.home-banner__media { position: relative; min-height: clamp(560px, 70vh, 760px); overflow: hidden; border: 1px solid rgba(255,202,139,.22); border-radius: 30px; background: #171719; }
.home-banner__media video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transform: scale(1.04); filter: saturate(1.1) contrast(1.03) brightness(1.08); will-change: transform; }
.home-banner__veil { position: absolute; inset: 0; background: radial-gradient(circle at 50% 48%, transparent, rgba(0,0,0,.1) 58%, rgba(0,0,0,.38) 100%), linear-gradient(180deg, rgba(0,0,0,.02), rgba(0,0,0,.12)); }
.home-banner__frame { position: absolute; inset: 0; pointer-events: none; border-radius: inherit; box-shadow: inset 0 0 0 1px rgba(255,226,185,.45), inset 0 0 76px rgba(217,166,96,.17); }
.home-banner__content { position: absolute; z-index: 2; top: 50%; left: 50%; width: min(850px, 94%); padding: 24px; text-align: center; transform: translate(-50%, -50%); text-shadow: 0 8px 28px rgba(0,0,0,.72); }
.home-banner__eyebrow { display: block; margin-bottom: 18px; color: rgba(255,224,185,.92); font-size: 10px; font-weight: 700; letter-spacing: .28em; text-transform: uppercase; }
.home-banner__content h1 { margin: 0; color: #fff; font-family: Georgia, "Times New Roman", serif; font-size: clamp(4rem, 7.4vw, 7.6rem); font-weight: 600; line-height: .82; letter-spacing: -.07em; }
.home-banner__content p { max-width: 670px; margin: 24px auto 0; color: rgba(255,255,255,.88); font-size: clamp(.95rem, 1.4vw, 1.08rem); line-height: 1.75; }
.home-banner__chips, .home-banner__actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
.home-banner__chips { margin-top: 22px; }
.home-banner__chips a { padding: 9px 13px; border: 1px solid rgba(255,255,255,.15); border-radius: 999px; color: rgba(255,255,255,.86); background: rgba(255,255,255,.09); backdrop-filter: blur(12px); font-size: .76rem; font-weight: 600; text-decoration: none; transition: border-color .25s ease, background .25s ease, transform .25s ease; }
.home-banner__chips a:hover { color: #fff; border-color: rgba(255,209,151,.55); background: rgba(228,172,103,.22); transform: translateY(-2px); }
.home-banner__actions { margin-top: 24px; }
.home-banner__actions > a { min-height: 52px; display: inline-flex; align-items: center; justify-content: center; gap: 11px; padding: 0 21px; border-radius: 14px; font-size: .7rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; text-decoration: none; transition: transform .25s ease, box-shadow .25s ease, background .25s ease; }
.home-banner__primary { color: #21160c; border: 1px solid rgba(255,255,255,.26); background: linear-gradient(180deg, #f0c183, #c98743); box-shadow: inset 0 1px rgba(255,255,255,.45), 0 18px 38px rgba(0,0,0,.3); }
.home-banner__primary:hover { color: #21160c; transform: translateY(-3px); box-shadow: 0 24px 45px rgba(0,0,0,.4); }
.home-banner__secondary { color: #fff; border: 1px solid rgba(255,255,255,.18); background: rgba(20,20,20,.4); backdrop-filter: blur(15px); }
.home-banner__secondary:hover { color: #fff; background: rgba(255,255,255,.12); transform: translateY(-3px); }
.home-banner__metric { position: absolute; z-index: 4; width: clamp(210px, 17.5vw, 285px); padding: 15px 17px; border: 1px solid rgba(255,255,255,.82); border-radius: 5px; color: rgba(55,43,29,.72); background: rgba(252,249,244,.78); box-shadow: 0 20px 48px rgba(85,59,31,.16), inset 0 1px 0 rgba(255,255,255,.9); backdrop-filter: blur(22px) saturate(1.15); }
.home-banner__metric strong { display: block; margin: 8px 0 6px; color: #2e2419; font-family: Georgia, "Times New Roman", serif; font-size: clamp(2rem, 3vw, 3rem); font-weight: 500; line-height: 1; letter-spacing: -.05em; }
.home-banner__metric > span:last-child { display: block; font-size: .72rem; line-height: 1.55; }
.home-banner__metric-label { display: block; color: #98703d; font-size: .6rem; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; }
.home-banner__metric--one { top: 14%; left: 0; }
.home-banner__metric--two { top: 22%; right: 0; }
.home-banner__metric--three { bottom: 14%; left: 0; }
.home-banner__metric--four { right: 0; bottom: 16%; }
.home-banner__glow { position: absolute; top: 50%; width: 24%; height: 48%; pointer-events: none; transform: translateY(-50%); filter: blur(28px); }
.home-banner__glow--left { left: -8%; background: radial-gradient(circle, rgba(255,255,255,.72), transparent 68%); }
.home-banner__glow--right { right: -8%; background: radial-gradient(circle, rgba(198,146,77,.25), transparent 68%); }
@media (max-width: 1199.98px) {
    .home-banner { min-height: auto; padding: 24px 14px 32px; }
    .home-banner__grid { min-height: auto; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .home-banner__core { grid-column: 1 / -1; grid-row: 1; width: 100%; }
    .home-banner__metric { position: static; width: auto; }
    .home-banner__metric--one { grid-column: 1; grid-row: 2; }
    .home-banner__metric--two { grid-column: 2; grid-row: 2; }
    .home-banner__metric--three { grid-column: 1; grid-row: 3; }
    .home-banner__metric--four { grid-column: 2; grid-row: 3; }
}
@media (max-width: 767.98px) {
    .home-banner { padding: 10px 9px 22px; }
    .home-banner__core { padding: 7px; border-radius: 25px; }
    .home-banner__media { min-height: 610px; border-radius: 20px; }
    .home-banner__content { width: calc(100% - 18px); padding: 16px 10px; }
    .home-banner__content h1 { font-size: clamp(3rem, 12.5vw, 4rem); line-height: .9; }
    .home-banner__content p { font-size: .9rem; line-height: 1.6; }
    .home-banner__eyebrow { letter-spacing: .18em; }
    .home-banner__chips a { padding: 8px 11px; font-size: .7rem; }
    .home-banner__actions > a { width: 100%; min-height: 48px; }
    .home-banner__metric { padding: 13px; }
    .home-banner__metric strong { font-size: 2rem; }
    .home-banner__metric > span:last-child { display: none; }
}
@media (prefers-reduced-motion: reduce) {
    .home-banner__media video { transform: none !important; }
    .home-banner__chips a, .home-banner__actions > a { transition: none; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const banner = document.querySelector('.home-banner');
    const video = document.getElementById('homeBannerVideo');
    if (!banner || !video || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    let scheduled = false;
    const updateVideo = function () {
        const rect = banner.getBoundingClientRect();
        const progress = Math.max(0, Math.min(1, -rect.top / Math.max(rect.height, 1)));
        video.style.transform = `scale(${1.04 + progress * .09}) translateY(${progress * 12}px)`;
        scheduled = false;
    };
    window.addEventListener('scroll', function () {
        if (scheduled) return;
        scheduled = true;
        window.requestAnimationFrame(updateVideo);
    }, { passive: true });
});
</script>
