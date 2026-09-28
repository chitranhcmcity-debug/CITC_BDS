<?php
/**
 * Component: Property Card - Thẻ hiển thị tin đăng bất động sản.
 * @var object $post Đối tượng tin đăng bất động sản (từ CSDL)
 */

$vipClass = '';
$vipBadge = '';
switch ((int)($post->goi_vip ?? 0)) {
    case 4:
        $vipClass = 'border-danger vip-special';
        $vipBadge = '<span class="badge bg-danger text-uppercase px-2.5 py-1 fw-bold fs-7 shadow-sm"><i class="fa-solid fa-crown me-1"></i>VIP Đặc Biệt</span>';
        break;
    case 3:
        $vipClass = 'border-warning vip-3';
        $vipBadge = '<span class="badge bg-warning text-dark text-uppercase px-2.5 py-1 fw-bold fs-7 shadow-sm"><i class="fa-solid fa-star me-1"></i>VIP 3</span>';
        break;
    case 2:
        $vipClass = 'border-primary vip-2';
        $vipBadge = '<span class="badge bg-primary text-uppercase px-2.5 py-1 fw-bold fs-7 shadow-sm">VIP 2</span>';
        break;
    case 1:
        $vipClass = 'border-info vip-1';
        $vipBadge = '<span class="badge bg-info text-dark text-uppercase px-2.5 py-1 fw-bold fs-7">VIP 1</span>';
        break;
}

// Định dạng giá hiển thị
$price = (float)($post->gia ?? 0);
$priceStr = 'Thỏa thuận';
if ($price > 0) {
    if ($price >= 1000000000) {
        $priceStr = number_format($price / 1000000000, 2, ',', '.') . ' Tỷ';
    } else {
        $priceStr = number_format($price / 1000000, 0, ',', '.') . ' Triệu';
    }
}

// Format ngày đăng tin
$dateStr = '';
if (!empty($post->ngay_luu)) {
    $dateStr = date('d/m/Y', strtotime($post->ngay_luu));
} elseif (!empty($post->ngay_tao)) {
    $dateStr = date('d/m/Y', strtotime($post->ngay_tao));
}

// Ảnh đại diện
$coverImage = !empty($post->anh_thu_nho) ? URL_ROOT . '/public/uploads/' . $post->anh_thu_nho : URL_ROOT . '/public/images/no-image.png';
if (str_starts_with($post->anh_thu_nho ?? '', 'http')) {
    $coverImage = $post->anh_thu_nho;
}
?>
<div class="card property-card h-100 shadow-sm border <?= $vipClass ?>" style="border-radius: 12px; overflow: hidden; transition: all 0.3s ease;">
    <!-- Ảnh và Badge VIP -->
    <div class="position-relative" style="height: 180px; overflow: hidden;">
        <img src="<?= $coverImage ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.5s ease;" alt="<?= htmlspecialchars($post->tieu_de) ?>" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
        
        <div class="position-absolute top-2.5 start-2.5 d-flex flex-column gap-1.5">
            <?= $vipBadge ?>
            <span class="badge bg-dark bg-opacity-75 text-white px-2.5 py-1 text-uppercase fw-semibold" style="font-size: 0.72rem;">
                <?= $post->loai_giao_dich === 'cho_thue' ? 'Cho thuê' : 'Bán' ?>
            </span>
        </div>
        
        <!-- Nút xóa nhanh khỏi yêu thích -->
        <button type="button" class="btn btn-light btn-sm rounded-circle position-absolute top-2.5 end-2.5 shadow-sm d-flex align-items-center justify-content-center btn-remove-fav" data-id="<?= $post->id ?>" style="width: 32px; height: 32px; color: #dc3545;" title="Bỏ lưu">
            <i class="fa-solid fa-heart-circle-xmark fs-5"></i>
        </button>
    </div>

    <!-- Nội dung thẻ -->
    <div class="card-body p-3.5 d-flex flex-column justify-content-between">
        <div>
            <!-- Giá & Diện tích -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-danger fw-extrabold fs-5"><?= $priceStr ?></span>
                <span class="text-secondary small fw-semibold"><i class="fa-solid fa-vector-square me-1"></i><?= number_format((float)$post->dien_tich, 1, ',', '.') ?> m²</span>
            </div>

            <!-- Tiêu đề -->
            <h5 class="card-title fw-bold text-dark fs-6 line-clamp-2 mb-2.5" style="height: 38px; line-height: 1.25;">
                <a href="<?= URL_ROOT ?>/du-an/detail/<?= $post->duong_dan ?>" class="text-dark text-decoration-none hover-primary">
                    <?= htmlspecialchars($post->tieu_de) ?>
                </a>
            </h5>

            <!-- Vị trí -->
            <p class="card-text text-muted small mb-2 text-truncate">
                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                <?= htmlspecialchars($post->dia_chi . ', ' . $post->phuong_xa . ', ' . $post->quan_huyen . ', ' . $post->tinh_thanh) ?>
            </p>
        </div>

        <div class="mt-2 pt-2 border-top">
            <!-- Thông tin người đăng & Ngày đăng -->
            <div class="d-flex justify-content-between align-items-center mb-3 text-secondary" style="font-size: 0.78rem;">
                <span><i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($post->ten_nguoi_dung ?? 'Chính chủ') ?></span>
                <span><i class="fa-regular fa-calendar me-1"></i>Lưu: <?= $dateStr ?></span>
            </div>

            <!-- Các nút hành động chính -->
            <div class="d-flex gap-2">
                <a href="<?= URL_ROOT ?>/du-an/detail/<?= $post->duong_dan ?>" class="btn btn-outline-primary btn-sm flex-fill fw-bold rounded-pill shadow-xs">
                    <i class="fa-solid fa-circle-info me-1"></i>Xem
                </a>
                
                <?php if(!empty($post->so_dien_thoai_lien_he)): ?>
                    <a href="tel:<?= $post->so_dien_thoai_lien_he ?>" class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;" title="Gọi điện">
                        <i class="fa-solid fa-phone fs-6"></i>
                    </a>
                <?php endif; ?>

                <?php if(!empty($post->ma_nguoi_dung)): ?>
                    <a href="<?= URL_ROOT ?>/chat?receiver=<?= $post->ma_nguoi_dung ?>" class="btn btn-info btn-sm text-white rounded-circle d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px;" title="Nhắn tin Chat">
                        <i class="fa-solid fa-comment-dots fs-6"></i>
                    </a>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center btn-share-link" data-url="<?= URL_ROOT ?>/du-an/detail/<?= $post->duong_dan ?>" style="width: 32px; height: 32px;" title="Chia sẻ">
                    <i class="fa-solid fa-share-nodes fs-6"></i>
                </button>
            </div>
        </div>
    </div>
</div>
