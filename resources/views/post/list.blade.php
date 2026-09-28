@include('layouts.header')

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
</style>

<main class="container py-4">
    <div class="row g-4">
        @include('nguoi-dung.sidebar')
        
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
                                    <td style="max-width: 320px;">
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
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-no-caret rounded-pill px-2.5 py-1 fw-bold shadow-xs" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i> <span class="d-none d-lg-inline ms-1" style="font-size: 0.8rem;">Thao tác</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px; font-size: 0.88rem; min-width: 160px; z-index: 1050;">
                                                <li>
                                                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= URL_ROOT ?>/nguoi-dung/editPost/<?= $p->id ?>">
                                                        <i class="fa-solid fa-pen text-primary" style="width: 16px;"></i> Chỉnh sửa
                                                    </a>
                                                </li>
                                                
                                                <?php if($p->trang_thai==='xuat_ban'): ?>
                                                    <li>
                                                        <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/upPost/<?= $p->id ?>" class="m-0">
                                                            <?= Csrf::field() ?>
                                                            <button type="submit" class="dropdown-item py-2 text-success d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-arrow-up" style="width: 16px;"></i> UP tin
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/hidePost/<?= $p->id ?>" class="m-0">
                                                            <?= Csrf::field() ?>
                                                            <button type="submit" class="dropdown-item py-2 text-secondary d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-eye-slash" style="width: 16px;"></i> Ẩn tin
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php elseif($p->trang_thai==='an'): ?>
                                                    <li>
                                                        <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/showPost/<?= $p->id ?>" class="m-0">
                                                            <?= Csrf::field() ?>
                                                            <button type="submit" class="dropdown-item py-2 text-success d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-eye" style="width: 16px;"></i> Hiện lại
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <?php if($p->goi_vip): ?>
                                                    <li>
                                                        <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/renewVipPost/<?= $p->id ?>" class="m-0">
                                                            <?= Csrf::field() ?>
                                                            <input type="hidden" name="days" value="7">
                                                            <button type="submit" class="dropdown-item py-2 text-warning d-flex align-items-center gap-2">
                                                                <i class="fa-solid fa-clock-rotate-left" style="width: 16px;"></i> Gia hạn VIP
                                                            </button>
                                                        </form>
                                                    </li>
                                                <?php endif; ?>
                                                
                                                <li>
                                                    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/clonePost/<?= $p->id ?>" class="m-0">
                                                        <?= Csrf::field() ?>
                                                        <button type="submit" class="dropdown-item py-2 text-dark d-flex align-items-center gap-2">
                                                            <i class="fa-solid fa-copy" style="width: 16px;"></i> Sao chép
                                                        </button>
                                                    </form>
                                                </li>
                                                
                                                <li><hr class="dropdown-divider bg-light"></li>
                                                
                                                <li>
                                                    <form method="POST" action="<?= URL_ROOT ?>/nguoi-dung/deletePost/<?= $p->id ?>" class="m-0" onsubmit="return confirm('Xóa tin đăng này?')">
                                                        <?= Csrf::field() ?>
                                                        <button type="submit" class="dropdown-item py-2 text-danger d-flex align-items-center gap-2">
                                                            <i class="fa-solid fa-trash" style="width: 16px;"></i> Xóa tin
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

@include('layouts.footer')
