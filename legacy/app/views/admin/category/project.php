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

        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-sm-row flex-column justify-content-between align-items-sm-center gap-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-building text-primary me-2"></i>Danh Sách Dự Án Bất Động Sản</h5>
                    <div class="d-flex gap-2">
                        <a href="<?= URL_ROOT ?>/admin/danh-muc?tab=categories" class="btn btn-sm btn-light border text-secondary fw-semibold"><i class="fa-solid fa-arrow-left me-1"></i>Quay lại</a>
                        <a href="<?= URL_ROOT ?>/admin/danh-muc/project?action=create" class="btn btn-sm btn-primary fw-bold"><i class="fa-solid fa-plus me-1"></i>Thêm Dự Án</a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Search filter -->
                <form method="GET" action="" class="row g-2 mb-3 align-items-center">
                    <input type="hidden" name="action" value="list">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tên, chủ đầu tư, địa chỉ..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= URL_ROOT ?>/admin/danh-muc/project" class="btn btn-light border"><i class="fa-solid fa-rotate-left"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-secondary small fw-bold">
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Tên dự án</th>
                                <th>Chủ đầu tư</th>
                                <th>Địa chỉ</th>
                                <th>Tiện ích</th>
                                <th style="width: 100px; text-align: center;">Trạng thái</th>
                                <th class="text-end pe-3" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">Không tìm thấy dự án nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $item['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($item['logo'])): ?>
                                                    <img src="<?= htmlspecialchars($item['logo']) ?>" class="rounded border" style="width: 32px; height: 32px; object-fit: contain;">
                                                <?php else: ?>
                                                    <div class="bg-light text-muted border rounded d-flex align-items-center justify-content-center text-center small" style="width: 32px; height: 32px;"><i class="fa-regular fa-building"></i></div>
                                                <?php endif; ?>
                                                <strong class="text-dark"><?= htmlspecialchars($item['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($item['investor'] ?? '-') ?></td>
                                        <td class="text-secondary"><?= htmlspecialchars($item['address'] ?? '-') ?></td>
                                        <td>
                                            <?php 
                                            // Tiện ích giải mã JSON
                                            $facs = json_decode($item['facilities'], true) ?: explode(',', $item['facilities'] ?? '');
                                            $facs = array_filter(array_map('trim', $facs));
                                            if (!empty($facs)): 
                                                foreach (array_slice($facs, 0, 3) as $f):
                                            ?>
                                                <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($f) ?></span>
                                            <?php 
                                                endforeach;
                                                if (count($facs) > 3):
                                            ?>
                                                <span class="badge bg-light text-secondary border">+<?= count($facs) - 3 ?></span>
                                            <?php 
                                                endif;
                                            else: 
                                            ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($item['status'] ?? 'active') === 'active'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success">Đang hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary">Tạm khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="<?= URL_ROOT ?>/admin/danh-muc/project?action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                                <form method="POST" action="<?= URL_ROOT ?>/admin/danh-muc/project?action=delete&id=<?= $item['id'] ?>" onsubmit="return confirm('Xóa dự án này?')">
                                                    <?= Csrf::field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
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
</section>

<?php require '../app/views/admin/layouts/footer.php'; ?>
