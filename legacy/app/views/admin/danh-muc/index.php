<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Danh Mục Dự Án / Bài Viết</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= URL_ROOT ?>/admin/danh-muc/create" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Thêm Danh Mục
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã</th>
                        <th>Tên danh mục</th>
                        <th>Phân loại</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['categories'])): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu danh mục nào.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($data['categories'] as $cat): ?>
                        <tr>
                            <td>#<?= $cat->id ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($cat->ten) ?></td>
                            <td>
                                <?php if ($cat->loai == 'du_an'): ?>
                                    <span class="badge bg-primary">Dự án</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tin tức</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cat->trang_thai == 'hoat_dong'): ?>
                                    <span class="badge bg-success">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Khóa</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= URL_ROOT ?>/admin/danh-muc/edit/<?= $cat->id ?>" class="btn btn-sm btn-outline-primary" title="Sửa">
                                    <i class="fa-solid fa-pen-to-square"></i> Sửa
                                </a>
                                <form action="<?= URL_ROOT ?>/admin/danh-muc/delete/<?= $cat->id ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Xóa">
                                        <i class="fa-solid fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>

