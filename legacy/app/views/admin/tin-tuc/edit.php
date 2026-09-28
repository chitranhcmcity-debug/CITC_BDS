<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Sửa Bài Viết</h2>
    <a href="<?= URL_ROOT ?>/admin/tin-tuc" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Quay Lại</a>
</div>

<div class="card shadow border-0">
    <div class="card-body p-4">
        <form action="<?= URL_ROOT ?>/admin/tin-tuc/edit/<?= $data['news']->id ?>" method="POST" enctype="multipart/form-data">
            <?= Csrf::field() ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" name="tieu_de" class="form-control" value="<?= htmlspecialchars($data['news']->tieu_de) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tóm tắt</label>
                        <textarea name="tom_tat" class="form-control" rows="3"><?= htmlspecialchars($data['news']->tom_tat ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nội dung chi tiết <span class="text-danger">*</span></label>
                        <textarea name="noi_dung" class="form-control" rows="15" required id="editor"><?= htmlspecialchars($data['news']->noi_dung) ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3 bg-light border-0">
                        <div class="card-body">
                            <h5 class="card-title fs-6 fw-bold mb-3">Tùy chọn hiển thị</h5>
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <select name="trang_thai" class="form-select">
                                    <option value="xuat_ban" <?= $data['news']->trang_thai == 'xuat_ban' ? 'selected' : '' ?>>Xuất bản (Hiển thị)</option>
                                    <option value="nhap" <?= $data['news']->trang_thai == 'nhap' ? 'selected' : '' ?>>Bản nháp (Ẩn)</option>
                                </select>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="noi_bat" value="1" class="form-check-input" id="noiBatCheck" <?= $data['news']->noi_bat ? 'checked' : '' ?>>
                                <label class="form-check-label text-danger fw-bold" for="noiBatCheck">Đánh dấu bài Nổi Bật</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select name="ma_danh_muc" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach($data['categories'] as $cat): ?>
                                        <option value="<?= $cat->id ?>" <?= $data['news']->ma_danh_muc == $cat->id ? 'selected' : '' ?>><?= $cat->ten ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title fs-6 fw-bold mb-3">Ảnh đại diện</h5>
                            <input type="file" name="anh_thu_nho" class="form-control mb-2" accept="image/*" onchange="previewImage(this)">
                            <div class="text-center mt-3">
                                <?php if(!empty($data['news']->anh_thu_nho)): ?>
                                    <img id="preview" src="<?= img_url($data['news']->anh_thu_nho ?? '') ?>" alt="Preview" class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <img id="preview" src="" alt="Preview" class="img-fluid rounded d-none" style="max-height: 200px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fa-solid fa-save me-2"></i> Cập Nhật</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor');
    
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('preview').classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
