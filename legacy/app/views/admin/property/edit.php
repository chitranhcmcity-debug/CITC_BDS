<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="content-wrapper p-3 bg-light">
    <!-- Nút quay lại và Tiêu đề -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 mb-2">
                    <a href="<?= URL_ROOT ?>/admin/du-an/detail/<?= $post->id ?>" class="text-decoration-none text-secondary small fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại chi tiết tin
                    </a>
                </div>
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1">Chỉnh sửa tin đăng #<?= $post->id ?></h1>
                    <p class="text-muted mb-0 small">Admin cập nhật thông tin kỹ thuật của tin đăng bất động sản.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Biểu mẫu chỉnh sửa -->
    <section class="content text-start">
        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
            <form action="<?= URL_ROOT ?>/admin/du-an/edit/<?= $post->id ?>" method="POST" class="card-body">
                <?= Csrf::field() ?>

                <div class="row g-3">
                    <!-- Tiêu đề & Slug -->
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-secondary mb-1">Tiêu đề tin đăng</label>
                        <input type="text" name="title" id="editTitleInput" class="form-control form-control-sm" value="<?= htmlspecialchars($post->tieu_de) ?>" required onkeyup="generateEditSlug()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Đường dẫn thân thiện (Slug)</label>
                        <input type="text" name="slug" id="editSlugInput" class="form-control form-control-sm" value="<?= htmlspecialchars($post->duong_dan) ?>" required>
                    </div>

                    <!-- Mô tả chi tiết -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-secondary mb-1">Mô tả nội dung bất động sản</label>
                        <textarea name="description" rows="6" class="form-control form-control-sm text-secondary" style="font-size: 0.88rem;" required><?= htmlspecialchars($post->mo_ta) ?></textarea>
                    </div>

                    <!-- Danh mục, Loại hình, Giao dịch -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Danh mục cấp 1</label>
                        <select name="category_id" class="form-select form-select-sm" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat->id ?>" <?= (int)$post->ma_danh_muc === (int)$cat->id ? 'selected' : '' ?>><?= htmlspecialchars($cat->ten) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Loại hình bất động sản</label>
                        <select name="property_type" class="form-select form-select-sm" required>
                            <option value="Căn hộ" <?= $post->loai_bat_dong_san === 'Căn hộ' ? 'selected' : '' ?>>Căn hộ chung cư</option>
                            <option value="Nhà riêng" <?= $post->loai_bat_dong_san === 'Nhà riêng' ? 'selected' : '' ?>>Nhà riêng / Nhà phố</option>
                            <option value="Đất nền" <?= $post->loai_bat_dong_san === 'Đất nền' ? 'selected' : '' ?>>Đất nền phân lô</option>
                            <option value="Shophouse" <?= $post->loai_bat_dong_san === 'Shophouse' ? 'selected' : '' ?>>Shophouse chân đế</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Hình thức giao dịch</label>
                        <select name="transaction_type" class="form-select form-select-sm" required>
                            <option value="ban" <?= $post->loai_giao_dich === 'ban' ? 'selected' : '' ?>>Cần bán</option>
                            <option value="cho_thue" <?= $post->loai_giao_dich === 'cho_thue' ? 'selected' : '' ?>>Cho thuê</option>
                        </select>
                    </div>

                    <!-- Giá & Diện tích -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Giá bán / thuê (Chuỗi mô tả)</label>
                        <input type="text" name="price" class="form-control form-control-sm text-danger fw-bold" value="<?= htmlspecialchars($post->gia) ?>" required>
                        <small class="text-muted d-block mt-0.5">Nhập số hoặc kèm đơn vị (Ví dụ: 1.5 tỷ, 15 triệu/tháng).</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Diện tích (m²)</label>
                        <input type="number" name="area" step="0.1" class="form-control form-control-sm" value="<?= htmlspecialchars($post->dien_tich) ?>" required>
                    </div>

                    <!-- Địa chỉ, Vị trí địa lý -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Tỉnh / Thành phố</label>
                        <input type="text" name="province" class="form-control form-control-sm" value="<?= htmlspecialchars($post->tinh_thanh) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Quận / Huyện</label>
                        <input type="text" name="district" class="form-control form-control-sm" value="<?= htmlspecialchars($post->quan_huyen) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Phường / Xã</label>
                        <input type="text" name="ward" class="form-control form-control-sm" value="<?= htmlspecialchars($post->phuong_xa) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Địa chỉ chi tiết</label>
                        <input type="text" name="address" class="form-control form-control-sm" value="<?= htmlspecialchars($post->dia_chi) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Vị trí chuỗi ghép đầy đủ</label>
                        <input type="text" name="location" class="form-control form-control-sm text-secondary" value="<?= htmlspecialchars($post->vi_tri) ?>" required>
                    </div>

                    <!-- Người liên hệ -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Tên người liên hệ</label>
                        <input type="text" name="contact_name" class="form-control form-control-sm" value="<?= htmlspecialchars($post->nguoi_lien_he) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Số điện thoại liên hệ</label>
                        <input type="text" name="contact_phone" class="form-control form-control-sm" value="<?= htmlspecialchars($post->so_dien_thoai_lien_he) ?>">
                    </div>

                    <!-- Thông số kỹ thuật phụ -->
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Pháp lý giấy tờ</label>
                        <input type="text" name="legal" class="form-control form-control-sm" value="<?= htmlspecialchars($post->phap_ly) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Hướng nhà</label>
                        <input type="text" name="direction" class="form-control form-control-sm" value="<?= htmlspecialchars($post->huong_nha) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Số phòng ngủ</label>
                        <input type="number" name="bedrooms" class="form-control form-control-sm" value="<?= htmlspecialchars($post->so_phong_ngu) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Số phòng WC</label>
                        <input type="number" name="bathrooms" class="form-control form-control-sm" value="<?= htmlspecialchars($post->so_phong_wc) ?>">
                    </div>

                    <!-- Cấu hình VIP & Ngày hiển thị -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Gói VIP</label>
                        <select name="vip_level" class="form-select form-select-sm" required>
                            <option value="0" <?= (int)$post->goi_vip === 0 ? 'selected' : '' ?>>Tin thường</option>
                            <option value="1" <?= (int)$post->goi_vip === 1 ? 'selected' : '' ?>>VIP 1</option>
                            <option value="2" <?= (int)$post->goi_vip === 2 ? 'selected' : '' ?>>VIP 2</option>
                            <option value="3" <?= (int)$post->goi_vip === 3 ? 'selected' : '' ?>>VIP 3</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Hạn hết VIP (YYYY-MM-DD HH:MM:SS)</label>
                        <input type="text" name="vip_expires_at" class="form-control form-control-sm text-secondary" value="<?= htmlspecialchars($post->ngay_het_han_vip ?: '') ?>" placeholder="2026-08-01 12:00:00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Hạn hết hiển thị tin</label>
                        <input type="text" name="expires_at" class="form-control form-control-sm text-secondary" value="<?= htmlspecialchars($post->ngay_het_han ?: '') ?>" placeholder="2026-09-01 12:00:00">
                    </div>

                    <!-- Trạng thái tin đăng -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Trạng thái tin</label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="cho_duyet" <?= $post->trang_thai === 'cho_duyet' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="xuat_ban" <?= $post->trang_thai === 'xuat_ban' ? 'selected' : '' ?>>Đang hiển thị</option>
                            <option value="tu_choi" <?= $post->trang_thai === 'tu_choi' ? 'selected' : '' ?>>Đã từ chối</option>
                            <option value="an" <?= $post->trang_thai === 'an' ? 'selected' : '' ?>>Đã ẩn</option>
                            <option value="khoa" <?= $post->trang_thai === 'khoa' ? 'selected' : '' ?>>Đã khóa</option>
                        </select>
                    </div>

                    <!-- Metadata SEO -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Meta Title</label>
                        <input type="text" name="meta_title" class="form-control form-control-sm" value="<?= htmlspecialchars($post->meta_title) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Meta Description</label>
                        <input type="text" name="meta_description" class="form-control form-control-sm" value="<?= htmlspecialchars($post->meta_description) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control form-control-sm" value="<?= htmlspecialchars($post->meta_keywords) ?>">
                    </div>
                </div>

                <!-- Submit -->
                <div class="border-top pt-3 mt-4 text-end">
                    <button type="submit" class="btn btn-sm btn-primary fw-bold px-4"><i class="fa-solid fa-save me-1"></i>Lưu thông tin thay đổi</button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
function generateEditSlug() {
    const title = document.getElementById('editTitleInput').value;
    let slug = title.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/([^a-z0-9\s-]|_)+/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
    document.getElementById('editSlugInput').value = slug;
}
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
