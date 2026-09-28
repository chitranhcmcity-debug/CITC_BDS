<?php require '../app/views/layouts/header.php'; ?>

<?php
if (!function_exists('format_post_price')) {
    function format_post_price($price) {
        if (empty($price)) {
            return 'Thỏa thuận';
        }
        if (preg_match('/[a-zA-Z\p{L}]/u', $price)) {
            return $price;
        }
        $num = (float)preg_replace('/[^0-9.]/', '', $price);
        if ($num <= 0) {
            return 'Thỏa thuận';
        }
        if ($num >= 1000000000) {
            $ty = $num / 1000000000;
            return (round($ty, 2)) . ' tỷ';
        }
        if ($num >= 1000000) {
            $trieu = $num / 1000000;
            return (round($trieu, 2)) . ' triệu';
        }
        return number_format($num) . ' đ';
    }
}
?>

<style>
    .post-table {
        table-layout: fixed;
        width: 100%;
    }
    .post-table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f8f9fa !important;
        color: #495057;
        padding: 14px 16px !important;
        border-bottom: 2px solid #dee2e6 !important;
    }
    .post-table td {
        padding: 16px !important;
        border-bottom: 1px solid #edf2f7 !important;
    }
    .hover-zoom {
        transition: transform 0.3s ease;
    }
    .hover-zoom:hover {
        transform: scale(1.08);
    }
    .hover-text-primary {
        color: #1a1a1a;
        transition: color 0.2s ease;
    }
    .hover-text-primary:hover {
        color: #5645d4 !important;
    }
    .page-link {
        font-size: 0.88rem;
        min-width: 38px;
        text-align: center;
        transition: all 0.2s ease;
        background: #fff;
        border: 1px solid #e2e8f0;
    }
    .page-link:hover {
        background-color: #f1f5f9;
        color: #5645d4 !important;
    }
    .page-link.active {
        background: #5645d4 !important;
        border-color: #5645d4 !important;
        color: #fff !important;
    }
    .post-action-modal .modal-content {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.24);
        overflow: hidden;
    }
    .post-action-modal .post-action-item {
        width: 100%;
        min-height: 46px;
        border: 1px solid #e8edf3;
        border-radius: 11px;
        background: #fff;
        padding: 10px 13px;
        display: flex;
        align-items: center;
        gap: 11px;
        color: #263142;
        font-weight: 600;
        text-align: left;
        text-decoration: none;
        transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
    }
    .post-action-modal .post-action-item:hover {
        background: #f7f8ff;
        border-color: #cfd4ff;
        transform: translateY(-1px);
    }
    .post-action-modal .post-action-item i {
        width: 20px;
        text-align: center;
    }
    .post-action-modal .post-action-danger {
        color: #dc3545;
        background: #fff8f8;
        border-color: #ffd8dc;
    }
</style>

