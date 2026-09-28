<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="content-wrapper p-3 bg-light">
    <!-- Tiêu đề trang -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1"><i class="fa-solid fa-hotel text-primary me-2"></i>Quản lý tin đăng Bất động sản</h1>
                    <p class="text-muted mb-0 small">Giám sát, kiểm duyệt, khóa/mở và xử lý vi phạm toàn bộ tin đăng trên hệ thống.</p>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="<?= URL_ROOT ?>/admin/du-an/report" class="btn btn-warning fw-bold px-3 shadow-sm btn-sm">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Báo cáo vi phạm
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Thống kê Analytics -->
    <section class="content mb-4">
        <div class="row g-3">
            <div class="col-md-2 col-4">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Tổng tin đăng</span>
                    <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Chờ duyệt</span>
                    <h4 class="fw-bold mb-0 text-warning"><?= number_format($stats['pending']) ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Bị từ chối</span>
                    <h4 class="fw-bold mb-0 text-danger"><?= number_format($stats['rejected']) ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Tin VIP hoạt động</span>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['vip']) ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Đã hết hạn</span>
                    <h4 class="fw-bold mb-0 text-secondary"><?= number_format($stats['expired']) ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-4">
                <div class="card p-3 shadow-sm border-0 bg-white">
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1" style="font-size: 0.72rem;">Đã bị khóa</span>
                    <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['locked']) ?></h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Bộ lọc tìm kiếm & Danh sách -->
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

        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-body">
                <!-- Form lọc nâng cao -->
                <form method="GET" action="" class="row g-2 mb-4 align-items-end" style="font-size: 0.85rem;">
                    <div class="col-lg-3 col-md-6 col-12">
                        <label class="form-label small fw-bold text-secondary mb-1">Tìm kiếm từ khóa</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Mã tin, tiêu đề, SĐT, Email..." value="<?= htmlspecialchars($filters['search']) ?>">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Trạng thái</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- Tất cả --</option>
                            <option value="cho_duyet" <?= $filters['status'] === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="xuat_ban" <?= $filters['status'] === 'xuat_ban' ? 'selected' : '' ?>>Đang hiển thị</option>
                            <option value="tu_choi" <?= $filters['status'] === 'tu_choi' ? 'selected' : '' ?>>Đã từ chối</option>
                            <option value="an" <?= $filters['status'] === 'an' ? 'selected' : '' ?>>Đã ẩn</option>
                            <option value="khoa" <?= $filters['status'] === 'khoa' ? 'selected' : '' ?>>Đã khóa</option>
                            <option value="da_ban" <?= $filters['status'] === 'da_ban' ? 'selected' : '' ?>>Đã bán</option>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-3 col-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Gói VIP</label>
                        <select name="vip_level" class="form-select form-select-sm">
                            <option value="">-- Tất cả --</option>
                            <option value="0" <?= $filters['vip_level'] === '0' ? 'selected' : '' ?>>Tin thường</option>
                            <option value="1" <?= $filters['vip_level'] === '1' ? 'selected' : '' ?>>VIP 1</option>
                            <option value="2" <?= $filters['vip_level'] === '2' ? 'selected' : '' ?>>VIP 2</option>
                            <option value="3" <?= $filters['vip_level'] === '3' ? 'selected' : '' ?>>VIP 3</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Danh mục</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>" <?= (int)$filters['category_id'] === (int)$cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->ten) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Giao dịch</label>
                        <select name="transaction_type" class="form-select form-select-sm">
                            <option value="">-- Tất cả --</option>
                            <option value="ban" <?= $filters['transaction_type'] === 'ban' ? 'selected' : '' ?>>Cần bán</option>
                            <option value="cho_thue" <?= $filters['transaction_type'] === 'cho_thue' ? 'selected' : '' ?>>Cho thuê</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-12 col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1 fw-bold py-1.5"><i class="fa-solid fa-filter me-1"></i>Lọc</button>
                        <a href="<?= URL_ROOT ?>/admin/du-an" class="btn btn-sm btn-light border flex-grow-1 text-secondary text-center py-1.5">Reset</a>
                    </div>
                </form>

                <!-- Bảng danh sách tin đăng -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-secondary small fw-bold">
                            <tr>
                                <th class="ps-3" style="width: 70px;">Mã tin</th>
                                <th style="width: 80px;">Hình ảnh</th>
                                <th>Tiêu đề bất động sản</th>
                                <th>Phân loại</th>
                                <th>Giá bán/thuê</th>
                                <th>Người đăng</th>
                                <th>VIP</th>
                                <th>Ngày đăng</th>
                                <th>Trạng thái</th>
                                <th class="text-end pe-3" style="width: 100px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted small">Không tìm thấy tin đăng nào khớp với bộ lọc tìm kiếm.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">#<?= $item->id ?></td>
                                        <td>
                                            <?php if ($item->anh_thu_nho): ?>
                                                <img src="<?= img_url($item->anh_thu_nho) ?>" class="img-thumbnail rounded" style="width: 64px; height: 48px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light text-muted rounded d-flex align-items-center justify-content-center text-center small" style="width: 64px; height: 48px;"><i class="fa-regular fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= URL_ROOT ?>/admin/du-an/detail/<?= $item->id ?>" class="text-dark fw-bold text-decoration-none d-block text-truncate" style="max-width: 250px;">
                                                <?= htmlspecialchars($item->tieu_de) ?>
                                            </a>
                                            <span class="text-muted small d-block"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($item->vi_tri) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border"><?= htmlspecialchars($item->category_name) ?></span>
                                            <span class="d-block text-muted small mt-0.5"><?= $item->loai_giao_dich === 'cho_thue' ? 'Cho thuê' : 'Bán' ?></span>
                                        </td>
                                        <td class="fw-bold text-danger">
                                            <?php
                                            $gia = $item->gia;
                                            if (is_numeric($gia)) {
                                                if ($gia >= 1000000000) $gia = ($gia / 1000000000) . ' tỷ';
                                                elseif ($gia >= 1000000) $gia = ($gia / 1000000) . ' triệu';
                                                else $gia = number_format($gia) . ' đ';
                                            }
                                            echo htmlspecialchars($gia);
                                            ?>
                                        </td>
                                        <td>
                                            <strong class="d-block text-secondary"><?= htmlspecialchars($item->seller_name ?: 'Thành viên') ?></strong>
                                            <span class="text-muted small">ID: #<?= $item->ma_nguoi_dung ?></span>
                                        </td>
                                        <td>
                                            <?php if ((int)$item->goi_vip > 0): ?>
                                                <span class="badge bg-success">VIP <?= $item->goi_vip ?></span>
                                                <span class="text-muted d-block small mt-0.5" style="font-size: 0.72rem;">Hạn: <?= date('d/m/Y', strtotime($item->ngay_het_han_vip)) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Thường</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-secondary small"><?= date('d/m/Y H:i', strtotime($item->ngay_tao)) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match ($item->trang_thai) {
                                                'cho_duyet' => 'warning text-dark',
                                                'xuat_ban'  => 'success',
                                                'tu_choi'   => 'danger',
                                                'an'        => 'secondary',
                                                'khoa'      => 'dark',
                                                default     => 'light text-dark'
                                            };
                                            $statusText = match ($item->trang_thai) {
                                                'cho_duyet' => 'Chờ duyệt',
                                                'xuat_ban'  => 'Hiển thị',
                                                'tu_choi'   => 'Từ chối',
                                                'an'        => 'Đã ẩn',
                                                'khoa'      => 'Đã khóa',
                                                default     => $item->trang_thai
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="<?= URL_ROOT ?>/admin/du-an/detail/<?= $item->id ?>" class="btn btn-xs btn-outline-info p-1 rounded" title="Xem chi tiết">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="<?= URL_ROOT ?>/admin/du-an/edit/<?= $item->id ?>" class="btn btn-xs btn-outline-primary p-1 rounded" title="Chỉnh sửa">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="<?= URL_ROOT ?>/admin/du-an/delete/<?= $item->id ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa tin đăng này?');">
                                                    <?= Csrf::field() ?>
                                                    <button type="submit" class="btn btn-xs btn-outline-danger p-1 rounded" title="Xóa tin">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if ($totalPages > 1): ?>
                    <?php
                    $pageNumbers = [1, $totalPages];
                    for ($candidate = max(1, $page - 2); $candidate <= min($totalPages, $page + 2); $candidate++) {
                        $pageNumbers[] = $candidate;
                    }
                    $pageNumbers = array_values(array_unique($pageNumbers));
                    sort($pageNumbers);
                    $previousPage = null;
                    ?>
                    <nav class="mt-3">
                        <ul class="pagination pagination-sm justify-content-center flex-wrap gap-1">
                            <?php foreach ($pageNumbers as $i): ?>
                                <?php if ($previousPage !== null && $i > $previousPage + 1): ?>
                                    <li class="page-item disabled"><span class="page-link border-0">…</span></li>
                                <?php endif; ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                                <?php $previousPage = $i; ?>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
