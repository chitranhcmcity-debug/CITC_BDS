@include('admin.layouts.header')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Sửa Danh Mục</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= URL_ROOT ?>/admin/danh-muc" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <?php Session::flash('admin_msg'); ?>

                <form action="<?= URL_ROOT ?>/admin/danh-muc/update/<?= $data['category']->id ?>" method="POST">
                    <?= Csrf::field() ?>
                    <div class="mb-3">
                        <label for="ten" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten" name="ten" required value="<?= htmlspecialchars($data['category']->ten) ?>" placeholder="Nhập tên danh mục...">
                    </div>

                    <div class="mb-3">
                        <label for="loai" class="form-label fw-bold">Phân loại</label>
                        <select class="form-select" id="loai" name="loai">
                            <option value="du_an" <?= ($data['category']->loai == 'du_an') ? 'selected' : '' ?>>Dự án</option>
                            <option value="bai_viet" <?= ($data['category']->loai == 'bai_viet') ? 'selected' : '' ?>>Tin tức / Bài viết</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="trang_thai" class="form-label fw-bold">Trạng thái</label>
                        <select class="form-select" id="trang_thai" name="trang_thai">
                            <option value="hoat_dong" <?= ($data['category']->trang_thai == 'hoat_dong') ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="ngung_hoat_dong" <?= ($data['category']->trang_thai == 'ngung_hoat_dong') ? 'selected' : '' ?>>Khóa (Ngưng hoạt động)</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-1"></i> Cập Nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')
