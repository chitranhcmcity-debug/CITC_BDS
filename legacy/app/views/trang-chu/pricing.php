<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-9">
            <h4 class="fw-bold mb-4">Báo giá tin đăng bất động sản</h4>

            <!-- Banner Image -->
            <div class="mb-5 border border-light-subtle rounded-1 p-1">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
                    alt="Báo giá tin đăng" class="img-fluid w-100 rounded-1" style="height: 300px; object-fit: cover;">
            </div>

            <!-- BÁO GIÁ TIN VIP BẤT ĐỘNG SẢN -->
            <h5 class="fw-bold text-info border-bottom border-info pb-2 mb-3 text-uppercase"
                style="color: #17a2b8 !important;">Báo giá tin VIP bất động sản</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered text-center align-middle">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th class="py-3" style="background-color: #888;">Loại tin</th>
                            <th class="py-3" style="background-color: #888;">Phí theo ngày (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-danger fw-bold py-3">VIP 5</td>
                            <td class="text-danger fw-bold py-3">
                                <?= number_format((int)($data['vipPrices'][5] ?? 0), 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr class="bg-light">
                            <td class="text-danger fw-bold py-3">VIP 4</td>
                            <td class="text-danger fw-bold py-3">
                                <?= number_format((int)($data['vipPrices'][4] ?? 0), 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-danger fw-bold py-3">VIP 3</td>
                            <td class="text-danger fw-bold py-3">
                                <?= number_format((int)($data['vipPrices'][3] ?? 0), 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr class="bg-light">
                            <td class="text-danger fw-bold py-3">VIP 2</td>
                            <td class="text-danger fw-bold py-3">
                                <?= number_format((int)($data['vipPrices'][2] ?? 0), 0, ',', '.') ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-danger fw-bold py-3">VIP 1</td>
                            <td class="text-danger fw-bold py-3">
                                <?= number_format((int)($data['vipPrices'][1] ?? 0), 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CHÚ THÍCH VÀ DEMO -->
            <h6 class="fw-bold fst-italic mb-2 text-uppercase">Chú thích và Demo</h6>
            <p class="mb-4">Tin đăng trong danh sách được sắp xếp theo thứ tự ưu tiên:
                <span class="text-danger fw-bold">VIP 5 ➝ VIP 4 ➝ VIP 3 ➝ VIP 2 ➝ VIP 1</span> ➝
                <span class="text-primary">Tin thường</span></p>

            <?php
            // Cau hinh tung cap VIP: [so_thu_tu, ten, mau_vip_badge, anh_bds, mo_ta_chi_tiet, tieu_de_demo, gia, dien_tich, dia_chi]
            $vipLevels = [
                5 => [
                    'img'   => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=400&q=80',
                    'desc'  => 'Vị trí số 1 đầu danh sách — hiển thị nổi bật nhất. Tiêu đề <span class="text-danger fw-bold">đỏ đậm</span>, gắn <i class="fa-solid fa-crown text-warning"></i> trên ảnh và <i class="fa-solid fa-star text-warning"></i> trên tiêu đề. Phù hợp dự án cao cấp, cần tiếp cận khách hàng tối đa.',
                    'title' => 'Biệt thự nghỉ dưỡng 5* – View biển, hồ bơi vô cực, Phú Quốc',
                    'gia'   => '35 tỷ', 'dt' => '450', 'dc' => 'Phú Quốc, Kiên Giang',
                ],
                4 => [
                    'img'   => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=400&q=80',
                    'desc'  => 'Vị trí thứ 2 sau VIP 5. Tiêu đề <span class="text-danger fw-bold">đỏ đậm</span>, gắn <i class="fa-solid fa-crown text-warning"></i> trên ảnh và <i class="fa-solid fa-star text-warning"></i> trên tiêu đề. Hiệu quả cao với dự án căn hộ cao cấp.',
                    'title' => 'Căn hộ Penthouse tầng 35, 3 phòng ngủ, view thành phố, Quận 1',
                    'gia'   => '12 tỷ', 'dt' => '180', 'dc' => 'Quận 1, TP. Hồ Chí Minh',
                ],
                3 => [
                    'img'   => 'https://images.unsplash.com/photo-1600047509782-20d39509f26d?auto=format&fit=crop&w=400&q=80',
                    'desc'  => 'Vị trí thứ 3. Tiêu đề <span class="text-danger fw-bold">đỏ đậm</span>, gắn <i class="fa-solid fa-crown text-warning"></i> trên ảnh và <i class="fa-solid fa-star text-warning"></i> trên tiêu đề. Lựa chọn phổ biến cho nhà phố, liền kề.',
                    'title' => 'Nhà phố 4 tầng mặt tiền 6m, thiết kế hiện đại, Thủ Đức',
                    'gia'   => '8.5 tỷ', 'dt' => '120', 'dc' => 'TP. Thủ Đức, TP. Hồ Chí Minh',
                ],
                2 => [
                    'img'   => 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=400&q=80',
                    'desc'  => 'Vị trí thứ 4. Tiêu đề <span class="text-danger fw-bold">đỏ đậm</span>, gắn <i class="fa-solid fa-crown text-warning"></i> trên ảnh và <i class="fa-solid fa-star text-warning"></i> trên tiêu đề. Phù hợp căn hộ chung cư, nhà riêng tầm trung.',
                    'title' => 'Chính chủ cho thuê nhà mặt phố 5 tầng, mặt tiền 3m, Cầu Giấy',
                    'gia'   => '37 triệu/tháng', 'dt' => '120', 'dc' => 'Cầu Giấy, Hà Nội',
                ],
                1 => [
                    'img'   => 'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?auto=format&fit=crop&w=400&q=80',
                    'desc'  => 'Vị trí thứ 5 — ngay trên tin thường. Tiêu đề <span class="text-danger fw-bold">đỏ đậm</span>, gắn <i class="fa-solid fa-crown text-warning"></i> trên ảnh và <i class="fa-solid fa-star text-warning"></i> trên tiêu đề. Giải pháp tiết kiệm để nổi bật hơn tin thường.',
                    'title' => 'Căn 2 ngủ 65m² giá 1 tỷ 8 nhận nhà ngay tại Eco City, Long Biên',
                    'gia'   => '1.8 tỷ', 'dt' => '65', 'dc' => 'Quận Long Biên, Hà Nội',
                ],
            ];
            ?>

            <?php foreach ($vipLevels as $vipNum => $vip): ?>
            <h6 class="fw-bold text-info border-bottom border-info pb-2 mb-3" style="color:#17a2b8!important;">
                VIP <?= $vipNum ?>
            </h6>
            <p class="mb-3"><?= $vip['desc'] ?></p>

            <!-- Card demo – dùng đúng cấu trúc/style như ngoài trang listing thực tế -->
            <div class="card mb-5 border-warning rounded-0 shadow-sm" style="pointer-events:none;">
                <div class="row g-0">
                    <!-- Cột ảnh + badge VIP -->
                    <div class="col-md-4 position-relative" style="min-height:160px;">
                        <span class="badge position-absolute top-0 start-0 m-2 px-3 py-1 vip-badge">
                            <i class="fa-solid fa-crown me-1"></i> VIP <?= $vipNum ?>
                        </span>
                        <img src="<?= $vip['img'] ?>"
                             class="img-fluid h-100 object-fit-cover w-100 rounded-0"
                             style="min-height:160px;"
                             alt="Demo VIP <?= $vipNum ?>">
                    </div>
                    <!-- Cột nội dung -->
                    <div class="col-md-8">
                        <div class="card-body h-100 d-flex flex-column">
                            <h5 class="card-title text-danger fw-bold">
                                <i class="fa-solid fa-star text-warning"></i>
                                <?= $vip['title'] ?>
                            </h5>
                            <div class="d-flex gap-4 mb-2">
                                <span class="text-danger fw-bold"><?= $vip['gia'] ?></span>
                                <span class="text-danger fw-bold"><?= $vip['dt'] ?> m²</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><i class="fa-solid fa-location-dot"></i> <?= $vip['dc'] ?></span>
                                <span><?= date('d/m/Y') ?></span>
                            </div>
                            <p class="card-text text-muted small mb-3 flex-grow-1"
                               style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                Tin VIP <?= $vipNum ?> được ưu tiên hiển thị tại vị trí <?= $vipNum === 5 ? 'số 1' : "thứ " . (6 - $vipNum) ?> trong danh sách tin đăng.
                                Giúp tin đăng của bạn tiếp cận nhiều khách hàng tiềm năng hơn so với tin thường.
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name=Chu+Nha&background=e74c3c&color=fff&size=35"
                                         class="rounded-circle" width="35" height="35" alt="Avatar">
                                    <span class="small fw-semibold text-dark">Chủ nhà</span>
                                </div>
                                <span class="btn btn-info text-white fw-bold px-3 py-1" style="pointer-events:none;">
                                    <i class="fa-brands fa-whatsapp"></i> 0900.000.00<?= $vipNum ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>


            <!-- CÁC GÓI UP TIN -->
            <h5 class="fw-bold text-info border-bottom border-info pb-2 mb-3 text-uppercase"
                style="color: #17a2b8 !important;">Các gói UP tin (Làm mới tin)</h5>
            <p class="mb-3">UP tin được áp dụng đối với tin thường. Tin thường được sắp xếp theo thứ tự tin mới lên trên
                tin cũ xuống dưới. UP tin giúp đẩy tin lên đầu danh sách tin thường.</p>
            <div class="table-responsive mb-5" style="max-width: 600px;">
                <table class="table table-bordered text-center align-middle">
                    <thead class="text-white" style="background-color: #17a2b8;">
                        <tr>
                            <th class="py-2">Số lần UP</th>
                            <th class="py-2">Giá (VNĐ)</th>
                            <th class="py-2">Hạn sử dụng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>60 lượt</td>
                            <td><?= number_format((int)($data['upPackages'][60] ?? 0), 0, ',', '.') ?> VNĐ</td>
                            <td>30 ngày</td>
                        </tr>
                        <tr class="bg-light">
                            <td>150 lượt</td>
                            <td><?= number_format((int)($data['upPackages'][150] ?? 0), 0, ',', '.') ?> VNĐ</td>
                            <td>30 ngày</td>
                        </tr>
                        <tr>
                            <td>500 lượt</td>
                            <td><?= number_format((int)($data['upPackages'][500] ?? 0), 0, ',', '.') ?> VNĐ</td>
                            <td>30 ngày</td>
                        </tr>
                        <tr class="bg-light">
                            <td>750 lượt</td>
                            <td><?= number_format((int)($data['upPackages'][750] ?? 0), 0, ',', '.') ?> VNĐ</td>
                            <td>30 ngày</td>
                        </tr>
                        <tr>
                            <td>1500 lượt</td>
                            <td><?= number_format((int)($data['upPackages'][1500] ?? 0), 0, ',', '.') ?> VNĐ</td>
                            <td>30 ngày</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CHƯƠNG TRÌNH KHUYẾN MÃI NẠP TIỀN -->
            <h5 class="fw-bold text-info border-bottom border-info pb-2 mb-3 text-uppercase"
                style="color: #17a2b8 !important;">Chương trình khuyến mãi nạp tiền</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered text-center align-middle">
                    <thead class="text-white" style="background-color: #17a2b8;">
                        <tr>
                            <th class="py-2">Số tiền nạp (đ)</th>
                            <th class="py-2">Khuyến mãi(%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Từ <span class="text-danger fw-bold">50.000 đ</span> đến <span
                                    class="text-danger fw-bold">100.000 đ</span></td>
                            <td class="fw-bold"><?= (int)($data['bonusTiers'][1] ?? 0) ?> %</td>
                        </tr>
                        <tr class="bg-light">
                            <td>Từ <span class="text-danger fw-bold">100.000 đ</span> đến <span
                                    class="text-danger fw-bold">200.000 đ</span></td>
                            <td class="fw-bold"><?= (int)($data['bonusTiers'][2] ?? 0) ?> %</td>
                        </tr>
                        <tr>
                            <td>Từ <span class="text-danger fw-bold">200.000 đ</span> đến <span
                                    class="text-danger fw-bold">500.000 đ</span></td>
                            <td class="fw-bold"><?= (int)($data['bonusTiers'][3] ?? 0) ?> %</td>
                        </tr>
                        <tr class="bg-light">
                            <td>Từ <span class="text-danger fw-bold">500.000 đ</span> đến <span
                                    class="text-danger fw-bold">1.000.000 đ</span></td>
                            <td class="fw-bold"><?= (int)($data['bonusTiers'][4] ?? 0) ?> %</td>
                        </tr>
                        <tr>
                            <td>Từ <span class="text-danger fw-bold">1.000.000 đ</span> đến <span
                                    class="text-danger fw-bold">3.000.000 đ</span></td>
                            <td class="fw-bold"><?= (int)($data['bonusTiers'][5] ?? 0) ?> %</td>
                        </tr>
                        <tr class="bg-light">
                            <td>Lớn hơn <span class="text-danger fw-bold">3.000.000 đ</span></td>
                            <td class="fw-bold"><?= (int)($data['bonusTiers'][6] ?? 0) ?> %</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="mb-5">Nếu bạn cần hỗ trợ thêm vui lòng liên hệ hotline/zalo: <span
                    class="text-danger fw-bold"><?= htmlspecialchars((string)($data['layout']['settings']['hotline'] ?? '')) ?></span></p>

        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card border border-primary rounded-0 mb-4 shadow-sm">
                <div class="card-header text-white text-center fw-bold py-2 rounded-0"
                    style="background-color: #0b4e82 !important; border-color: #0b4e82;">
                    Mua bán nhà đất
                </div>
                <div class="card-body bg-light" style="background-color: #f4f6f8 !important;">
                    <div class="row g-2 small">
                        <?php if (!empty($data['cityCounts'])): ?>
                            <?php foreach ($data['cityCounts'] as $city => $count): ?>
                            <div class="col-6 mb-2">
                                <a href="<?= URL_ROOT ?>/du-an/search?q=<?= urlencode($city) ?>"
                                   class="text-decoration-none"
                                   style="color: #4a8bc2;">
                                    <span><?= htmlspecialchars($city) ?></span>
                                    <span class="text-muted" style="font-size:0.75rem;">(<?= $count ?>)</span>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-muted small text-center py-2">Chưa có dữ liệu.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once '../app/views/layouts/footer.php'; ?>
