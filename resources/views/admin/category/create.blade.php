@include('admin.layouts.header')

<section class="content text-start">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Alerts -->
                <?php if (Session::get('admin_msg')): ?>
                    <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                        <?= Session::get('admin_msg'); Session::delete('admin_msg'); ?>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-plus text-primary me-2"></i>Thêm Mới Danh Mục (<?= htmlspecialchars($type) ?>)</h5>
                            <a href="<?= URL_ROOT ?>/admin/danh-muc?tab=<?= $type ?>" class="btn btn-sm btn-light border text-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Quay lại</a>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <form method="POST" action="<?= URL_ROOT ?>/admin/danh-muc/store">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

                            <!-- Tên hiển thị -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Tên hiển thị <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Ví dụ: Căn hộ chung cư, Cho thuê..." required autocomplete="off">
                            </div>

                            <!-- Mã Code / Shortcode -->
                            <?php if ($type !== 'facilities'): ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Mã hệ thống (Code)</label>
                                    <input type="text" name="code" class="form-control" placeholder="Ví dụ: apartment, rent, dong_bac...">
                                    <div class="form-text small text-muted">Dùng cho định danh hệ thống/truy vấn API.</div>
                                </div>
                            <?php endif; ?>

                            <!-- Icon (dành cho categories & facilities) -->
                            <?php if ($type === 'categories' || $type === 'facilities'): ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Biểu tượng Icon (FontAwesome)</label>
                                    <input type="text" name="icon" class="form-control" placeholder="Ví dụ: fa-solid fa-building">
                                    <div class="form-text small text-muted">Xem biểu tượng tại <a href="https://fontawesome.com/search" target="_blank" class="text-primary text-decoration-none">FontAwesome</a>.</div>
                                </div>
                            <?php endif; ?>

                            <!-- Màu Sắc (dành cho categories) -->
                            <?php if ($type === 'categories'): ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Màu sắc định danh</label>
                                    <input type="color" name="color" class="form-control form-control-color w-100" value="#3b82f6" title="Chọn màu sắc">
                                </div>
                            <?php endif; ?>

                            <!-- Mô tả chi tiết (dành cho categories & transaction_types) -->
                            <?php if ($type === 'categories' || $type === 'transaction_types'): ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Mô tả ngắn</label>
                                    <textarea name="description" rows="3" class="form-control" placeholder="Nhập một vài mô tả giới thiệu về danh mục..."></textarea>
                                </div>
                            <?php endif; ?>

                            <div class="row g-3 mb-4">
                                <!-- Thứ tự sắp xếp -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Thứ tự hiển thị</label>
                                    <input type="number" name="sort_order" class="form-control" value="0">
                                </div>

                                <!-- Trạng thái -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Trạng thái hoạt động</label>
                                    <select name="status" class="form-select">
                                        <option value="active">Hoạt động (Active)</option>
                                        <option value="inactive">Tạm khóa (Inactive)</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <!-- Submit -->
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="reset" class="btn btn-light border text-secondary"><i class="fa-solid fa-rotate-left me-1"></i>Reset</button>
                                <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fa-solid fa-save me-1"></i>Lưu lại</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('admin.layouts.footer')
