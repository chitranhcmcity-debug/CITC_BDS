<?php require '../app/views/admin/layouts/header.php'; ?>

<!-- Content Section -->
<section class="content text-start">
    <div class="container-fluid">
        <!-- Message Alerts -->
        <?php if (Session::get('admin_msg')): ?>
            <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= Session::get('admin_msg'); Session::delete('admin_msg'); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Stats Widget -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info rounded shadow-sm p-3 text-white">
                    <div class="inner">
                        <h3><?= count($this->catSvc->getCategories()) ?></h3>
                        <p class="mb-0">Số danh mục</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3"><i class="fa-solid fa-tags"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success rounded shadow-sm p-3 text-white">
                    <div class="inner">
                        <h3><?= count($this->projSvc->getProjects()) ?></h3>
                        <p class="mb-0">Số dự án</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3"><i class="fa-solid fa-building"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning rounded shadow-sm p-3 text-white">
                    <div class="inner">
                        <h3><?= count($this->locSvc->getProvinces()) ?></h3>
                        <p class="mb-0">Tỉnh / Thành phố</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3"><i class="fa-solid fa-map-location-dot"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger rounded shadow-sm p-3 text-white">
                    <div class="inner">
                        <h3><?= count($this->catSvc->getFacilities()) ?></h3>
                        <p class="mb-0">Số tiện ích</p>
                    </div>
                    <div class="icon fs-1 opacity-25 position-absolute end-0 bottom-0 me-3"><i class="fa-solid fa-dumbbell"></i></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-sm-row flex-column justify-content-between align-items-sm-center gap-3">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs card-header-tabs border-bottom-0" style="font-size: 0.88rem;">
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'categories' ? 'active font-weight-bold text-primary border-primary' : 'text-secondary' ?>" href="<?= URL_ROOT ?>/admin/danh-muc?tab=categories">
                                <i class="fa-solid fa-tags me-1"></i>Danh Mục / Loại BĐS
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'transaction_types' ? 'active font-weight-bold text-primary border-primary' : 'text-secondary' ?>" href="<?= URL_ROOT ?>/admin/danh-muc?tab=transaction_types">
                                <i class="fa-solid fa-arrow-right-arrow-left me-1"></i>Loại Giao Dịch
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'facilities' ? 'active font-weight-bold text-primary border-primary' : 'text-secondary' ?>" href="<?= URL_ROOT ?>/admin/danh-muc?tab=facilities">
                                <i class="fa-solid fa-dumbbell me-1"></i>Tiện Ích
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'directions' ? 'active font-weight-bold text-primary border-primary' : 'text-secondary' ?>" href="<?= URL_ROOT ?>/admin/danh-muc?tab=directions">
                                <i class="fa-solid fa-compass me-1"></i>Hướng Nhà
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === 'legal_types' ? 'active font-weight-bold text-primary border-primary' : 'text-secondary' ?>" href="<?= URL_ROOT ?>/admin/danh-muc?tab=legal_types">
                                <i class="fa-solid fa-file-invoice me-1"></i>Pháp Lý
                            </a>
                        </li>
                    </ul>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-end align-items-center">
                        <a href="<?= URL_ROOT ?>/admin/danh-muc/create?type=<?= $tab ?>" class="btn btn-sm btn-primary fw-bold"><i class="fa-solid fa-plus me-1"></i>Thêm Mới</a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Search & Filters -->
                <form method="GET" action="" class="row g-2 mb-3 align-items-center">
                    <input type="hidden" name="tab" value="<?= $tab ?>">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Tìm kiếm tên, slug..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <?php if (!empty($search)): ?>
                                <a href="<?= URL_ROOT ?>/admin/danh-muc?tab=<?= $tab ?>" class="btn btn-light border"><i class="fa-solid fa-rotate-left"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;" id="sortable-table">
                        <thead class="table-light text-secondary small fw-bold">
                            <tr>
                                <th style="width: 50px;"></th>
                                <th style="width: 70px;">ID</th>
                                <th>Tên</th>
                                <th>Slug</th>
                                <?php if ($tab === 'categories' || $tab === 'transaction_types'): ?>
                                    <th>Mã code</th>
                                <?php endif; ?>
                                <?php if ($tab === 'categories' || $tab === 'facilities'): ?>
                                    <th style="width: 100px; text-align: center;">Biểu tượng</th>
                                <?php endif; ?>
                                <th style="width: 100px; text-align: center;">Sắp xếp</th>
                                <th style="width: 150px; text-align: center;">Trạng thái</th>
                                <th class="text-end pe-3" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="sort-items">
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted small">Không tìm thấy dữ liệu phù hợp.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $item): ?>
                                    <tr data-id="<?= $item['id'] ?>">
                                        <td class="text-muted handle" style="cursor: grab;"><i class="fa-solid fa-grip-vertical"></i></td>
                                        <td class="fw-bold">#<?= $item['id'] ?></td>
                                        <td>
                                            <?php if (!empty($item['color'])): ?>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($item['color']) ?>; width: 12px; height: 12px; display: inline-block; border-radius: 50%; vertical-align: middle; margin-right: 6px;"></span>
                                            <?php endif; ?>
                                            <strong class="text-dark"><?= htmlspecialchars($item['name']) ?></strong>
                                        </td>
                                        <td class="text-secondary"><?= htmlspecialchars($item['slug']) ?></td>
                                        <?php if ($tab === 'categories' || $tab === 'transaction_types'): ?>
                                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($item['code'] ?? 'N/A') ?></span></td>
                                        <?php endif; ?>
                                        <?php if ($tab === 'categories' || $tab === 'facilities'): ?>
                                            <td class="text-center fs-5 text-secondary">
                                                <?php if (!empty($item['icon'])): ?>
                                                    <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td class="text-center fw-bold text-secondary"><?= $item['sort_order'] ?></td>
                                        <td class="text-center">
                                            <form method="POST" action="<?= URL_ROOT ?>/admin/danh-muc/status/<?= $item['id'] ?>?type=<?= $tab ?>">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="status" value="<?= ($item['status'] ?? 'active') === 'active' ? 'inactive' : 'active' ?>">
                                                <button type="submit" class="btn btn-sm btn-link text-decoration-none py-0">
                                                    <?php if (($item['status'] ?? 'active') === 'active'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success"><i class="fa-solid fa-circle-check me-1"></i>Hoạt động</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary"><i class="fa-solid fa-circle-minus me-1"></i>Tạm khóa</span>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <a href="<?= URL_ROOT ?>/admin/danh-muc/edit/<?= $item['id'] ?>?type=<?= $tab ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                                <form method="POST" action="<?= URL_ROOT ?>/admin/danh-muc/delete/<?= $item['id'] ?>?type=<?= $tab ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này? Hủy bỏ nếu đang có tin đăng sử dụng!')">
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

<!-- Drag and Drop Sort Integration -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('sort-items');
    if (!el) return;

    Sortable.create(el, {
        handle: '.handle',
        animation: 150,
        onEnd: function() {
            let orders = {};
            document.querySelectorAll('#sort-items tr').forEach((tr, index) => {
                orders[tr.getAttribute('data-id')] = index + 1;
            });

            // Gửi Ajax sắp xếp
            const formData = new FormData();
            formData.append('csrf_token', '<?= Csrf::token() ?>');
            for (let id in orders) {
                formData.append('orders[' + id + ']', orders[id]);
            }

            fetch('<?= URL_ROOT ?>/admin/danh-muc/sort?type=<?= $tab ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Cập nhật lại số thứ tự hiển thị trên bảng
                    document.querySelectorAll('#sort-items tr').forEach((tr, index) => {
                        tr.children[tr.children.length - 3].innerText = index + 1;
                    });
                }
            });
        }
    });
});
</script>

<?php require '../app/views/admin/layouts/footer.php'; ?>
