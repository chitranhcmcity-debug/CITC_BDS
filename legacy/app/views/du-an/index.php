<?php require_once '../app/views/layouts/header.php'; ?>

<style>
.listing-type-tabs {
    display: inline-flex;
    gap: 6px;
    padding: 6px;
    border-radius: 14px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.listing-type-tab {
    min-width: 168px;
    padding: 12px 20px;
    border-radius: 10px;
    color: #111827;
    text-align: center;
    text-decoration: none;
    font-weight: 800;
    transition: color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.listing-type-tab:hover,
.listing-type-tab:focus {
    color: #198754;
    background: rgba(25, 135, 84, 0.08);
    transform: translateY(-1px);
}

.listing-type-tab.is-active {
    color: #ffffff;
    background: linear-gradient(135deg, #15803d, #16a34a);
    box-shadow: 0 10px 22px rgba(22, 163, 74, 0.22);
}

.listing-type-tab.is-active:hover,
.listing-type-tab.is-active:focus {
    color: #ffffff;
    transform: translateY(-1px);
}

@media (max-width: 575.98px) {
    .listing-type-tabs {
        display: grid;
        grid-template-columns: 1fr;
        width: 100%;
    }

    .listing-type-tab {
        min-width: 0;
        width: 100%;
    }
}
</style>

<!-- Advanced Search Bar -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <!-- Tabs -->
        <?php 
        $isRent = isset($_GET['type']) && $_GET['type'] == 'rent'; 
        $titleMain = $isRent ? 'Cho thuê nhà đất' : 'Mua bán nhà đất';
        $titleSub = $isRent ? 'Cho thuê nhà đất' : 'Bán nhà đất';
        ?>
        <div class="listing-type-tabs mb-3" role="tablist" aria-label="Loại tin bất động sản">
            <a href="?type=sale" class="listing-type-tab <?= !$isRent ? 'is-active' : '' ?>">Nhà đất bán</a>
            <a href="?type=rent" class="listing-type-tab <?= $isRent ? 'is-active' : '' ?>">Nhà đất cho thuê</a>
        </div>
        
        <form action="<?= URL_ROOT ?>/du-an/search" method="GET">
        <!-- Search input -->
        <div class="input-group mb-2 shadow-sm">
            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
            <input type="text" name="q" class="form-control border-start-0 py-2" placeholder="Nhập từ khóa, khu vực tìm kiếm. Ví dụ: Tp. HCM">
        </div>
        <small class="text-muted mb-3 d-block fst-italic">Hoặc chọn thuộc tính tìm kiếm dưới đây</small>

        <!-- Filters -->
        <div class="row row-cols-2 row-cols-md-5 g-2">
            <div class="col">
                <select name="category" class="form-select text-muted">
                    <option value="">Danh mục BĐS</option>
                    <option value="sale">Nhà đất bán</option>
                    <option value="rent">Nhà đất cho thuê</option>
                </select>
            </div>
            <div class="col"><select id="province" name="province" class="form-select text-muted"><option value="">Tỉnh/Tp</option></select></div>
            <div class="col"><select id="district" name="district" class="form-select text-muted" disabled><option value="">Quận / Huyện</option></select></div>
            <div class="col"><select id="ward" name="ward" class="form-select text-muted" disabled><option value="">Xã / Phường</option></select></div>
            <div class="col"><select name="project" class="form-select text-muted"><option value="">Chọn dự án</option></select></div>
            
            <div class="col">
                <select name="area" class="form-select text-muted">
                    <option value="">Diện tích</option>
                    <option value="Dưới 30 m²">Dưới 30 m²</option>
                    <option value="30 - 50 m²">30 - 50 m²</option>
                    <option value="50 - 80 m²">50 - 80 m²</option>
                    <option value="80 - 100 m²">80 - 100 m²</option>
                    <option value="100 - 150 m²">100 - 150 m²</option>
                    <option value="150 - 200 m²">150 - 200 m²</option>
                    <option value="200 - 250 m²">200 - 250 m²</option>
                    <option value="250 - 300 m²">250 - 300 m²</option>
                    <option value="300 - 500 m²">300 - 500 m²</option>
                    <option value="Trên 500 m²">Trên 500 m²</option>
                </select>
            </div>
            <div class="col">
                <select name="price" class="form-select text-muted">
                    <option value="">Mức giá</option>
                    <option value="Dưới 500 triệu">Dưới 500 triệu</option>
                    <option value="500 - 800 triệu">500 - 800 triệu</option>
                    <option value="800 triệu - 1 tỷ">800 triệu - 1 tỷ</option>
                    <option value="1 - 2 tỷ">1 - 2 tỷ</option>
                    <option value="2 - 3 tỷ">2 - 3 tỷ</option>
                    <option value="3 - 5 tỷ">3 - 5 tỷ</option>
                    <option value="5 - 7 tỷ">5 - 7 tỷ</option>
                    <option value="7 - 10 tỷ">7 - 10 tỷ</option>
                    <option value="10 - 20 tỷ">10 - 20 tỷ</option>
                    <option value="20 - 30 tỷ">20 - 30 tỷ</option>
                    <option value="Trên 30 tỷ">Trên 30 tỷ</option>
                    <option value="Thỏa thuận">Thỏa thuận</option>
                </select>
            </div>
            <div class="col">
                <select name="rooms" class="form-select text-muted">
                    <option value="">Số phòng ngủ</option>
                    <option value="1">1 phòng</option>
                    <option value="2">2 phòng</option>
                    <option value="3">3 phòng</option>
                    <option value="4">4 phòng</option>
                    <option value="5+">5 phòng trở lên</option>
                </select>
            </div>
            <div class="col">
                <select name="direction" class="form-select text-muted">
                    <option value="">Hướng</option>
                    <option value="Đông">Đông</option>
                    <option value="Tây">Tây</option>
                    <option value="Nam">Nam</option>
                    <option value="Bắc">Bắc</option>
                    <option value="Đông Bắc">Đông Bắc</option>
                    <option value="Tây Bắc">Tây Bắc</option>
                    <option value="Tây Nam">Tây Nam</option>
                    <option value="Đông Nam">Đông Nam</option>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-danger w-100 fw-bold d-flex align-items-center justify-content-center"><i class="fa-solid fa-magnifying-glass me-2"></i> Tìm kiếm</button>
            </div>
        </div>
        </form>
    </div>
</section>

<!-- Listings Body -->
<section class="py-4">
    <div class="container">
        <div class="row">
            <!-- Left Column: Properties list -->
            <div class="col-lg-8">
                <h3 class="fw-bold mb-1"><?= $titleMain ?></h3>
                <p class="text-muted mb-4"><?= $titleSub ?></p>

                <!-- Property Cards List -->
                <!-- Property Cards List -->
                <?php if(empty($data['projects'])): ?>
                    <div class="alert alert-info border-0 rounded-0 shadow-sm text-center py-4">
                        <i class="fa-solid fa-circle-info fa-2x mb-3 text-info"></i>
                        <p class="mb-0">Hiện chưa có bất động sản nào trong mục này.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($data['projects'] as $project): ?>
                    <?php $cardBorder = ($project->active_vip > 0) ? 'border-warning' : 'border-0'; ?>
                    <div class="card property-card listing-card mb-4 <?= $cardBorder ?> shadow-sm">
                        <div class="row g-0 h-100">
                            <div class="col-md-4 position-relative listing-card__media">
                                <?php if($project->active_vip > 0): ?>
                                    <span class="badge position-absolute top-0 start-0 m-2 px-3 py-1 vip-badge">
                                        <i class="fa-solid fa-crown me-1"></i> VIP <?= $project->active_vip ?>
                                    </span>
                                <?php endif; ?>
                                <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>" class="d-block h-100">
                                    <img src="<?= img_url($project->anh_thu_nho ?? '') ?>" class="img-fluid h-100 object-fit-cover w-100 listing-card__image" alt="<?= htmlspecialchars($project->tieu_de) ?>">
                                </a>
                            </div>
                            <div class="col-md-8">
                                <div class="card-body h-100 d-flex flex-column">
                                    <h5 class="card-title text-danger fw-bold">
                                        <?php if($project->active_vip > 0): ?>
                                            <i class="fa-solid fa-star text-warning"></i> 
                                        <?php endif; ?>
                                        <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>" class="text-danger text-decoration-none"><?= htmlspecialchars($project->tieu_de) ?></a>
                                    </h5>
                                    <div class="d-flex gap-4 mb-2">
                                        <?php if(!empty($project->gia)): ?>
                                            <span class="text-danger fw-bold"><?= htmlspecialchars($project->gia) ?></span>
                                        <?php endif; ?>
                                        <?php if(!empty($project->dien_tich)): ?>
                                            <span class="text-danger fw-bold"><?= htmlspecialchars($project->dien_tich) ?> m²</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mb-2">
                                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($project->vi_tri ?? 'Đang cập nhật') ?></span>
                                        <span><?= date('d/m/Y', strtotime($project->ngay_tao)) ?></span>
                                    </div>
                                    <p class="card-text text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($project->mo_ta ?: $project->tieu_de) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= !empty($project->anh_dai_dien) ? URL_ROOT . '/public/uploads/avatars/' . $project->anh_dai_dien : 'https://ui-avatars.com/api/?name=' . urlencode($project->ten_nguoi_dung ?? 'NguoiDung') . '&background=random' ?>" class="rounded-circle" width="35" height="35" alt="Avatar">
                                            <span class="small fw-semibold text-dark"><?= htmlspecialchars($project->ten_nguoi_dung ?? 'Người dùng') ?></span>
                                        </div>
                                        <?php $phone = $project->dien_thoai ?? '0900000000'; ?>
                                        <a href="tel:<?= $phone ?>" class="btn btn-info text-white fw-bold px-3 py-1" data-track-post="<?= (int)$project->id ?>" data-track-type="call"><i class="fa-brands fa-whatsapp"></i> <?= $phone ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Pagination thực tế + hiệu ứng -->
                <?php
                $tongTrang = $data['tongTrang'] ?? 1;
                $trangHien = $data['trangHien'] ?? 1;
                $tongSoTin = $data['tongSoTin'] ?? 0;
                $perPage   = $data['perPage']   ?? 10;
                $loai      = $data['loai']       ?? 'sale';

                // Xây dựng base URL giữ nguyên các query string lọc hiện tại.
                $queryParams = $_GET;
                unset($queryParams['url'], $queryParams['page']);
                if (empty($queryParams['type']) && empty($data['isSearch'])) {
                    $queryParams['type'] = $loai;
                }
                $baseQuery = http_build_query(array_filter($queryParams, static fn($value) => $value !== '' && $value !== null));

                if ($tongTrang > 1):
                    // Hiển thị tối đa 5 trang xung quanh trang hiện tại
                    $delta      = 2;
                    $rangeStart = max(1, $trangHien - $delta);
                    $rangeEnd   = min($tongTrang, $trangHien + $delta);
                    // Luôn hiện trang đầu và cuối
                    if ($rangeStart > 2)  $showDotLeft  = true;
                    if ($rangeEnd   < $tongTrang - 1) $showDotRight = true;
                ?>
                <nav aria-label="Phân trang" class="mt-4" id="pagination-nav">
                    <ul class="pagination pagination-sm justify-content-center flex-wrap gap-1">

                        <!-- Previous -->
                        <li class="page-item <?= $trangHien <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill px-3" 
                               href="?<?= $baseQuery ?>&page=<?= $trangHien - 1 ?>"
                               aria-label="Trang trước">
                                <i class="fa-solid fa-chevron-left"></i>
                            </a>
                        </li>

                        <!-- Trang đầu -->
                        <?php if ($rangeStart > 1): ?>
                            <li class="page-item <?= $trangHien === 1 ? 'active' : '' ?>">
                                <a class="page-link rounded-pill px-3 page-btn" 
                                   href="?<?= $baseQuery ?>&page=1"
                                   data-page="1">1</a>
                            </li>
                            <?php if (!empty($showDotLeft)): ?>
                                <li class="page-item disabled"><span class="page-link border-0 bg-transparent">…</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Dải trang giữa -->
                        <?php for ($p = $rangeStart; $p <= $rangeEnd; $p++): ?>
                            <li class="page-item <?= $p === $trangHien ? 'active' : '' ?>">
                                <a class="page-link rounded-pill px-3 page-btn <?= $p === $trangHien ? 'fw-bold' : '' ?>"
                                   href="?<?= $baseQuery ?>&page=<?= $p ?>"
                                   data-page="<?= $p ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Trang cuối -->
                        <?php if ($rangeEnd < $tongTrang): ?>
                            <?php if (!empty($showDotRight)): ?>
                                <li class="page-item disabled"><span class="page-link border-0 bg-transparent">…</span></li>
                            <?php endif; ?>
                            <li class="page-item <?= $trangHien === $tongTrang ? 'active' : '' ?>">
                                <a class="page-link rounded-pill px-3 page-btn"
                                   href="?<?= $baseQuery ?>&page=<?= $tongTrang ?>"
                                   data-page="<?= $tongTrang ?>"><?= $tongTrang ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Next -->
                        <li class="page-item <?= $trangHien >= $tongTrang ? 'disabled' : '' ?>">
                            <a class="page-link rounded-pill px-3"
                               href="?<?= $baseQuery ?>&page=<?= $trangHien + 1 ?>"
                               aria-label="Trang sau">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>

                    <!-- Thông tin phân trang -->
                    <p class="text-center text-muted small mt-2">
                        Hiển thị <?= (($trangHien - 1) * $perPage) + 1 ?>
                        – <?= min($trangHien * $perPage, $tongSoTin) ?>
                        trong tổng số <strong><?= number_format($tongSoTin) ?></strong> tin đăng
                        (Trang <strong><?= $trangHien ?></strong> / <?= $tongTrang ?>)
                    </p>
                </nav>

                <style>
                /* Pagination animation */
                #pagination-nav .page-link {
                    transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
                    font-weight: 500;
                }
                #pagination-nav .page-item.active .page-link {
                    background: linear-gradient(135deg, #e73539, #c0202a);
                    border-color: #e73539;
                    box-shadow: 0 4px 12px rgba(231,53,57,0.35);
                    transform: scale(1.08);
                }
                #pagination-nav .page-link:hover:not(.active) {
                    background: #fff1f1;
                    color: #e73539;
                    border-color: #e73539;
                    transform: translateY(-2px);
                    box-shadow: 0 3px 8px rgba(231,53,57,0.2);
                }
                /* Entry animation cho trang active */
                @keyframes pagePop {
                    0%   { transform: scale(0.6); opacity: 0; }
                    70%  { transform: scale(1.15); }
                    100% { transform: scale(1.08); opacity: 1; }
                }
                #pagination-nav .page-item.active .page-link {
                    animation: pagePop 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;
                }
                </style>

                <script>
                // Hiệu ứng loading khi click chuyển trang
                document.querySelectorAll('#pagination-nav .page-btn').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        var page = parseInt(this.dataset.page);
                        var current = <?= $trangHien ?>;
                        if (page === current) { e.preventDefault(); return; }
                        // Flash hiệu ứng trên nút được click
                        this.style.transform = 'scale(0.92)';
                        this.style.opacity   = '0.7';
                    });
                });
                </script>
                <?php endif; ?>

            </div>


            <!-- Right Column: Sidebar -->
            <div class="col-lg-4">
                <div class="card border border-primary rounded-0 mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white text-center fw-bold py-2 rounded-0">
                        <?= $titleMain ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            <?php 
                            if (!empty($data['cityCounts'])):
                                foreach($data['cityCounts'] as $city => $count):
                            ?>
                            <div class="col-6">
                                <a href="<?= URL_ROOT ?>/du-an/search?province=<?= urlencode($city) ?>&type=<?= urlencode($data['loai'] ?? 'sale') ?>" class="text-decoration-none text-primary"><span class="hover-text-dark"><?= htmlspecialchars($city) ?></span> <span class="text-muted">(<?= $count ?>)</span></a>
                            </div>
                            <?php 
                                endforeach; 
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../app/views/layouts/footer.php'; ?>

