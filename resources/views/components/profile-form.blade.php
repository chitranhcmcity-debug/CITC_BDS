<?php
/** @var object $user Thông tin người dùng */
$accountType = $user->loai_tai_khoan ?? 'ca_nhan';
$accountTypes = [
    'ca_nhan' => ['Cá nhân chính chủ', 'fa-user'],
    'moi_gioi' => ['Nhà môi giới chuyên nghiệp', 'fa-user-tie'],
    'cong_ty' => ['Văn phòng/Công ty BĐS', 'fa-building'],
];
[$accountTypeName, $accountTypeIcon] = $accountTypes[$accountType] ?? $accountTypes['ca_nhan'];
$genderLabels = ['nam' => 'Nam', 'nu' => 'Nữ', 'khac' => 'Khác'];
$emptyText = '<span class="profile-display-empty">Chưa cập nhật</span>';
$showText = static function (mixed $value) use ($emptyText): string {
    $value = trim((string) $value);
    return $value !== '' ? htmlspecialchars($value) : $emptyText;
};
$birthDate = !empty($user->ngay_sinh) ? date('d/m/Y', strtotime($user->ngay_sinh)) : '';
?>

<div class="profile-static-view">
    <div class="row g-3 profile-summary-grid">
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Họ và tên</span><span class="profile-display-value"><?= $showText($user->ten ?? '') ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Số điện thoại</span><span class="profile-display-value"><?= $showText($user->dien_thoai ?? '') ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Email</span><span class="profile-display-value d-flex align-items-center gap-2"><?= $showText($user->email ?? '') ?><?php if (!empty($user->email_verified_at)): ?><i class="fa-solid fa-circle-check text-success" title="Đã xác thực"></i><?php endif; ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Loại tài khoản</span><span class="profile-display-value"><i class="fa-solid <?= $accountTypeIcon ?> text-primary me-2"></i><?= htmlspecialchars($accountTypeName) ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Ngày sinh</span><span class="profile-display-value"><?= $showText($birthDate) ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Giới tính</span><span class="profile-display-value"><?= $showText($genderLabels[$user->gioi_tinh ?? ''] ?? '') ?></span></div></div>
        <div class="col-12"><div class="profile-display-item"><span class="profile-display-label">Địa chỉ cư trú</span><span class="profile-display-value"><?= $showText($user->dia_chi ?? '') ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Nghề nghiệp</span><span class="profile-display-value"><?= $showText($user->nghe_nghiep ?? '') ?></span></div></div>
        <div class="col-md-6"><div class="profile-display-item"><span class="profile-display-label">Khu vực hoạt động</span><span class="profile-display-value"><?= $showText($user->khu_vuc_hoat_dong ?? '') ?></span></div></div>
        <div class="col-12"><div class="profile-display-item profile-display-item--bio"><span class="profile-display-label">Giới thiệu bản thân</span><span class="profile-display-value"><?= !empty(trim((string)($user->mo_ta_ca_nhan ?? ''))) ? nl2br(htmlspecialchars($user->mo_ta_ca_nhan)) : $emptyText ?></span></div></div>
    </div>

    <div class="profile-form-footer mt-4">
        <div class="profile-joined-date"><i class="fa-solid fa-calendar-check"></i> Gia nhập ngày <strong><?= date('d/m/Y', strtotime($user->ngay_tao)) ?></strong></div>
        <button type="button" class="btn btn-premium-primary" data-bs-toggle="modal" data-bs-target="#profileEditModal"><i class="fa-solid fa-pen-to-square"></i>Chỉnh sửa thông tin</button>
    </div>
</div>

