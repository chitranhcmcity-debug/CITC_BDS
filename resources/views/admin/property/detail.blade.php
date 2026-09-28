<?php
@include('admin.layouts.header')
$statusMap = [
    'nhap' => ['secondary', 'Bản nháp'], 'cho_duyet' => ['warning', 'Chờ duyệt'],
    'xuat_ban' => ['success', 'Đang hiển thị'], 'da_ban' => ['info', 'Đã bán'],
    'tu_choi' => ['danger', 'Bị từ chối'], 'an' => ['secondary', 'Đã ẩn'],
    'khoa' => ['dark', 'Đã khóa'], 'xoa' => ['danger', 'Đã xóa'],
];
[$statusClass, $statusText] = $statusMap[$post->trang_thai] ?? ['secondary', 'Không xác định'];
?>

<div class="content-wrapper p-3 bg-light">
    <!-- Nút quay lại và Tiêu đề -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 mb-2">
                    <a href="<?= URL_ROOT ?>/admin/du-an" class="text-decoration-none text-secondary small fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                    </a>
                </div>
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1">Chi tiết tin đăng #<?= $post->id ?></h1>
                    <span class="badge bg-light text-secondary border">Mã tin: <?= $post->id ?></span>
                    <?php if ($reportsCount > 0): ?>
                        <span class="badge bg-danger ms-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Bị báo cáo: <?= $reportsCount ?> lần</span>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6 text-end">
                    <!-- Các nút hành động chính -->
                    <div class="d-flex gap-1.5 justify-content-end align-items-center">
                        <?php if ($post->trang_thai === 'cho_duyet'): ?>
                            <!-- Nút duyệt -->
                            <form action="<?= URL_ROOT ?>/admin/du-an/approve/<?= $post->id ?>" method="POST" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-success fw-bold px-3">
                                    <i class="fa-solid fa-check me-1"></i> Duyệt tin
                                </button>
                            </form>
                            
                            <!-- Nút từ chối -->
                            <button class="btn btn-sm btn-danger fw-bold px-3" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fa-solid fa-xmark me-1"></i> Từ chối
                            </button>
                        <?php endif; ?>

                        <!-- Ghim / VIP / Gia hạn -->
                        <button class="btn btn-sm btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#vipModal">
                            <i class="fa-solid fa-gem me-1"></i> Gói VIP
                        </button>
                        <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#renewModal">
                            <i class="fa-solid fa-clock me-1"></i> Gia hạn
                        </button>

                        <!-- Khóa / Mở khóa -->
                        <?php if ($post->trang_thai === 'khoa'): ?>
                            <form action="<?= URL_ROOT ?>/admin/du-an/unlock/<?= $post->id ?>" method="POST" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-dark fw-bold">
                                    <i class="fa-solid fa-lock-open me-1"></i> Mở khóa
                                </button>
                            </form>
                        <?php else: ?>
                            <form action="<?= URL_ROOT ?>/admin/du-an/lock/<?= $post->id ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn khóa tin đăng này?');">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-dark fw-bold">
                                    <i class="fa-solid fa-lock me-1"></i> Khóa tin
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Ẩn / Hiện hiển thị -->
                        <?php if ($post->trang_thai === 'an'): ?>
                            <form action="<?= URL_ROOT ?>/admin/du-an/show/<?= $post->id ?>" method="POST" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary fw-bold">
                                    <i class="fa-solid fa-eye me-1"></i> Hiện tin
                                </button>
                            </form>
                        <?php else: ?>
                            <form action="<?= URL_ROOT ?>/admin/du-an/hide/<?= $post->id ?>" method="POST" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary fw-bold">
                                    <i class="fa-solid fa-eye-slash me-1"></i> Ẩn tin
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Sửa -->
                        <a href="<?= URL_ROOT ?>/admin/du-an/edit/<?= $post->id ?>" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="fa-solid fa-edit me-1"></i> Sửa
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nội dung chính -->
    <section class="content text-start">
        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Cột trái: Chi tiết bất động sản -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom pt-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-circle-info text-primary me-2"></i>Thông tin tin đăng</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="fw-bold text-dark mb-3"><?= htmlspecialchars($post->tieu_de) ?></h4>
                        
                        <!-- Mô tả -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Mô tả chi tiết</label>
                            <div class="border rounded bg-light p-3 text-secondary" style="font-size: 0.9rem; line-height: 1.6; white-space: pre-wrap; max-height: 350px; overflow-y: auto;">
                                <?= htmlspecialchars($post->mo_ta) ?>
                            </div>
                        </div>

                        <!-- Gallery ảnh và video -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary mb-1">Ảnh đại diện tin đăng</label>
                            <div class="text-center bg-light p-3 rounded border">
                                <?php if ($post->anh_thu_nho): ?>
                                    <img src="<?= img_url($post->anh_thu_nho) ?>" class="img-fluid rounded border shadow-sm" style="max-height: 280px; object-fit: contain;">
                                <?php else: ?>
                                    <span class="text-muted small">Tin đăng chưa tải lên hình ảnh nào.</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Thông số kỹ thuật -->
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Giá bán/thuê</span>
                                <strong class="text-danger fs-5">
                                    <?php
                                    $gia = $post->gia;
                                    if (is_numeric($gia)) {
                                        if ($gia >= 1000000000) $gia = ($gia / 1000000000) . ' tỷ';
                                        elseif ($gia >= 1000000) $gia = ($gia / 1000000) . ' triệu';
                                        else $gia = number_format($gia) . ' đ';
                                    }
                                    echo htmlspecialchars($gia);
                                    ?>
                                </strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Diện tích</span>
                                <strong class="text-dark fs-5"><?= $post->dien_tich ?> m²</strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Pháp lý</span>
                                <strong class="text-secondary"><?= htmlspecialchars($post->phap_ly ?: 'Chưa rõ') ?></strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Hướng nhà</span>
                                <strong class="text-secondary"><?= htmlspecialchars($post->huong_nha ?: 'Chưa rõ') ?></strong>
                            </div>

                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Số phòng ngủ</span>
                                <strong class="text-secondary"><?= $post->so_phong_ngu ?: '0' ?> phòng</strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Số WC</span>
                                <strong class="text-secondary"><?= $post->so_phong_wc ?: '0' ?> phòng</strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Mặt tiền</span>
                                <strong class="text-secondary"><?= htmlspecialchars($post->mat_tien ?: 'Chưa rõ') ?></strong>
                            </div>
                            <div class="col-md-3 col-6">
                                <span class="text-muted small d-block">Nội thất</span>
                                <strong class="text-secondary"><?= htmlspecialchars($post->noi_that ?: 'Chưa rõ') ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Trạng thái hiển thị, Người đăng & VIP -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom pt-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-shield-halved text-success me-2"></i>Trạng thái & Người đăng</h5>
                    </div>
                    <div class="card-body" style="font-size: 0.88rem;">
                        <ul class="list-group list-group-flush mb-0 text-start">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                <span class="text-muted">Trạng thái tin</span>
                                <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                            </li>
                            <?php if ($post->trang_thai === 'tu_choi' && $post->ly_do_tu_choi): ?>
                                <li class="list-group-item py-2.5 ps-0 pe-0">
                                    <span class="text-danger fw-bold d-block mb-1">Lý do từ chối duyệt:</span>
                                    <div class="bg-light p-2 rounded text-danger small border-start border-3 border-danger"><?= htmlspecialchars($post->ly_do_tu_choi) ?></div>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                <span class="text-muted">Gói VIP hiện tại</span>
                                <span class="fw-bold text-success"><?= $post->goi_vip > 0 ? "VIP {$post->goi_vip}" : 'Tin thường' ?></span>
                            </li>
                            <?php if ($post->goi_vip > 0): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                    <span class="text-muted">Hạn gói VIP</span>
                                    <span class="text-secondary"><?= date('d/m/Y H:i', strtotime($post->ngay_het_han_vip)) ?></span>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                <span class="text-muted">Hạn hiển thị tin</span>
                                <span class="text-secondary"><?= date('d/m/Y H:i', strtotime($post->ngay_het_han)) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                <span class="text-muted">Người đăng</span>
                                <span class="fw-bold text-primary"><?= htmlspecialchars($post->seller_name) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                <span class="text-muted">Số điện thoại liên hệ</span>
                                <span class="text-secondary"><?= htmlspecialchars($post->so_dien_thoai_lien_he ?: $post->seller_phone) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 ps-0 pe-0">
                                <span class="text-muted">Email người đăng</span>
                                <span class="text-secondary text-truncate" style="max-width: 170px;"><?= htmlspecialchars($post->seller_email) ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Từ chối duyệt -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= URL_ROOT ?>/admin/du-an/reject/<?= $post->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Lý do từ chối duyệt tin</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Ghi chú lý do vi phạm</label>
                        <textarea name="reason" rows="4" class="form-control form-control-sm text-secondary" placeholder="Ví dụ: Hình ảnh sai thực tế / Tiêu đề chứa từ khóa quảng cáo..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">Xác nhận từ chối</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Đổi VIP -->
<div class="modal fade" id="vipModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/du-an/vip/<?= $post->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Chuyển gói VIP tin đăng</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Gói VIP</label>
                        <select name="vip_level" class="form-select form-select-sm" required>
                            <option value="0">Tin thường</option>
                            <option value="1">VIP 1 (Premium)</option>
                            <option value="2">VIP 2 (Hot)</option>
                            <option value="3">VIP 3 (Vip)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Thời hạn VIP (ngày)</label>
                        <input type="number" name="days" class="form-control form-control-sm" value="7" min="1" max="365" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold">Đổi gói VIP</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Gia hạn -->
<div class="modal fade" id="renewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/du-an/renew/<?= $post->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Gia hạn hiển thị tin đăng</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Số ngày gia hạn thêm</label>
                        <input type="number" name="days" class="form-control form-control-sm" value="30" min="1" max="180" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">Gia hạn ngay</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('admin.layouts.footer')
