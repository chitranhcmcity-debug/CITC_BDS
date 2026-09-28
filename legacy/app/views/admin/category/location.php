<?php require '../app/views/admin/layouts/header.php'; ?>

<section class="content text-start">
    <div class="container-fluid">
        <!-- Message Alerts -->
        <?php if (Session::get('admin_msg')): ?>
            <div class="alert alert-info alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= Session::get('admin_msg'); Session::delete('admin_msg'); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Side: Import & Live Test Hierarchical Dropdowns -->
            <div class="col-lg-4">
                <!-- CSV Import Widget -->
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-file-import text-primary me-2"></i>Nhập Địa Giới HC Từ CSV</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= URL_ROOT ?>/admin/danh-muc/import" enctype="multipart/form-data">
                            <?= Csrf::field() ?>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Loại dữ liệu nhập</label>
                                <select name="type" class="form-select form-select-sm" required>
                                    <option value="provinces">Tỉnh / Thành phố (Provinces)</option>
                                    <option value="districts">Quận / Huyện (Districts)</option>
                                    <option value="wards">Phường / Xã (Wards)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Chọn file CSV</label>
                                <input type="file" name="file" class="form-control form-control-sm" accept=".csv" required>
                                <div class="form-text small text-muted">Định dạng cột CSV: <code>code, name, type</code> (và <code>province_code/district_code</code> làm cột thứ 2 cho Quận/Phường).</div>
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold py-2"><i class="fa-solid fa-upload me-1"></i>Bắt đầu Import</button>
                        </form>
                    </div>
                </div>

                <!-- Hierarchical Location Live Test Dropdowns -->
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-circle-nodes text-primary me-2"></i>Kiểm Tra Liên Kết Ajax (Live Test)</h6>
                    </div>
                    <div class="card-body">
                        <!-- Province select -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Tỉnh / Thành phố</label>
                            <select id="test-province" class="form-select form-select-sm">
                                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            </select>
                        </div>

                        <!-- District select -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Quận / Huyện</label>
                            <select id="test-district" class="form-select form-select-sm" disabled>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                        </div>

                        <!-- Ward select -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary mb-1">Phường / Xã</label>
                            <select id="test-ward" class="form-select form-select-sm" disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Provinces List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex flex-sm-row flex-column justify-content-between align-items-sm-center gap-3">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Danh Sách Tỉnh / Thành Phố</h5>
                            <div class="d-flex gap-2">
                                <a href="<?= URL_ROOT ?>/admin/danh-muc/export?tab=provinces" class="btn btn-sm btn-outline-secondary fw-semibold"><i class="fa-solid fa-file-excel me-1"></i>Xuất Excel</a>
                                <a href="<?= URL_ROOT ?>/admin/danh-muc?tab=categories" class="btn btn-sm btn-light border text-secondary fw-semibold"><i class="fa-solid fa-arrow-left me-1"></i>Quay lại</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search form -->
                        <form method="GET" action="" class="row g-2 mb-3 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tên tỉnh, mã..." value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                                    <?php if (!empty($search)): ?>
                                        <a href="<?= URL_ROOT ?>/admin/danh-muc/location" class="btn btn-light border"><i class="fa-solid fa-rotate-left"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light text-secondary small fw-bold">
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Mã Code</th>
                                        <th>Tên Tỉnh / Thành phố</th>
                                        <th>Phân loại hành chính</th>
                                        <th style="width: 120px; text-align: center;">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($provinces)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted small">Không tìm thấy tỉnh thành nào.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($provinces as $p): ?>
                                            <tr>
                                                <td class="fw-bold">#<?= $p['id'] ?></td>
                                                <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($p['code']) ?></span></td>
                                                <td><strong class="text-dark"><?= htmlspecialchars($p['name']) ?></strong></td>
                                                <td class="text-secondary"><?= htmlspecialchars($p['type'] ?? 'Tỉnh') ?></td>
                                                <td class="text-center">
                                                    <?php if (($p['status'] ?? 'active') === 'active'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success">Hoạt động</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary">Khóa</span>
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
            </div>
        </div>
    </div>
</section>

<!-- Ajax Dropdowns Controller Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provSelect = document.getElementById('test-province');
    const distSelect = document.getElementById('test-district');
    const wardSelect = document.getElementById('test-ward');

    // 1. Tải danh sách tỉnh/thành phố khi trang load
    fetch('<?= URL_ROOT ?>/api/location/province')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.code;
                    opt.innerText = p.name;
                    provSelect.appendChild(opt);
                });
            }
        });

    // 2. Tải quận huyện khi tỉnh thay đổi
    provSelect.addEventListener('change', function() {
        distSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        distSelect.disabled = true;
        wardSelect.disabled = true;

        const val = this.value;
        if (!val) return;

        fetch('<?= URL_ROOT ?>/api/location/district/' + val)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d.code;
                        opt.innerText = d.name;
                        distSelect.appendChild(opt);
                    });
                    distSelect.disabled = false;
                }
            });
    });

    // 3. Tải phường xã khi quận thay đổi
    distSelect.addEventListener('change', function() {
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled = true;

        const val = this.value;
        if (!val) return;

        fetch('<?= URL_ROOT ?>/api/location/ward/' + val)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(w => {
                        const opt = document.createElement('option');
                        opt.value = w.code;
                        opt.innerText = w.name;
                        wardSelect.appendChild(opt);
                    });
                    wardSelect.disabled = false;
                }
            });
    });
});
</script>

<?php require '../app/views/admin/layouts/footer.php'; ?>
