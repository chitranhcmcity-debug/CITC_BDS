@include('admin.layouts.header')

<section class="content text-start">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
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
                            <h5 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-building text-primary me-2"></i>Thêm Dự Án Mới</h5>
                            <a href="<?= URL_ROOT ?>/admin/danh-muc/project" class="btn btn-sm btn-light border text-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Quay lại</a>
                        </div>
                    </div>
                    <div class="card-body py-4">
                        <form method="POST" action="<?= URL_ROOT ?>/admin/danh-muc/project?action=store">
                            <?= Csrf::field() ?>

                            <div class="row g-3 mb-3">
                                <!-- Tên dự án -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Tên dự án <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Ví dụ: Vinhomes Ocean Park" required autocomplete="off">
                                </div>
                                <!-- Chủ đầu tư -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Chủ đầu tư</label>
                                    <input type="text" name="investor" class="form-control form-control-sm" placeholder="Ví dụ: Vingroup, Sun Group...">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- Địa chỉ -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Địa chỉ dự án</label>
                                    <input type="text" name="address" class="form-control form-control-sm" placeholder="Ví dụ: Gia Lâm, Hà Nội">
                                </div>
                                <!-- Google Map Link -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Đường dẫn Google Map</label>
                                    <input type="url" name="google_map" class="form-control form-control-sm" placeholder="https://maps.google.com/?q=...">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <!-- Logo URL -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Đường dẫn Logo dự án</label>
                                    <input type="url" name="logo" class="form-control form-control-sm" placeholder="https://images.unsplash.com/...">
                                </div>
                                <!-- Cover Image URL -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Hình ảnh banner dự án (Cover Image)</label>
                                    <input type="url" name="image" class="form-control form-control-sm" placeholder="https://images.unsplash.com/...">
                                </div>
                            </div>

                            <!-- Tiện ích (Facilities Checkboxes) -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Tiện ích nội khu</label>
                                <div class="bg-light p-3 rounded border">
                                    <div class="row">
                                        <?php 
                                        $facSvc = new CategoryService();
                                        $facilities = $facSvc->getFacilities('', false);
                                        if (empty($facilities)):
                                        ?>
                                            <div class="col-12 text-muted small">Chưa có tiện ích nào trong hệ thống. Vui lòng thêm tiện ích ở Tab Tiện Ích trước.</div>
                                        <?php else: ?>
                                            <?php foreach ($facilities as $fac): ?>
                                                <div class="col-md-3 col-6 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="facilities[]" value="<?= htmlspecialchars($fac['name']) ?>" id="fac-<?= $fac['id'] ?>">
                                                        <label class="form-check-label small" for="fac-<?= $fac['id'] ?>">
                                                            <?php if (!empty($fac['icon'])): ?>
                                                                <i class="<?= htmlspecialchars($fac['icon']) ?> text-secondary me-1"></i>
                                                            <?php endif; ?>
                                                            <?= htmlspecialchars($fac['name']) ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Mô tả chi tiết -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary mb-1">Mô tả dự án</label>
                                <textarea name="description" rows="4" class="form-control form-control-sm" placeholder="Nhập một vài thông tin giới thiệu dự án..."></textarea>
                            </div>

                            <div class="row g-3 mb-4">
                                <!-- Thứ tự sắp xếp -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Thứ tự hiển thị</label>
                                    <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                                </div>

                                <!-- Trạng thái -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Trạng thái</label>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="active">Đang hoạt động (Active)</option>
                                        <option value="inactive">Tạm khóa (Inactive)</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4 text-muted opacity-25">

                            <!-- Submit -->
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="reset" class="btn btn-sm btn-light border text-secondary px-3"><i class="fa-solid fa-rotate-left me-1"></i>Reset</button>
                                <button type="submit" class="btn btn-sm btn-primary fw-bold px-4"><i class="fa-solid fa-save me-1"></i>Lưu lại</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('admin.layouts.footer')