<main class="container py-4">
    <div class="row g-4">
        <?php require '../app/views/nguoi-dung/sidebar.php'; ?>
        
        <section class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-folder-open text-primary me-2"></i>Quản lý tin đăng
                </h1>
                <a class="btn btn-primary btn-sm px-3 d-flex align-items-center gap-1.5 shadow-xs" href="<?= URL_ROOT ?>/nguoi-dung/post">
                    <i class="fa-solid fa-plus"></i> Đăng tin mới
                </a>
            </div>

            <?php foreach(['success'=>'success','error'=>'danger'] as $k=>$type): if(Session::get($k)): ?>
                <div class="alert alert-<?= $type ?> alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fa-solid <?= $type === 'success' ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger' ?> me-2"></i>
                    <?= htmlspecialchars((string)Session::get($k)); Session::delete($k); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; endforeach; ?>

            <div class="card profile-card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table post-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-nowrap" style="width: 45%;">Tin đăng</th>
                                <th class="text-nowrap text-end" style="width: 15%;">Giá</th>
                                <th class="text-nowrap text-center" style="width: 15%;">Trạng thái</th>
                                <th class="text-nowrap text-center" style="width: 10%;">Thống kê</th>
                                <th class="text-nowrap text-end" style="width: 15%;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!$posts): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <div class="py-4">
                                            <i class="fa-regular fa-folder-open fs-1 text-muted mb-3 opacity-50"></i>
                                            <p class="mb-0 fw-medium">Bạn chưa có tin đăng nào.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php 
                            $labels = [
                                'nhap' => 'Nháp',
                                'cho_duyet' => 'Chờ duyệt',
                                'xuat_ban' => 'Đang hiển thị',
                                'da_ban' => 'Đã bán',
                                'tu_choi' => 'Bị từ chối',
                                'an' => 'Đã ẩn'
                            ];
                            
                            foreach($posts as $p): 
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="position-relative shadow-sm rounded overflow-hidden" style="width: 80px; height: 60px; flex-shrink: 0;">
                                                <img src="<?= htmlspecialchars(img_url($p->anh_thu_nho??'')) ?>" loading="lazy" class="w-100 h-100 object-fit-cover hover-zoom" alt="">
                                            </div>
                                            <div style="min-width: 0; flex-grow: 1;">
                                                <a href="<?= URL_ROOT ?>/du-an/detail/<?= $p->duong_dan ?>" target="_blank" class="fw-bold hover-text-primary text-decoration-none d-block text-truncate" title="<?= htmlspecialchars($p->tieu_de) ?>">
                                                    <?= htmlspecialchars($p->tieu_de) ?>
                                                </a>
                                                <div class="d-flex align-items-center gap-2 mt-1.5 small text-muted">
                                                    <span><i class="fa-regular fa-calendar-days me-1"></i><?= date('d/m/Y', strtotime($p->ngay_tao)) ?></span>
                                                    <?php if($p->goi_vip): ?>
                                                        <span class="badge vip-badge d-inline-flex align-items-center gap-0.5 ms-1">
                                                            <i class="fa-solid fa-crown" style="font-size: 0.7rem;"></i> VIP <?= (int)$p->goi_vip ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-end">
                                        <span class="text-danger fw-bold fs-6">
                                            <?= format_post_price($p->gia) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php
                                        $badgeClasses = [
                                            'nhap' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            'cho_duyet' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            'xuat_ban' => 'bg-success-subtle text-success border border-success-subtle',
                                            'da_ban' => 'bg-info-subtle text-info border border-info-subtle',
                                            'tu_choi' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'an' => 'bg-dark-subtle text-dark border border-dark-subtle'
                                        ];
                                        $badgeClass = $badgeClasses[$p->trang_thai] ?? 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                        ?>
                                        <span class="badge <?= $badgeClass ?> rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                            <?= htmlspecialchars($labels[$p->trang_thai] ?? $p->trang_thai) ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <a href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $p->id ?>" class="btn btn-sm btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Xem thống kê">
                                            <i class="fa-solid fa-chart-line"></i>
                                        </a>
                                    </td>
                                    
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-bold shadow-xs"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#postActionsModal<?= (int)$p->id ?>"
                                                aria-label="Mở các thao tác cho tin <?= htmlspecialchars($p->tieu_de, ENT_QUOTES) ?>">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                            <span class="d-none d-lg-inline ms-1" style="font-size: 0.8rem;">Thao tác</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php foreach (($posts ?: []) as $actionPost): ?>
                <div class="modal fade post-action-modal" id="postActionsModal<?= (int)$actionPost->id ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-1 px-4 pt-4">
                                <div class="pe-2" style="min-width: 0;">
                                    <h2 class="modal-title h5 fw-bold mb-1">Thao tác tin đăng</h2>
                                    <div class="small text-muted text-truncate" title="<?= htmlspecialchars($actionPost->tieu_de, ENT_QUOTES) ?>">
                                        <?= htmlspecialchars($actionPost->tieu_de) ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body px-4 pt-3 pb-4 d-grid gap-2">
                                <a class="post-action-item" href="<?= URL_ROOT ?>/nguoi-dung/editPost/<?= (int)$actionPost->id ?>">
                                    <i class="fa-solid fa-pen text-primary"></i> Chỉnh sửa
                                </a>

                                <?php if ($actionPost->trang_thai === 'xuat_ban'): ?>
                                    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/upPost/<?= (int)$actionPost->id ?>">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="post-action-item text-success">
                                            <i class="fa-solid fa-arrow-up"></i> UP tin
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/hidePost/<?= (int)$actionPost->id ?>">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="post-action-item text-secondary">
                                            <i class="fa-solid fa-eye-slash"></i> Ẩn tin
                                        </button>
                                    </form>
                                <?php elseif ($actionPost->trang_thai === 'an'): ?>
                                    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/showPost/<?= (int)$actionPost->id ?>">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="post-action-item text-success">
                                            <i class="fa-solid fa-eye"></i> Hiện lại
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($actionPost->goi_vip): ?>
                                    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/renewVipPost/<?= (int)$actionPost->id ?>">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="days" value="7">
                                        <button type="submit" class="post-action-item text-warning">
                                            <i class="fa-solid fa-clock-rotate-left"></i> Gia hạn VIP 7 ngày
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/deletePost/<?= (int)$actionPost->id ?>" onsubmit="return confirm('Xóa tin đăng này?')">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="post-action-item post-action-danger">
                                        <i class="fa-solid fa-trash"></i> Xóa tin
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Phân trang -->
            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center mb-0 gap-1">
                        
                        <!-- Trang đầu -->
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0 shadow-xs px-3 py-2 text-dark" href="?page=1" title="Trang đầu">
                                    <i class="fa-solid fa-angles-left small"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0 shadow-xs px-3 py-2 text-dark" href="?page=<?= $currentPage - 1 ?>" title="Trang trước">
                                    <i class="fa-solid fa-angle-left small"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Danh sách trang số -->
                        <?php
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                            $active = ($i === $currentPage) ? 'active fw-bold' : 'text-dark';
                        ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0 shadow-xs px-3 py-2 <?= $active ?>" href="?page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Trang sau / cuối -->
                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0 shadow-xs px-3 py-2 text-dark" href="?page=<?= $currentPage + 1 ?>" title="Trang sau">
                                    <i class="fa-solid fa-angle-right small"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0 shadow-xs px-3 py-2 text-dark" href="?page=<?= $totalPages ?>" title="Trang cuối">
                                    <i class="fa-solid fa-angles-right small"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </nav>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require '../app/views/layouts/footer.php'; ?>
