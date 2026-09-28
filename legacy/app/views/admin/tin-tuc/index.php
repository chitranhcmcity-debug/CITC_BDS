<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản Lý Bài Viết / Tin Tức</h2>
    <a href="<?= URL_ROOT ?>/admin/tin-tuc/create" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Thêm Bài Mới</a>
</div>

<?php Session::flash('admin_msg'); ?>

<div class="card shadow border-0">
    <div class="card-body">
        <form method="GET" action="<?= URL_ROOT ?>/admin/tin-tuc" class="row g-2 align-items-end mb-4">
            <div class="col-xl-4 col-md-12">
                <label for="newsFilterKeyword" class="form-label fw-semibold">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="search" id="newsFilterKeyword" name="keyword" class="form-control"
                           value="<?= htmlspecialchars($data['filters']['keyword']) ?>"
                           placeholder="Nhập ID, tiêu đề hoặc tác giả">
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="newsFilterCategory" class="form-label fw-semibold">Danh mục</label>
                <select id="newsFilterCategory" name="category_id" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($data['categories'] as $category): ?>
                        <option value="<?= $category->id ?>" <?= $data['filters']['category_id'] === (string)$category->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category->ten) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="newsFilterStatus" class="form-label fw-semibold">Trạng thái</label>
                <select id="newsFilterStatus" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="xuat_ban" <?= $data['filters']['status'] === 'xuat_ban' ? 'selected' : '' ?>>Đã xuất bản</option>
                    <option value="nhap" <?= $data['filters']['status'] === 'nhap' ? 'selected' : '' ?>>Bản nháp</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6">
                <label for="newsFilterFeatured" class="form-label fw-semibold">Nổi bật</label>
                <select id="newsFilterFeatured" name="featured" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="1" <?= $data['filters']['featured'] === '1' ? 'selected' : '' ?>>Nổi bật</option>
                    <option value="0" <?= $data['filters']['featured'] === '0' ? 'selected' : '' ?>>Không nổi bật</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i> Lọc
                </button>
                <a href="<?= URL_ROOT ?>/admin/tin-tuc" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>

        <div class="small text-muted mb-2">Tìm thấy <strong><?= count($data['newsList']) ?></strong> bài viết</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">ID</th>
                        <th width="100">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Trạng thái</th>
                        <th>Nổi bật</th>
                        <th>Ngày đăng</th>
                        <th width="150" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['newsList'])): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="fa-solid fa-newspaper me-1"></i> Không tìm thấy bài viết phù hợp.</td></tr>
                    <?php else: ?>
                        <?php foreach($data['newsList'] as $news): ?>
                            <tr>
                                <td><?= $news->id ?></td>
                                <td>
                                    <?php if(!empty($news->anh_thu_nho)): ?>
                                        <img src="<?= img_url($news->anh_thu_nho ?? '') ?>" alt="" class="img-thumbnail" style="width:80px;height:60px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width:80px;height:60px;">No img</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($news->tieu_de) ?></strong><br>
                                    <small class="text-muted"><i class="fa-solid fa-eye me-1"></i> <?= $news->luot_xem ?> lượt xem</small>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($news->ten_danh_muc ?? 'Chưa phân loại') ?></span></td>
                                <td>
                                    <?php if($news->trang_thai == 'xuat_ban'): ?>
                                        <span class="badge bg-success">Đã xuất bản</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Bản nháp (Ẩn)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($news->noi_bat): ?>
                                        <span class="badge bg-danger"><i class="fa-solid fa-star"></i> Nổi bật</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($news->ngay_tao)) ?></td>
                                <td class="text-center">
                                    <a href="<?= URL_ROOT ?>/admin/tin-tuc/edit/<?= $news->id ?>" class="btn btn-sm btn-outline-primary" title="Sửa"><i class="fa-solid fa-pen"></i></a>
                                    <form action="<?= URL_ROOT ?>/admin/tin-tuc/delete/<?= $news->id ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa"><i class="fa-solid fa-trash"></i></button>
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
