<?php require_once '../app/views/layouts/header.php'; ?>
<?php
$a = $analytics;
$post = $a['post'];
$s = $a['summary'];
$r = $a['range'];
?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/analytics.css?v=<?= filemtime(APP_ROOT . '/public/css/analytics.css') ?>">

<div class="container py-4 analytics-shell">
    <div class="row">
        <!-- Sidebar thành viên -->
        <?php require '../app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Nội dung chính -->
        <main class="col-lg-9 col-md-8 col-12">
            <!-- Header tiêu đề và nút quay lại -->
            <section class="analytics-header p-3 mb-4 shadow-sm rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?= URL_ROOT ?>/nguoi-dung/analytics" class="btn btn-outline-secondary btn-sm rounded-circle p-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold mb-1">Báo cáo chi tiết</span>
                        <h2 class="h5 fw-bold mb-0 text-dark">Hiệu quả của Tin đăng</h2>
                    </div>
                </div>

                <a href="<?= URL_ROOT ?>/du-an/detail/<?= htmlspecialchars($post->duong_dan) ?>" class="btn btn-primary btn-sm fw-bold" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Xem tin đăng
                </a>
            </section>

            <!-- Card tóm tắt thông tin bài đăng -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-center">
                        <img class="rounded shadow-sm" style="width: 120px; height: 86px; object-fit: cover;" 
                             src="<?= htmlspecialchars(img_url((string)$post->anh_thu_nho)) ?>" alt="Listing Thumbnail">
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2 text-dark"><?= htmlspecialchars($post->tieu_de) ?></h5>
                            <div class="d-flex flex-wrap gap-2 align-items-center small">
                                <span class="badge bg-<?= $post->trang_thai === 'xuat_ban' ? 'success' : 'secondary' ?>">
                                    <?= $post->trang_thai === 'xuat_ban' ? 'Đã xuất bản' : 'Ẩn/Nháp' ?>
                                </span>
                                <span class="badge bg-<?= (int)$post->goi_vip > 0 ? 'warning text-dark' : 'light text-dark border' ?>">
                                    <?= (int)$post->goi_vip > 0 ? 'VIP ' . $post->goi_vip : 'Tin thường' ?>
                                </span>
                                <span class="text-muted">
                                    Đăng ngày: <strong><?= date('d/m/Y', strtotime($post->ngay_tao)) ?></strong>
                                </span>
                                <?php if ($post->ngay_het_han_vip): ?>
                                    <span class="text-muted">|</span>
                                    <span class="text-muted">
                                        Hết hạn VIP: <strong><?= date('d/m/Y', strtotime($post->ngay_het_han_vip)) ?></strong>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bộ lọc thời gian cho riêng bài viết này -->
            <?php require '../app/views/components/filter.php'; ?>

            <!-- Chỉ số hiệu suất tổng quan cho riêng bài viết -->
            <div class="row g-3 mb-4">
                <!-- Lượt xem -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-eye', 'label' => 'Lượt xem (View)', 'value' => $s['views'], 'color' => 'primary']); ?>
                <!-- Liên hệ -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-headset', 'label' => 'Liên hệ (Lead)', 'value' => $s['contacts'], 'color' => 'success']); ?>
                <!-- Cuộc gọi -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-phone-volume', 'label' => 'Gọi điện (Call)', 'value' => $s['calls'], 'color' => 'danger']); ?>
                <!-- Chat -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-comments', 'label' => 'Tin nhắn Chat', 'value' => $s['chats'], 'color' => 'info']); ?>
                <!-- Lưu tin -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-heart', 'label' => 'Lưu tin (Save)', 'value' => $s['saves'], 'color' => 'warning']); ?>
                <!-- Chia sẻ -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-share-nodes', 'label' => 'Chia sẻ (Share)', 'value' => $s['shares'], 'color' => 'secondary']); ?>
                <!-- Tỷ lệ CTR -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-arrow-pointer', 'label' => 'Tỷ lệ CTR', 'value' => $s['ctr'] . '%', 'color' => 'primary', 'extra' => 'Lượt gọi + chat / xem']); ?>
                <!-- Tỷ lệ chuyển đổi -->
                <?php $this->view('components/statistic-card', ['icon' => 'fa-circle-check', 'label' => 'Chuyển đổi', 'value' => $s['conversion_rate'] . '%', 'color' => 'success', 'extra' => 'Gọi + chat + lưu / xem']); ?>
            </div>

            <!-- Các biểu đồ thống kê cho riêng bài viết -->
            <div class="mb-4">
                <?php require '../app/views/components/chart.php'; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