<!-- Script gọi API Tỉnh thành Việt Nam -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    let locationData = [];

    // Gọi API lấy danh sách Tỉnh/Thành phố
    $.ajax({
        url: 'https://provinces.open-api.vn/api/?depth=3',
        method: 'GET',
        success: function(data) {
            locationData = data;
            let provinceHtml = '<option value="">Tỉnh/Tp</option>';
            data.forEach(function(province) {
                provinceHtml += `<option value="${province.name}" data-code="${province.code}">${province.name}</option>`;
            });
            $('#province').html(provinceHtml);
        }
    });

    // Khi chọn Tỉnh/Thành phố
    $('#province').change(function() {
        let provinceCode = $(this).find(':selected').data('code');
        let districtHtml = '<option value="">Quận / Huyện</option>';
        let wardHtml = '<option value="">Xã / Phường</option>';
        
        $('#district').prop('disabled', true).html(districtHtml);
        $('#ward').prop('disabled', true).html(wardHtml);

        if (provinceCode) {
            let province = locationData.find(p => p.code == provinceCode);
            if (province && province.districts) {
                province.districts.forEach(function(district) {
                    districtHtml += `<option value="${district.name}" data-code="${district.code}">${district.name}</option>`;
                });
                $('#district').prop('disabled', false).html(districtHtml);
            }
        }
    });

    // Khi chọn Quận/Huyện
    $('#district').change(function() {
        let provinceCode = $('#province').find(':selected').data('code');
        let districtCode = $(this).find(':selected').data('code');
        let wardHtml = '<option value="">Xã / Phường</option>';
        
        $('#ward').prop('disabled', true).html(wardHtml);

        if (provinceCode && districtCode) {
            let province = locationData.find(p => p.code == provinceCode);
            if (province && province.districts) {
                let district = province.districts.find(d => d.code == districtCode);
                if (district && district.wards) {
                    district.wards.forEach(function(ward) {
                        wardHtml += `<option value="${ward.name}">${ward.name}</option>`;
                    });
                    $('#ward').prop('disabled', false).html(wardHtml);
                }
            }
        }
    });
});
</script>
