@include('layouts.header')
<?php
$a = $analytics;
$s = $a['summary'];
$r = $a['range'];
?>
<link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/analytics.css?v=<?= filemtime(APP_ROOT . '/public/css/analytics.css') ?>">

<div class="container py-4 analytics-shell">
    <div class="row">
        <!-- Sidebar thành viên -->
        @include('nguoi-dung.sidebar')

        <!-- Nội dung chính -->
        <main class="col-lg-9 col-md-8 col-12">
            <!-- Banner chào mừng & Xuất báo cáo -->
            <section class="analytics-header p-4 mb-4 shadow-sm rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold mb-1">Hiệu quả tin đăng</span>
                    <h2 class="h4 fw-bold mb-1 text-dark">Dashboard Analytics</h2>
                    <p class="mb-0 small text-muted">
                        <?= htmlspecialchars($r['label']) ?> · <?= date('d/m/Y', strtotime($r['from'])) ?> – <?= date('d/m/Y', strtotime($r['to'])) ?>
                    </p>
                </div>
                
                <!-- Nhóm nút Xuất báo cáo -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm fw-bold dropdown-toggle" type="button" id="exportMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-export me-1"></i> Xuất dữ liệu
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3" aria-labelledby="exportMenu">
                        <li>
                            <a class="dropdown-item py-2 small fw-semibold" href="<?= URL_ROOT ?>/nguoi-dung/analytics/export?<?= http_build_query(array_merge($_GET, ['format' => 'csv'])) ?>">
                                <i class="fa-solid fa-file-csv text-success me-2 fs-5"></i> Xuất CSV
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 small fw-semibold" href="<?= URL_ROOT ?>/nguoi-dung/analytics/export?<?= http_build_query(array_merge($_GET, ['format' => 'excel'])) ?>">
                                <i class="fa-solid fa-file-excel text-primary me-2 fs-5"></i> Xuất Excel (XLS)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 small fw-semibold" href="<?= URL_ROOT ?>/nguoi-dung/analytics/export?<?= http_build_query(array_merge($_GET, ['format' => 'pdf'])) ?>" target="_blank">
                                <i class="fa-solid fa-file-pdf text-danger me-2 fs-5"></i> In / Lưu PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Bộ lọc thời gian & VIP -->
            <?php require '../app/views/components/filter.php'; ?>

            <!-- Chỉ số hiệu suất tổng quan -->
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

            <!-- Các tin nổi bật tốt nhất -->
            <div class="row g-3 mb-4">
                <!-- Tin nhiều view nhất -->
                <div class="col-lg-6 col-12">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-white" style="border-left: 5px solid #2563eb !important;">
                        <div class="card-body p-3">
                            <span class="text-uppercase small fw-bold text-muted d-block mb-1">Tin nhiều lượt xem nhất</span>
                            <?php if ($a['most_viewed']): ?>
                                <a href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $a['most_viewed']->id ?>" class="fw-bold text-decoration-none text-dark d-block mt-2 h6 mb-1 text-truncate">
                                    <?= htmlspecialchars($a['most_viewed']->tieu_de) ?>
                                </a>
                                <div class="small text-muted mt-2">
                                    Tổng: <strong class="text-primary fs-5"><?= number_format($a['most_viewed']->views) ?></strong> lượt xem
                                </div>
                            <?php else: ?>
                                <div class="text-muted small mt-2">Chưa có dữ liệu bài đăng.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tin chuyển đổi tốt nhất -->
                <div class="col-lg-6 col-12">
                    <div class="card border-0 shadow-sm rounded-3 h-100 bg-white" style="border-left: 5px solid #059669 !important;">
                        <div class="card-body p-3">
                            <span class="text-uppercase small fw-bold text-muted d-block mb-1">Tin hiệu quả chuyển đổi nhất</span>
                            <?php if ($a['best_conversion']): ?>
                                <a href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $a['best_conversion']->id ?>" class="fw-bold text-decoration-none text-dark d-block mt-2 h6 mb-1 text-truncate">
                                    <?= htmlspecialchars($a['best_conversion']->tieu_de) ?>
                                </a>
                                <div class="small text-muted mt-2">
                                    Tỷ lệ chuyển đổi: <strong class="text-success fs-5"><?= $a['best_conversion']->conversion_rate ?>%</strong>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small mt-2">Chưa có dữ liệu bài đăng.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Các biểu đồ thống kê -->
            <div class="mb-4">
                <?php require '../app/views/components/chart.php'; ?>
            </div>

            <!-- Danh sách bài đăng chi tiết -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white" id="danh-sach-tin">
                <div class="card-header bg-white border-0 pt-3 pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold text-secondary mb-0">Hiệu quả chi tiết từng bài đăng</h6>
                    
                    <!-- Nhóm nút sắp xếp bảng -->
                    <div class="btn-group btn-group-sm rounded-3 overflow-hidden border">
                        <?php foreach(['view'=>'Lượt xem','contact'=>'Liên hệ','chat'=>'Chat','save'=>'Lưu tin','share'=>'Chia sẻ'] as $key=>$lbl): ?>
                            <a class="btn btn-sm <?= $a['sort']===$key?'btn-primary':'btn-light' ?> border-0 fw-semibold" 
                               href="?<?= http_build_query(array_merge($_GET,['sort'=>$key,'page'=>1])) ?>#danh-sach-tin">
                                <?= $lbl ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small fw-bold">
                                <tr>
                                    <th class="ps-3" style="width: 80px;">Mã tin</th>
                                    <th>Tiêu đề tin đăng</th>
                                    <th class="text-center" style="width: 80px;">Xem</th>
                                    <th class="text-center" style="width: 80px;">Liên hệ</th>
                                    <th class="text-center" style="width: 80px;">Gọi điện</th>
                                    <th class="text-center" style="width: 80px;">Chat</th>
                                    <th class="text-center" style="width: 80px;">Lưu</th>
                                    <th class="text-center" style="width: 80px;">Chia sẻ</th>
                                    <th class="text-center" style="width: 100px;">Chuyển đổi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($a['top_posts'])): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted small">
                                            Chưa có dữ liệu bài đăng nào.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($a['top_posts'] as $post): ?>
                                        <tr>
                                            <td class="ps-3 text-secondary small fw-semibold">#<?= $post->id ?></td>
                                            <td>
                                                <a href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $post->id ?>" class="fw-semibold text-decoration-none text-dark d-block text-truncate" style="max-width: 320px;">
                                                    <?= htmlspecialchars($post->tieu_de) ?>
                                                </a>
                                            </td>
                                            <td class="text-center fw-bold text-secondary"><?= number_format($post->views) ?></td>
                                            <td class="text-center fw-bold text-success"><?= number_format($post->contacts) ?></td>
                                            <td class="text-center text-muted"><?= number_format($post->calls) ?></td>
                                            <td class="text-center text-muted"><?= number_format($post->chats) ?></td>
                                            <td class="text-center text-muted"><?= number_format($post->saves) ?></td>
                                            <td class="text-center text-muted"><?= number_format($post->shares) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                                    <?= number_format($post->conversion_rate, 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Phân trang bảng -->
                <?php if ($a['total_pages'] > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0 gap-1">
                                <?php for ($p = 1; $p <= $a['total_pages']; $p++): ?>
                                    <li class="page-item <?= $p === $a['page'] ? 'active' : '' ?>">
                                        <a class="page-link rounded-3 border-0" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>#danh-sach-tin">
                                            <?= $p ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

@include('layouts.footer')