<div class="modal fade profile-edit-modal" id="profileEditModal" tabindex="-1" aria-labelledby="profileEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="<?= URL_ROOT ?>/nguoi-dung/profile" method="POST" id="profile-form" class="modal-content profile-compact-form">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="profile">
            <div class="modal-header">
                <div><h5 class="modal-title fw-bold" id="profileEditModalLabel"><i class="fa-solid fa-user-pen me-2 text-primary"></i>Chỉnh sửa thông tin cá nhân</h5><p class="text-muted small mb-0 mt-1">Cập nhật thông tin và nhấn “Lưu thay đổi” khi hoàn tất.</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 profile-form-grid">
                    <div class="col-md-6"><label class="form-label fw-bold text-secondary">Họ và tên <span class="text-danger">*</span></label><input type="text" class="form-control premium-input" name="ten" value="<?= htmlspecialchars($user->ten ?? '') ?>" required minlength="2" maxlength="100"></div>
                    <div class="col-md-6"><label class="form-label fw-bold text-secondary">Số điện thoại</label><input type="tel" class="form-control premium-input" name="dien_thoai" value="<?= htmlspecialchars($user->dien_thoai ?? '') ?>" placeholder="Ví dụ: 0368108923" pattern="[0-9]{10,11}"></div>
                    <div class="col-md-6"><label class="form-label fw-bold text-secondary">Ngày sinh</label><input type="text" class="form-control premium-input" name="ngay_sinh" data-date-input value="<?= htmlspecialchars($birthDate) ?>" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="bday" maxlength="10" pattern="(?:0[1-9]|[12][0-9]|3[01])\/(?:0[1-9]|1[0-2])\/\d{4}" title="Nhập ngày theo định dạng dd/mm/yyyy"></div>
                    <div class="col-md-6"><label class="form-label fw-bold text-secondary">Giới tính</label><select class="form-select premium-input" name="gioi_tinh"><option value="">Chọn giới tính</option><option value="nam" <?= ($user->gioi_tinh ?? '') === 'nam' ? 'selected' : '' ?>>Nam</option><option value="nu" <?= ($user->gioi_tinh ?? '') === 'nu' ? 'selected' : '' ?>>Nữ</option><option value="khac" <?= ($user->gioi_tinh ?? '') === 'khac' ? 'selected' : '' ?>>Khác</option></select></div>
                    <div class="col-12"><label class="form-label fw-bold text-secondary">Địa chỉ cư trú</label><input type="text" class="form-control premium-input" name="dia_chi" value="<?= htmlspecialchars($user->dia_chi ?? '') ?>" placeholder="Ví dụ: Cầu Giấy, Hà Nội"></div>
                    <div class="col-md-6"><label class="form-label fw-bold text-secondary">Nghề nghiệp</label><input type="text" class="form-control premium-input" name="nghe_nghiep" value="<?= htmlspecialchars($user->nghe_nghiep ?? '') ?>" placeholder="Ví dụ: Kỹ sư, Nhà đầu tư,..."></div>
                    <div class="col-md-6"><label class="form-label fw-bold text-secondary">Khu vực hoạt động</label><input type="text" class="form-control premium-input" name="khu_vuc_hoat_dong" value="<?= htmlspecialchars($user->khu_vuc_hoat_dong ?? '') ?>" placeholder="Ví dụ: Nam Từ Liêm, Hoài Đức,..."></div>
                    <div class="col-12"><label class="form-label fw-bold text-secondary">Giới thiệu bản thân</label><textarea class="form-control premium-input" name="mo_ta_ca_nhan" rows="4" placeholder="Giới thiệu ngắn về kinh nghiệm và lĩnh vực hoạt động của bạn..."><?= htmlspecialchars($user->mo_ta_ca_nhan ?? '') ?></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-premium-primary"><i class="fa-solid fa-floppy-disk"></i>Lưu thay đổi</button></div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('profileEditModal');
    if (modal && modal.parentElement !== document.body) document.body.appendChild(modal);
    const input = document.querySelector('[data-date-input]');
    if (!input) return;
    const formatDate = value => {
        const digits = value.replace(/\D/g, '').slice(0, 8);
        if (digits.length <= 2) return digits;
        if (digits.length <= 4) return `${digits.slice(0, 2)}/${digits.slice(2)}`;
        return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
    };
    const validateDate = () => {
        input.setCustomValidity('');
        if (input.value === '') return;
        const match = input.value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!match) { input.setCustomValidity('Vui lòng nhập ngày sinh theo định dạng dd/mm/yyyy.'); return; }
        const [, day, month, year] = match.map(Number);
        const date = new Date(year, month - 1, day);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const isRealDate = date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
        if (!isRealDate || date > today) input.setCustomValidity('Ngày sinh không hợp lệ hoặc nằm trong tương lai.');
    };
    input.addEventListener('input', () => { input.value = formatDate(input.value); validateDate(); });
    input.addEventListener('blur', validateDate);
})();
</script>
