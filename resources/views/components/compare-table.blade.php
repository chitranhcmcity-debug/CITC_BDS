<?php
/**
 * Component: Compare Table - Bảng đối sánh chi tiết các bất động sản side-by-side.
 * @var array $items Danh sách từ 1 đến 4 bất động sản cần so sánh
 */
?>
<div class="table-responsive shadow-sm border" style="border-radius: 12px; overflow: hidden; background: #fff;">
    <table class="table table-bordered align-middle mb-0 text-center" style="min-width: 800px; table-layout: fixed;">
        <!-- Cột thuộc tính (trái) + Cột tin đăng (phải) -->
        <colgroup>
            <col style="width: 200px; background: #f8fafc; font-weight: 700; text-align: left;">
            <?php foreach ($items as $item): ?>
                <col style="width: calc((100% - 200px) / <?= count($items) ?>);">
            <?php endforeach; ?>
        </colgroup>
        
        <thead>
            <tr>
                <th class="text-start py-3 text-secondary uppercase fs-7">Thuộc tính</th>
                <?php foreach ($items as $item): 
                    $coverImage = !empty($item->anh_thu_nho) ? URL_ROOT . '/public/uploads/' . $item->anh_thu_nho : URL_ROOT . '/public/images/no-image.png';
                    if (str_starts_with($item->anh_thu_nho ?? '', 'http')) $coverImage = $item->anh_thu_nho;
                ?>
                    <th class="py-3 px-2 text-center position-relative">
                        <!-- Nút xóa nhanh khỏi bảng so sánh -->
                        <a href="<?= URL_ROOT ?>/nguoi-dung/removeCompare/<?= $item->id ?>" class="btn btn-danger btn-xs rounded-circle position-absolute top-2 end-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 24px; height: 24px; padding: 0;" title="Xóa khỏi so sánh">
                            <i class="fa-solid fa-xmark fs-6"></i>
                        </a>
                        
                        <div class="mx-auto mb-2.5 rounded shadow-xs" style="width: 120px; height: 80px; overflow: hidden;">
                            <img src="<?= $coverImage ?>" class="w-100 h-100" style="object-fit: cover;" alt="Image">
                        </div>
                        <a href="<?= URL_ROOT ?>/du-an/detail/<?= $item->duong_dan ?>" class="text-dark fw-bold line-clamp-2 fs-6.5 text-decoration-none hover-primary" style="height: 38px; line-height: 1.25;">
                            <?= htmlspecialchars($item->tieu_de) ?>
                        </a>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        
        <tbody>
            <!-- Giá -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-money-bill-wave text-success me-2"></i>Giá cả</td>
                <?php foreach ($items as $item): 
                    $price = (float)($item->gia ?? 0);
                    $priceStr = 'Thỏa thuận';
                    if ($price > 0) {
                        $priceStr = $price >= 1000000000 
                            ? number_format($price / 1000000000, 2, ',', '.') . ' Tỷ' 
                            : number_format($price / 1000000, 0, ',', '.') . ' Triệu';
                    }
                ?>
                    <td class="text-danger fw-extrabold fs-5"><?= $priceStr ?></td>
                <?php endforeach; ?>
            </tr>
            
            <!-- Diện tích -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-vector-square text-primary me-2"></i>Diện tích</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-bold text-dark fs-5"><?= number_format((float)$item->dien_tich, 1, ',', '.') ?> m²</td>
                <?php endforeach; ?>
            </tr>

            <!-- Loại BĐS -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-house-chimney text-warning me-2"></i>Phân loại</td>
                <?php foreach ($items as $item): ?>
                    <td>
                        <span class="badge bg-secondary mb-1"><?= $item->loai_giao_dich === 'cho_thue' ? 'Cho thuê' : 'Bán' ?></span><br>
                        <span class="small fw-semibold text-secondary"><?= htmlspecialchars($item->loai_bat_dong_san ?? 'Chưa xác định') ?></span>
                    </td>
                <?php endforeach; ?>
            </tr>

            <!-- Địa chỉ -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-location-dot text-danger me-2"></i>Địa chỉ chi tiết</td>
                <?php foreach ($items as $item): ?>
                    <td class="small line-clamp-3 text-start px-3" style="height: 54px;">
                        <?= htmlspecialchars($item->dia_chi . ', ' . $item->phuong_xa . ', ' . $item->quan_huyen . ', ' . $item->tinh_thanh) ?>
                    </td>
                <?php endforeach; ?>
            </tr>

            <!-- Tên dự án -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-city text-info me-2"></i>Thuộc dự án</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-semibold text-dark"><?= htmlspecialchars($item->ten_du_an ?: 'Không có/Nhỏ lẻ') ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Số phòng ngủ -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-bed text-muted me-2"></i>Số phòng ngủ</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-bold"><?= $item->so_phong_ngu ? $item->so_phong_ngu . ' phòng' : 'Chưa cập nhật' ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Số nhà vệ sinh -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-restroom text-muted me-2"></i>Nhà vệ sinh (WC)</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-bold"><?= $item->so_phong_wc ? $item->so_phong_wc . ' phòng' : 'Chưa cập nhật' ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Mặt tiền -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-road text-muted me-2"></i>Độ rộng mặt tiền</td>
                <?php foreach ($items as $item): ?>
                    <td><?= $item->mat_tien ? number_format((float)$item->mat_tien, 1) . ' m' : 'Chưa cập nhật' ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Pháp lý -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-file-contract text-muted me-2"></i>Tình trạng pháp lý</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-medium"><?= htmlspecialchars($item->phap_ly ?: 'Chưa cập nhật') ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Nội thất -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-couch text-muted me-2"></i>Trạng thái nội thất</td>
                <?php foreach ($items as $item): ?>
                    <td class="small text-truncate" title="<?= htmlspecialchars($item->noi_soft ?? $item->noi_that ?? '') ?>">
                        <?= htmlspecialchars($item->noi_soft ?? $item->noi_that ?: 'Chưa cập nhật') ?>
                    </td>
                <?php endforeach; ?>
            </tr>

            <!-- Hướng -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-compass text-muted me-2"></i>Hướng nhà</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-semibold text-dark"><?= htmlspecialchars($item->huong_nha ?: 'Chưa cập nhật') ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Ngày đăng -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-regular fa-calendar-days text-muted me-2"></i>Ngày đăng tin</td>
                <?php foreach ($items as $item): ?>
                    <td><?= date('d/m/Y', strtotime($item->ngay_tao)) ?></td>
                <?php endforeach; ?>
            </tr>

            <!-- Lượt xem -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-regular fa-eye text-muted me-2"></i>Số lượt xem</td>
                <?php foreach ($items as $item): ?>
                    <td class="fw-bold text-secondary"><?= number_format((int)($item->luot_xem ?? 0)) ?> lượt</td>
                <?php endforeach; ?>
            </tr>

            <!-- Người đăng -->
            <tr>
                <td class="text-start fw-bold"><i class="fa-solid fa-user-tie text-muted me-2"></i>Người liên hệ</td>
                <?php foreach ($items as $item): ?>
                    <td class="small">
                        <strong><?= htmlspecialchars($item->nguoi_lien_he ?: 'Chính chủ') ?></strong><br>
                        <?php if(!empty($item->so_dien_thoai_lien_he)): ?>
                            <a href="tel:<?= $item->so_dien_thoai_lien_he ?>" class="text-decoration-none text-success fw-bold">
                                <i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($item->so_dien_thoai_lien_he) ?>
                            </a>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>

            <!-- Nút hành động cuối -->
            <tr>
                <td class="text-start fw-bold">Thao tác</td>
                <?php foreach ($items as $item): ?>
                    <td class="py-3 px-2">
                        <div class="d-flex flex-column gap-2 max-w-200 mx-auto">
                            <a href="<?= URL_ROOT ?>/du-an/detail/<?= $item->duong_dan ?>" class="btn btn-primary btn-sm fw-bold rounded-pill">
                                <i class="fa-solid fa-circle-info me-1"></i>Xem Chi Tiết
                            </a>
                            <a href="<?= URL_ROOT ?>/nguoi-dung/removeCompare/<?= $item->id ?>" class="btn btn-outline-danger btn-sm rounded-pill">
                                <i class="fa-solid fa-trash-can me-1"></i>Xóa So Sánh
                            </a>
                        </div>
                    </td>
                <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
</div>
