@include('layouts.header')

<div class="container py-4">
    <div class="row">
        @include('nguoi-dung.sidebar')

        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-arrow-up me-2"></i>Mua Gói Lượt UP Tin</h4>
                <a href="<?= URL_ROOT ?>/vi-dien-tu" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại Ví
                </a>
            </div>

            <?php if (Session::flash('wallet_error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= Session::flash('wallet_error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (Session::flash('wallet_success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                    <i class="fa-solid fa-circle-check me-2"></i><?= Session::flash('wallet_success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px; background-color: #fafafa;">
                <div class="d-flex align-items-center">
                    <span class="fs-1 text-primary me-3"><i class="fa-solid fa-circle-info"></i></span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Lượt UP tin dùng để làm gì?</h6>
                        <p class="text-secondary small mb-0">Khi áp dụng lượt UP cho tin đăng của bạn, tin đăng sẽ nhảy lên vị trí đầu tiên của danh sách tìm kiếm/danh mục bài viết tương ứng, tăng lượt tiếp cận khách hàng gấp 10 lần.</p>
                    </div>
                </div>
            </div>

            <!-- Packages grid -->
            <div class="row g-4">
                <?php
                $upPackages = $data['upPackages'] ?? [];
                
                // Cấu trúc gói: [ token_count => price ]
                // Sắp xếp tăng dần theo lượt UP
                ksort($upPackages);

                $packageDetails = [
                    60 => ['name' => 'Gói Đồng', 'desc' => 'Thích hợp cho người bán cá nhân', 'icon' => 'fa-solid fa-gem text-muted'],
                    150 => ['name' => 'Gói Bạc', 'desc' => 'Giải pháp tối ưu cho môi giới nhỏ', 'icon' => 'fa-solid fa-medal text-secondary'],
                    500 => ['name' => 'Gói Vàng', 'desc' => 'Được tin dùng bởi đại lý chuyên nghiệp', 'icon' => 'fa-solid fa-crown text-warning'],
                    750 => ['name' => 'Gói Bạch Kim', 'desc' => 'Chi phí tiết kiệm, độ bao phủ cao', 'icon' => 'fa-solid fa-award text-info'],
                    1500 => ['name' => 'Gói Kim Cương', 'desc' => 'Phục vụ tối đa nhu cầu bán hàng quy mô lớn', 'icon' => 'fa-solid fa-gem text-primary'],
                ];
                ?>

                <?php foreach ($upPackages as $turns => $price): ?>
                    <?php 
                    $details = $packageDetails[$turns] ?? ['name' => 'Gói Lượt UP', 'desc' => 'Tăng tốc bán bất động sản', 'icon' => 'fa-solid fa-rocket'];
                    $discountText = '';
                    if ($turns == 150) $discountText = 'Tiết kiệm 20%';
                    if ($turns == 500) $discountText = 'Tiết kiệm 28%';
                    if ($turns == 750) $discountText = 'Tiết kiệm 32%';
                    if ($turns == 1500) $discountText = 'Tiết kiệm 40%';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm text-center p-4 hover-shadow" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                            <div class="mb-3">
                                <span class="fs-2 p-3 bg-light rounded-circle inline-block" style="width:70px; height:70px; display:inline-flex; align-items:center; justify-content:center;">
                                    <i class="<?= $details['icon'] ?>"></i>
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1"><?= $details['name'] ?></h5>
                            <p class="text-secondary small mb-3"><?= $details['desc'] ?></p>

                            <div class="py-3 border-top border-bottom border-light mb-3 bg-light-subtle rounded">
                                <span class="display-6 fw-extrabold text-primary"><?= number_format($turns) ?></span>
                                <span class="text-secondary fw-semibold">Lượt UP</span>
                            </div>

                            <div class="mb-4">
                                <h4 class="fw-bold text-danger mb-0"><?= number_format($price) ?> đ</h4>
                                <?php if ($discountText): ?>
                                    <span class="badge bg-danger-subtle text-danger mt-2 fw-semibold px-2 py-1.5"><?= $discountText ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">&nbsp;</span>
                                <?php endif; ?>
                            </div>

                            <form action="<?= URL_ROOT ?>/vi-dien-tu/buyUpPackage" method="POST" onsubmit="return confirm('Xác nhận mua gói <?= $details['name'] ?> với giá <?= number_format($price) ?>đ?')">
                                <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                <input type="hidden" name="package_id" value="<?= $turns ?>">
                                <button type="submit" class="btn btn-outline-primary w-100 py-2.5 fw-bold" style="border-radius: 10px;">
                                    <i class="fa-solid fa-cart-shopping me-1"></i>Mua ngay
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important;
}
</style>

@include('layouts.footer')
