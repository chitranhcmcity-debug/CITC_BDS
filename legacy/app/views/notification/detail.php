<?php require_once '../app/views/layouts/header.php'; ?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/notifications.css?v=<?= filemtime(APP_ROOT . '/public/css/notifications.css') ?>">

<div class="container py-4 notifications-wrapper">
    <div class="row">
        <!-- Sidebar thành viên -->
        <?php require '../app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Nội dung chính -->
        <main class="col-lg-9 col-md-8 col-12">
            <!-- Header quay lại -->
            <section class="d-flex align-items-center mb-4 gap-3 bg-white p-3 rounded-3 shadow-sm">
                <a href="<?= URL_ROOT ?>/nguoi-dung/notifications" class="btn btn-outline-secondary btn-sm rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="h6 fw-bold mb-0 text-dark">Chi tiết thông báo</h2>
                </div>
            </section>

            <!-- Card nội dung chi tiết -->
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-body p-4 text-start">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-<?= (int)$notification->is_read === 1 ? 'secondary' : 'primary' ?>">
                            <?= (int)$notification->is_read === 1 ? 'Đã đọc' : 'Chưa đọc' ?>
                        </span>
                        <span class="text-muted small">
                            <i class="fa-regular fa-clock me-1"></i> <?= date('H:i d/m/Y', strtotime($notification->created_at)) ?>
                        </span>
                    </div>

                    <h4 class="fw-bold text-dark mb-3"><?= htmlspecialchars($notification->title) ?></h4>
                    
                    <div class="text-secondary mb-4" style="line-height: 1.6; font-size: 0.98rem; white-space: pre-wrap;">
                        <?= htmlspecialchars($notification->content) ?>
                    </div>

                    <?php if (!empty($notification->url)): ?>
                        <hr class="my-4 text-muted opacity-25">
                        <div class="text-center">
                            <a href="<?= htmlspecialchars($notification->url) ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Đi tới liên kết
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
