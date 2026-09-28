<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản Lý Tin Đăng (Dự Án)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="fa-solid fa-clock me-1"></i> Chờ Duyệt: <?= $data['pendingCount'] ?></span>
    </div>
</div>

<?php Session::flash('admin_msg'); ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form method="GET" action="<?= URL_ROOT ?>/admin/du-an" class="row g-2 align-items-end mb-4">
            <div class="col-lg-5 col-md-12">
                <label for="projectFilterKeyword" class="form-label fw-semibold">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="search" id="projectFilterKeyword" name="keyword" class="form-control"
                           value="<?= htmlspecialchars($data['filters']['keyword']) ?>"
                           placeholder="Nhập mã, tiêu đề, người đăng hoặc email">
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <label for="projectFilterType" class="form-label fw-semibold">Loại hình</label>
                <select id="projectFilterType" name="property_type" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($data['propertyTypes'] as $propertyType): ?>
                        <option value="<?= htmlspecialchars($propertyType) ?>" <?= $data['filters']['property_type'] === $propertyType ? 'selected' : '' ?>>
                            <?= htmlspecialchars($propertyType) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <label for="projectFilterStatus" class="form-label fw-semibold">Trạng thái</label>
                <select id="projectFilterStatus" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="cho_duyet" <?= $data['filters']['status'] === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                    <option value="xuat_ban" <?= $data['filters']['status'] === 'xuat_ban' ? 'selected' : '' ?>>Đã duyệt</option>
                    <option value="nhap" <?= $data['filters']['status'] === 'nhap' ? 'selected' : '' ?>>Bản nháp</option>
                    <option value="da_ban" <?= $data['filters']['status'] === 'da_ban' ? 'selected' : '' ?>>Đã bán</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
                <a href="<?= URL_ROOT ?>/admin/du-an" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        <div class="small text-muted mb-2">Tìm thấy <strong><?= $data['totalProjects'] ?></strong> dự án</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">Mã</th>
                        <th>Tiêu Đề / Người đăng</th>
                        <th class="text-nowrap">Loại Hình</th>
                        <th class="text-nowrap">Giá</th>
                        <th class="text-nowrap">Trạng Thái</th>
                        <th class="text-nowrap text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['projects'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fa-regular fa-folder-open fs-2 mb-3 text-secondary"></i><br>
                            Chưa có tin đăng nào.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($data['projects'] as $p): ?>
                        <tr>
                            <td class="fw-semibold text-muted">#<?= $p->id ?></td>
                            <td>
                                <div class="mb-2">
                                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= urlencode($p->duong_dan ?? '') ?>" target="_blank" class="text-decoration-none fw-bold text-dark lh-sm d-inline-block" style="max-width: 400px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                                        <?= htmlspecialchars($p->tieu_de) ?>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($p->ten_nguoi_dung) ?>&background=random" class="rounded-circle shadow-sm" width="24" height="24" alt="Avatar">
                                    <span class="text-muted small fw-medium"><?= htmlspecialchars($p->ten_nguoi_dung) ?></span>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <?php
                                $loaiClass = 'primary';
                                if (strpos(mb_strtolower($p->loai_bat_dong_san, 'UTF-8'), 'thuê') !== false) $loaiClass = 'success';
                                ?>
                                <span class="badge bg-<?= $loaiClass ?> bg-opacity-10 text-<?= $loaiClass ?> border border-<?= $loaiClass ?>-subtle rounded-pill px-2 py-1">
                                    <?= htmlspecialchars($p->loai_bat_dong_san) ?>
                                </span>
                            </td>
                            <td class="text-nowrap fw-semibold text-danger">
                                <?= htmlspecialchars($p->gia ?? 'Đang cập nhật') ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if ($p->trang_thai == 'cho_duyet'): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-dark border border-warning-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-circle-dot small me-1"></i> Chờ duyệt</span>
                                <?php elseif ($p->trang_thai == 'xuat_ban'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-check small me-1"></i> Đã duyệt</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2 py-1"><?= $p->trang_thai ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <?php if ($p->trang_thai == 'cho_duyet'): ?>
                                        <form action="<?= URL_ROOT ?>/admin/du-an/approve/<?= $p->id ?>" method="POST" class="m-0" onsubmit="return handleApproveConfirm(event, this)">
                                            <?= Csrf::field() ?>
                                            <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="Duyệt tin">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="<?= URL_ROOT ?>/admin/du-an/reject/<?= $p->id ?>" method="POST" class="m-0" onsubmit="var r=prompt('Nhập lý do từ chối:','Tin đăng chưa đáp ứng tiêu chí kiểm duyệt.');if(r===null)return false;this.reason.value=r;return true;">
                                            <?= Csrf::field() ?>
                                            <input type="hidden" name="reason" value="">
                                            <button type="submit" class="btn btn-sm btn-warning px-2 py-1" title="Từ chối tin">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="<?= URL_ROOT ?>/admin/du-an/delete/<?= $p->id ?>" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tin đăng này?');">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
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

        <?php if ($data['totalPages'] > 1): ?>
            <?php
            $pageNumbers = [1, $data['totalPages']];
            for ($page = max(1, $data['currentPage'] - 2); $page <= min($data['totalPages'], $data['currentPage'] + 2); $page++) {
                $pageNumbers[] = $page;
            }
            $pageNumbers = array_values(array_unique($pageNumbers));
            sort($pageNumbers);
            $previousPage = 0;
            $paginationFilters = array_filter($data['filters'], static fn($value) => $value !== '');
            ?>
            <nav aria-label="Phân trang danh sách dự án" class="mt-4">
                <ul class="pagination justify-content-center mb-0">
                    <?php foreach ($pageNumbers as $page): ?>
                        <?php if ($previousPage > 0 && $page > $previousPage + 1): ?>
                            <li class="page-item disabled"><span class="page-link">…</span></li>
                        <?php endif; ?>
                        <?php $pageUrl = URL_ROOT . '/admin/du-an?' . http_build_query(array_merge($paginationFilters, ['page' => $page])); ?>
                        <li class="page-item <?= $page === $data['currentPage'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($pageUrl) ?>" <?= $page === $data['currentPage'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                        </li>
                        <?php $previousPage = $page; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- ===== CUSTOM ADMIN CONFIRM MODAL ===== -->
<style>
#adminConfirmOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    animation: overlayFadeIn 0.25s ease;
}
#adminConfirmOverlay.show {
    display: flex;
}
@keyframes overlayFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

