@include('admin.layouts.header')

<div class="content-wrapper p-3 bg-light">
    <!-- Tiêu đề trang -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Quản lý báo cáo vi phạm</h1>
                    <p class="text-muted mb-0 small">Xem xét và giải quyết các khiếu nại báo cáo tin đăng không chính xác từ người dùng.</p>
                </div>
                <div class="col-sm-6 text-end">
                    <a href="<?= URL_ROOT ?>/admin/du-an" class="btn btn-sm btn-outline-secondary fw-bold px-3">
                        <i class="fa-solid fa-hotel me-1"></i> Quản lý tin đăng
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Danh sách báo cáo -->
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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-secondary small fw-bold">
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th>Tin đăng bị báo cáo</th>
                                <th>Người báo cáo</th>
                                <th>Lý do</th>
                                <th>Chi tiết mô tả</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú Admin</th>
                                <th>Ngày báo cáo</th>
                                <th class="text-end pe-3" style="width: 130px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small">Không tìm thấy báo cáo vi phạm nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $r): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">#<?= $r->id ?></td>
                                        <td>
                                            <a href="<?= URL_ROOT ?>/admin/du-an/detail/<?= $r->ma_du_an ?>" class="text-dark fw-bold text-decoration-none d-block text-truncate" style="max-width: 200px;">
                                                <?= htmlspecialchars($r->post_title) ?>
                                            </a>
                                            <span class="text-muted small">Mã tin: #<?= $r->ma_du_an ?></span>
                                        </td>
                                        <td>
                                            <strong class="d-block text-secondary"><?= htmlspecialchars($r->ho_ten ?: $r->reporter_name ?: 'Khách vãng lai') ?></strong>
                                            <span class="text-muted small"><?= htmlspecialchars($r->email ?: 'N/A') ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $reasonText = match ($r->ly_do) {
                                                'thong_tin_sai' => 'Thông tin sai lệch',
                                                'hinh_anh_sai'  => 'Hình ảnh sai thực tế',
                                                'gia_sai'       => 'Sai lệch giá',
                                                'lua_dao'       => 'Nghi ngờ lừa đảo',
                                                'tin_trung_lap' => 'Tin đăng trùng lặp',
                                                default         => 'Lý do khác'
                                            };
                                            ?>
                                            <span class="badge bg-light text-danger border border-danger"><?= $reasonText ?></span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small d-block text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($r->mo_ta) ?>">
                                                <?= htmlspecialchars($r->mo_ta ?: 'Không có mô tả thêm.') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($r->trang_thai) {
                                                'cho_xu_ly' => 'warning text-dark',
                                                'da_xu_ly'  => 'success',
                                                'da_huy'    => 'secondary',
                                                default     => 'light text-dark'
                                            };
                                            $statusLabel = match ($r->trang_thai) {
                                                'cho_xu_ly' => 'Chờ xử lý',
                                                'da_xu_ly'  => 'Đã xử lý',
                                                'da_huy'    => 'Đã hủy bỏ',
                                                default     => $r->trang_thai
                                            };
                                            ?>
                                            <span class="badge bg-<?= $badgeClass ?>"><?= $statusLabel ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted small d-block text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($r->ghi_chu_admin) ?>">
                                                <?= htmlspecialchars($r->ghi_chu_admin ?: 'Chưa có ghi chú.') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-secondary small"><?= date('d/m/Y H:i', strtotime($r->ngay_tao)) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if ($r->trang_thai === 'cho_xu_ly'): ?>
                                                <button onclick='openResolveModal(<?= json_encode($r) ?>)' class="btn btn-xs btn-outline-success py-1 px-2.5 rounded fw-bold" style="font-size: 0.72rem;">
                                                    Xử lý
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">Đã xong</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Giải quyết khiếu nại -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="resolveForm">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Xử lý báo cáo vi phạm</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Phương thức xử lý</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="da_xu_ly">Chấp nhận báo cáo (Đã xử lý)</option>
                            <option value="da_huy">Từ chối / Hủy bỏ báo cáo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Ghi chú phản hồi / lý do xử lý</label>
                        <textarea name="ghi_chu_admin" rows="4" class="form-control form-control-sm text-secondary" placeholder="Nhập ghi chú phản hồi..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold">Xác nhận xử lý</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(report) {
    document.getElementById('resolveForm').action = `<?= URL_ROOT ?>/admin/du-an/resolve-report/${report.id}`;
    const modal = new bootstrap.Modal(document.getElementById('resolveModal'));
    modal.show();
}
</script>

@include('admin.layouts.footer')
