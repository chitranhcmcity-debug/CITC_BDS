<?php
$layout = $data['layout'] ?? [];
$siteSettings = $layout['settings'] ?? [];
?>
<footer class="bg-dark text-white pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <a href="<?= URL_ROOT ?>/" class="fw-bold mb-4 text-white d-flex align-items-center text-decoration-none">
                    <img src="<?= URL_ROOT ?>/public/images/favicon.png?v=2" alt="Logo" width="45" height="45" class="me-2" style="object-fit: contain; filter: brightness(0) invert(1);">
                    <?= htmlspecialchars((string)($siteSettings['site_name'] ?? SITE_NAME)) ?>
                </a>
                <p class="text-white-50">Nền tảng hàng đầu để tìm kiếm ngôi nhà mơ ước và các cơ hội đầu tư bất động sản tốt nhất với quy trình minh bạch và sự hỗ trợ chuyên nghiệp.</p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="fw-bold mb-4 text-white">Liên kết nhanh</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= URL_ROOT ?>/" class="text-white-50 text-decoration-none hover-white">Trang chủ</a></li>
                    <li class="mb-2"><a href="<?= URL_ROOT ?>/du-an" class="text-white-50 text-decoration-none hover-white">Dự án</a></li>
                    <li class="mb-2"><a href="<?= URL_ROOT ?>/tin-tuc" class="text-white-50 text-decoration-none hover-white">Tin tức</a></li>
                    <li class="mb-2"><a href="<?= URL_ROOT ?>/contact" class="text-white-50 text-decoration-none hover-white">Liên hệ</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4 text-white">Thông tin liên hệ</h5>
                <ul class="list-unstyled text-white-50">
                    <li class="mb-3"><i class="fa-solid fa-location-dot text-primary me-2"></i> <?= htmlspecialchars((string)($siteSettings['address'] ?? '')) ?></li>
                    <li class="mb-3"><i class="fa-solid fa-phone text-primary me-2"></i> <?= htmlspecialchars((string)($siteSettings['hotline'] ?? '')) ?></li>
                    <li class="mb-3"><i class="fa-solid fa-envelope text-primary me-2"></i> <?= htmlspecialchars((string)($siteSettings['email'] ?? '')) ?></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="fw-bold mb-4 text-white">Đăng ký nhận tin</h5>
                <p class="text-white-50">Đăng ký để nhận những thông tin cập nhật mới nhất.</p>
                <a class="btn btn-primary" href="mailto:<?= htmlspecialchars((string)($siteSettings['email'] ?? '')) ?>">
                    <i class="fa-solid fa-envelope me-1"></i> Liên hệ nhận tin
                </a>
            </div>
        </div>
        <hr class="mt-4 mb-3 border-secondary">
        <div class="text-center text-white-50 small">
            &copy; <?= date('Y') ?> <?= htmlspecialchars((string)($siteSettings['site_name'] ?? SITE_NAME)) ?>. Đã đăng ký bản quyền.
        </div>
    </div>
</footer>

<div class="live-chat-widget" data-live-chat-widget>
    <div class="live-chat-panel">
        <div class="live-chat-header">
            <div>
                <div class="live-chat-title">Live Chat</div>
                <div class="live-chat-subtitle" data-chat-subtitle>Nhân viên sẽ phản hồi sớm</div>
            </div>
            <button type="button" class="live-chat-close" data-chat-close aria-label="Đóng chat">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="live-chat-messages" data-chat-messages>
            <div class="live-chat-empty">Bấm mở chat để bắt đầu trao đổi với tư vấn viên.</div>
        </div>
        <form class="live-chat-form" data-chat-form>
            <input type="text" class="live-chat-input" data-chat-input maxlength="2000" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button type="submit" class="live-chat-send" aria-label="Gửi tin nhắn">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>
    </div>
    <button type="button" class="live-chat-toggle" data-chat-toggle aria-label="Mở live chat" data-tooltip="Live Chat">
        <i class="fa-regular fa-comments"></i>
        <span class="live-chat-badge" data-chat-badge>0</span>
    </button>
</div>

<script>
window.LiveChatConfig = {
    bootstrapUrl: '<?= URL_ROOT ?>/chat/bootstrap',
    statusUrl: '<?= URL_ROOT ?>/chat/status',
    sendUrl: '<?= URL_ROOT ?>/chat/send',
    pollUrl: '<?= URL_ROOT ?>/chat/poll',
    csrf: '<?= Csrf::token() ?>'
};
window.AnalyticsEventConfig = {
    endpoint: '<?= URL_ROOT ?>/analytics',
    csrf: '<?= Csrf::token() ?>'
};
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= URL_ROOT ?>/public/js/live-chat.js?v=<?= filemtime(APP_ROOT . '/public/js/live-chat.js') ?>"></script>
<script src="<?= URL_ROOT ?>/public/js/analytics-events.js?v=<?= filemtime(APP_ROOT . '/public/js/analytics-events.js') ?>"></script>
<!-- Custom JS -->
<script src="<?= URL_ROOT ?>/public/js/main.js?v=<?= filemtime(APP_ROOT . '/public/js/main.js') ?>"></script>
</body>
</html>