#adminConfirmBox {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    padding: 35px 30px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    position: relative;
    animation: boxPopUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes boxPopUp {
    from { opacity: 0; transform: scale(0.85) translateY(20px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

#adminConfirmBox .icon-wrapper {
    width: 72px;
    height: 72px;
    background: #dcfce7;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #22c55e;
    font-size: 32px;
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0.1);
    animation: pulseGreen 2s infinite;
}
@keyframes pulseGreen {
    0%, 100% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0.1), 0 0 0 20px rgba(34, 197, 94, 0.05); }
    50%      { box-shadow: 0 0 0 15px rgba(34, 197, 94, 0.15), 0 0 0 30px rgba(34, 197, 94, 0.02); }
}

#adminConfirmBox h4 {
    color: #1e293b;
    font-weight: 700;
    margin-bottom: 10px;
}

#adminConfirmBox p {
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 25px;
    line-height: 1.5;
}

#adminConfirmBox .btn-row {
    display: flex;
    gap: 12px;
}

#adminConfirmBox .btn-cancel {
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 12px;
    padding: 12px 0;
    font-weight: 600;
    flex: 1;
    cursor: pointer;
    transition: all 0.2s;
}
#adminConfirmBox .btn-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
}

#adminConfirmBox .btn-confirm {
    background: #10b981;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 0;
    font-weight: 600;
    flex: 1;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}
#adminConfirmBox .btn-confirm:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}
</style>

<div id="adminConfirmOverlay">
    <div id="adminConfirmBox">
        <div class="icon-wrapper">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <h4>Duyệt Tin Đăng</h4>
        <p>Bạn có chắc chắn muốn duyệt và xuất bản tin đăng này ra trang chủ?</p>
        <div class="btn-row">
            <button class="btn-cancel" onclick="closeAdminConfirm()">Hủy</button>
            <button class="btn-confirm" onclick="submitAdminConfirm()"><i class="fa-solid fa-check me-1"></i> Đồng ý duyệt</button>
        </div>
    </div>
</div>

<script>
let currentFormToSubmit = null;

async function handleApproveConfirm(event, formElement) {
    event.preventDefault();
    const accepted = await AdminDialog.confirm(
        'Tin đăng sẽ được xuất bản và hiển thị công khai trên hệ thống.',
        { title: 'Duyệt tin đăng?', type: 'success', confirmText: 'Đồng ý duyệt' }
    );
    if (accepted) {
        formElement.onsubmit = null;
        formElement.submit();
    }
    return false;
}

function closeAdminConfirm() {
    document.getElementById('adminConfirmOverlay').classList.remove('show');
    currentFormToSubmit = null;
}

function submitAdminConfirm() {
    if (currentFormToSubmit) {
        currentFormToSubmit.onsubmit = null;
        currentFormToSubmit.submit();
    }
}

// Đóng modal khi click ra ngoài
document.getElementById('adminConfirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeAdminConfirm();
});
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
