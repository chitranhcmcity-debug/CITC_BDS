<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar Menu (Left Column) -->
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <!-- Main Content (Right Column) -->
        <div class="col-lg-9 col-md-8">
            
            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><strong>Thành công!</strong> <?= Session::get('success'); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Lỗi!</strong> <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php $quick = $data['analyticsQuick'] ?? ['current'=>[],'changes'=>[]]; ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-line text-primary me-2"></i>Hiệu quả hôm nay</h6>
                <a href="<?= URL_ROOT ?>/nguoi-dung/analytics" class="btn btn-sm btn-outline-primary">Xem Analytics</a>
            </div>
            <div class="row g-2 mb-4">
                <?php foreach ([['views','View'],['calls','Call'],['chats','Chat'],['saves','Save'],['shares','Share']] as [$key,$label]): $change=(float)($quick['changes'][$key]??0); ?>
                <div class="col-xl col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3"><div class="small text-muted fw-semibold"><?= $label ?></div><div class="fs-4 fw-bold"><?= number_format((int)($quick['current'][$key]??0)) ?></div><small class="<?= $change>=0?'text-success':'text-danger' ?>"><i class="fa-solid fa-arrow-<?= $change>=0?'up':'down' ?>"></i> <?= abs($change) ?>%</small></div></div></div>
                <?php endforeach; ?>
                <div class="col-xl col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3"><div class="small text-muted fw-semibold">CTR</div><div class="fs-4 fw-bold text-primary"><?= number_format((float)($quick['current']['ctr']??0),1) ?>%</div><small class="text-muted">Liên hệ / View</small></div></div></div>
            </div>

            <!-- Top Alert Box -->
            <div class="alert alert-secondary border-0 bg-light rounded-3 shadow-sm mb-4" role="alert">
                <div class="row align-items-center">
                    <div class="col-12 small">
                        <p class="mb-2">Tặng <b class="text-danger">100.000 VNĐ</b> vào tài khoản trên TimNhaDat.site khi bạn chia sẻ link website TimNhaDat.site lên Zalo, Facebook.</p>
                        <b class="d-block mb-1">Hướng dẫn cách nhận tiền</b>
                        <p class="mb-1"><b>Bước 1:</b> Truy cập website <b class="text-danger">TimNhaDat.site</b></p>
                        <p class="mb-1"><b>Bước 2:</b> Kéo xuống dưới cùng của website tới nút chia sẻ Zalo, Facebook</p>
                        <p class="mb-1"><b>Bước 3:</b> Chia sẻ công khai website lên Zalo, Facebook</p>
                        <p class="mb-1"><b>Bước 4:</b> Kết bạn zalo với admin: <span class="text-danger">0368180923</span> để gửi link facebook cho admin</p>
                        <p class="mb-0"><b>Bước 5:</b> Gửi sđt hoặc mã thành viên cho admin để được tặng tiền vào tài khoản.</p>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold mb-3">Mua lượt UP TIN</h6>
            <div class="table-responsive shadow-sm rounded-3 mb-5">
                <table class="table table-bordered table-hover text-center align-middle mb-0 bg-white">
                    <thead style="background-color: #17a2b8; color: white;">
                        <tr>
                            <th class="py-3">Số lần UP</th>
                            <th class="py-3">Giá (VNĐ)</th>
                            <th class="py-3">Hạn sử dụng</th>
                            <th class="py-3">Mua lượt UP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $upPackages = $data['upPackages'] ?? [];
                        foreach ($upPackages as $turns => $price):
                        ?>
                        <tr>
                            <td class="fw-semibold"><?= number_format($turns) ?> lượt</td>
                            <td><?= number_format($price) ?> VNĐ</td>
                            <td>30 ngày</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm px-3 rounded-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmBuyModal" 
                                        data-package="<?= $turns ?>" 
                                        data-price="<?= number_format($price) ?>">Mua ngay</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Danh Sách Tin Đăng -->
            <h6 id="danh-sach-tin" class="fw-bold mb-2">DANH SÁCH TIN ĐĂNG CỦA BẠN (<?= $data['totalProjects'] ?> tin)</h6>
            <div class="border-bottom mb-3"></div>
            
            <div class="table-responsive shadow-sm rounded-3 bg-white mb-4">
                <table class="table table-hover table-bordered text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tiêu Đề</th>
                            <th>Giá</th>
                            <th>Diện Tích</th>
                            <th>Ngày Đăng</th>
                            <th>Trạng Thái</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['myProjects'])): ?>
                            <tr><td colspan="6" class="text-muted py-4">Bạn chưa đăng tin nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['myProjects'] as $p): ?>
                            <tr>
                                <td class="text-start">
                                    <strong class="text-primary"><?= htmlspecialchars($p->tieu_de) ?></strong><br>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($p->vi_tri) ?></small>
                                    
                                    <?php if ((int)$p->goi_vip > 0): ?>
                                        <div class="mt-2 d-flex align-items-center flex-wrap gap-2">
                                            <?php
                                            $expiryTime = strtotime($p->ngay_het_han_vip);
                                            $timeRemaining = $expiryTime - time();
                                            if ($timeRemaining <= 0):
                                            ?>
                                                <span class="badge bg-danger text-white"><i class="fa-solid fa-circle-exclamation me-1"></i>Hết hạn VIP <?= $p->goi_vip ?></span>
                                            <?php elseif ($timeRemaining <= 3 * 86400): ?>
                                                <?php
                                                $hours = ceil($timeRemaining / 3600);
                                                $days = ceil($timeRemaining / 86400);
                                                $timeStr = $days > 1 ? "$days ngày" : "$hours giờ";
                                                ?>
                                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass-half me-1"></i>Sắp hết hạn VIP <?= $p->goi_vip ?> (còn <?= $timeStr ?>)</span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i>VIP <?= $p->goi_vip ?> (Hạn đến: <?= date('d/m/Y H:i', $expiryTime) ?>)</span>
                                            <?php endif; ?>
                                            
                                            <button type="button" 
                                                    class="btn btn-danger text-white rounded-1 btn-renew-vip py-0 px-2 shadow-sm d-inline-flex align-items-center" 
                                                    style="font-size: 0.72rem; height: 20px; line-height: 1;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#renewVipModal" 
                                                    data-project-id="<?= $p->id ?>" 
                                                    data-project-title="<?= htmlspecialchars($p->tieu_de) ?>" 
                                                    data-vip-level="<?= $p->goi_vip ?>"
                                                    data-expiry-date="<?= date('d/m/Y H:i', $expiryTime) ?>">
                                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Gia hạn VIP
                                            </button>

                                            <!-- Nút gạt bật/tắt Tự động gia hạn -->
                                            <div class="form-check form-switch ms-1 d-inline-flex align-items-center" title="Tự động gia hạn VIP 7 ngày khi hết hạn nếu số dư còn đủ">
                                                <input class="form-check-input auto-renew-toggle cursor-pointer" 
                                                       type="checkbox" 
                                                       id="autoRenewSwitch_<?= $p->id ?>" 
                                                       data-project-id="<?= $p->id ?>" 
                                                       <?= (isset($p->tu_dong_gia_han_vip) && (int)$p->tu_dong_gia_han_vip === 1) ? 'checked' : '' ?>
                                                       style="width: 2.0em; height: 1.0em; cursor: pointer;">
                                                <label class="form-check-label text-muted ms-1 cursor-pointer" for="autoRenewSwitch_<?= $p->id ?>" style="font-size: 0.7rem; font-weight: 600; user-select: none;">Tự động gia hạn</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-danger fw-bold"><?= number_format((float)$p->gia) ?> đ</td>
                                <td><?= htmlspecialchars($p->dien_tich) ?> m²</td>
                                <td><?= date('d/m/Y', strtotime($p->ngay_tao)) ?></td>
                                <td>
                                    <?php if ($p->trang_thai == 'cho_duyet'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock"></i> Chờ duyệt</span>
                                    <?php elseif ($p->trang_thai == 'xuat_ban'): ?>
                                        <span class="badge bg-success"><i class="fa-solid fa-check"></i> Đã duyệt</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= $p->trang_thai ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= URL_ROOT ?>/nguoi-dung/editPost/<?= $p->id ?>" class="btn btn-sm btn-outline-primary" title="Sửa tin"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    <a href="<?= URL_ROOT ?>/nguoi-dung/postAnalytics/<?= $p->id ?>" class="btn btn-sm btn-outline-success mt-1" title="Xem thống kê"><i class="fa-solid fa-chart-simple"></i> Analytics</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($data['totalPages'] > 1): ?>
                <?php
                $pageNumbers = [1, $data['totalPages']];
                for ($page = max(1, $data['currentPage'] - 2); $page <= min($data['totalPages'], $data['currentPage'] + 2); $page++) {
                    $pageNumbers[] = $page;
                }
                $pageNumbers = array_values(array_unique($pageNumbers));
                sort($pageNumbers);
                $previousPage = 0;
                ?>
                <nav aria-label="Phân trang danh sách tin đăng" class="mb-4">
                    <ul class="pagination justify-content-center mb-0">
                        <?php foreach ($pageNumbers as $page): ?>
                            <?php if ($previousPage > 0 && $page > $previousPage + 1): ?>
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php endif; ?>
                            <li class="page-item <?= $page === $data['currentPage'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= URL_ROOT ?>/nguoi-dung/dashboard?page=<?= $page ?>#danh-sach-tin" <?= $page === $data['currentPage'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                            </li>
                            <?php $previousPage = $page; ?>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <!-- Promotion Table -->
            <div class="p-4 bg-light rounded-3 shadow-sm mt-4">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-gift text-danger me-2"></i>Chương trình khuyến mãi nạp tiền</h6>
                <div class="table-responsive bg-white">
                    <table class="table table-bordered table-hover text-center align-middle mb-0">
                        <thead style="background-color: #17a2b8; color: white;">
                            <tr>
                                <th class="py-3">Số tiền nạp (đ)</th>
                                <th class="py-3">Khuyến mãi(%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Từ <b class="text-danger">50.000 đ</b> đến <b class="text-danger">100.000 đ</b></td>
                                <td><b>20 %</b></td>
                            </tr>
                            <tr>
                                <td>Từ <b class="text-danger">100.000 đ</b> đến <b class="text-danger">200.000 đ</b></td>
                                <td><b>50 %</b></td>
                            </tr>
                            <tr>
                                <td>Từ <b class="text-danger">200.000 đ</b> đến <b class="text-danger">500.000 đ</b></td>
                                <td><b>100 %</b></td>
                            </tr>
                            <tr>
                                <td>Từ <b class="text-danger">500.000 đ</b> đến <b class="text-danger">1.000.000 đ</b></td>
                                <td><b>150 %</b></td>
                            </tr>
                            <tr>
                                <td>Từ <b class="text-danger">1.000.000 đ</b> đến <b class="text-danger">3.000.000 đ</b></td>
                                <td><b>200 %</b></td>
                            </tr>
                            <tr>
                                <td>Lớn hơn <b class="text-danger">3.000.000 đ</b></td>
                                <td><b>250 %</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 small">
                    <p class="mb-2">=> Xem thêm <a href="#" class="text-decoration-none">Báo giá tin đăng chi tiết</a></p>
                    <p class="mb-0">Nếu bạn cần hỗ trợ thêm vui lòng liên hệ hotline/zalo: <span class="text-danger fw-bold">0368180923</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác nhận mua UP Tin -->
<div class="modal fade" id="confirmBuyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-circle-question me-2"></i>Xác nhận mua lượt UP</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= URL_ROOT ?>/vi-dien-tu/buyUpPackage" method="POST">
                <?= Csrf::field() ?>
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-hand-holding-dollar text-warning mb-3" style="font-size: 3rem;"></i>
                    <h5 class="mb-3">Bạn có chắc chắn muốn mua gói này?</h5>
                    <p class="mb-0 fs-5">Gói: <b id="modalPackageTurns" class="text-danger">0</b> lượt UP</p>
                    <p class="mb-0 fs-5">Giá: <b id="modalPackagePrice" class="text-primary">0</b> VNĐ</p>
                    <input type="hidden" name="package_id" id="modalPackageId" value="">
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger px-4">Xác nhận thanh toán</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$totalViewsForUser = (int)($data['totalViews'] ?? 0);
$discountPercentForUser = (int)($data['discount'] ?? 0);
$userBalance = (int)($data['user']->so_du ?? 0);
?>

<!-- Modal Gia hạn VIP -->
<div class="modal fade" id="renewVipModal" tabindex="-1" aria-labelledby="renewVipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
            <!-- Dynamic header background set via JS based on VIP tier -->
            <div class="modal-header border-0 text-white p-4" id="renewModalHeader" style="background: linear-gradient(135deg, #17a2b8, #117a8b); transition: all 0.3s ease;">
                <h5 class="modal-title fw-bold" id="renewVipModalLabel">
                    <i class="fa-solid fa-gem me-2 animate-bounce"></i>GIA HẠN TIN DĂNG VIP
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="<?= URL_ROOT ?>/nguoi-dung/renewVipPost" method="POST" id="renewVipForm">
                <?= Csrf::field() ?>
                <input type="hidden" name="project_id" id="renewProjectId">
                
                <div class="modal-body p-4">
                    <!-- Project Title and Info -->
                    <div class="mb-3 p-3 bg-light rounded-3 border-start border-4 border-info">
                        <small class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tin đăng đang gia hạn</small>
                        <h6 class="fw-bold mb-2 text-dark" id="renewProjectTitle" style="font-size: 0.95rem; line-height: 1.4;"></h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" id="renewVipLevelBadge"></span>
                            <span class="text-muted small" style="font-size: 0.8rem;"><i class="fa-regular fa-calendar-times me-1"></i>Hạn cũ: <span id="renewExpiryDateText" class="fw-semibold"></span></span>
                        </div>
                    </div>
                    
                    <!-- Duration Packages Selector -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2" style="font-size: 0.9rem;">
                            <i class="fa-solid fa-calendar-days text-info me-1"></i>Chọn thời gian gia hạn:
                        </label>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="w-100 h-100 m-0">
                                    <input type="radio" name="days" value="7" class="card-radio-input d-none" checked>
                                    <div class="card-radio text-center p-3 rounded-3 border cursor-pointer h-100 d-flex flex-column justify-content-center transition-all">
                                        <span class="fw-bold text-dark d-block">7 Ngày</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">Cơ bản</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="w-100 h-100 m-0">
                                    <input type="radio" name="days" value="15" class="card-radio-input d-none">
                                    <div class="card-radio text-center p-3 rounded-3 border cursor-pointer h-100 d-flex flex-column justify-content-center transition-all">
                                        <span class="fw-bold text-dark d-block">15 Ngày</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">Phổ biến</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-4">
                                <label class="w-100 h-100 m-0">
                                    <input type="radio" name="days" value="30" class="card-radio-input d-none">
                                    <div class="card-radio text-center p-3 rounded-3 border cursor-pointer h-100 d-flex flex-column justify-content-center transition-all position-relative">
                                        <span class="badge bg-danger text-white position-absolute top-0 start-50 translate-middle-y px-2 py-1 rounded-pill" style="font-size: 0.65rem; transform: translate(-50%, -50%);">Ưu đãi</span>
                                        <span class="fw-bold text-dark d-block mt-1">30 Ngày</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">Tiết kiệm</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cost Breakdown Details -->
                    <div class="p-3 rounded-3 mb-4" style="background: rgba(23, 162, 184, 0.05); border: 1px dashed rgba(23, 162, 184, 0.3);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Đơn giá ngày:</span>
                            <span class="fw-semibold text-dark small" id="calcDailyPrice">0đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Thành tiền gốc:</span>
                            <span class="fw-semibold text-dark small" id="calcBasePrice">0đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Khấu trừ ưu đãi (<span id="calcDiscountPercent">0</span>%):</span>
                            <span class="text-success fw-semibold small" id="calcDiscountAmount">-0đ</span>
                        </div>
                        <div class="border-top my-2"></div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark">Tổng tiền thanh toán:</span>
                            <span class="fw-bold text-danger fs-5" id="calcFinalPrice">0đ</span>
                        </div>
                        
                        <!-- Account Wallet Status -->
                        <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-2 border">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-wallet text-warning me-2 fs-5"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Số dư ví của bạn</small>
                                    <span class="fw-bold text-dark small"><?= number_format($userBalance) ?> đ</span>
                                </div>
                            </div>
                            <span class="badge" id="walletStatusBadge">Đang kiểm tra...</span>
                        </div>
                    </div>
                    
                    <!-- Alert Message if Insufficient Balance -->
                    <div class="alert alert-danger d-none align-items-center mb-0 border-0 rounded-3" id="insufficientBalanceAlert" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                        <div>
                            Số dư ví không đủ để thanh toán. Vui lòng nạp thêm tiền! 
                            <a href="<?= URL_ROOT ?>/vi-dien-tu" class="alert-link text-decoration-underline ms-1">Nạp tiền ngay <i class="fa-solid fa-arrow-right small"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 bg-light p-3 d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary px-4 py-2 fw-semibold rounded-3 text-dark border-0 shadow-sm" style="background-color: #e2e8f0;" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-info text-white px-4 py-2 fw-semibold rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" id="btnConfirmRenew" style="min-width: 140px; transition: all 0.2s ease;">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận gia hạn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* CSS styles for card-radio interaction */
.card-radio-input:checked + .card-radio {
    border-color: #17a2b8 !important;
    background-color: rgba(23, 162, 184, 0.08) !important;
    box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2) !important;
}
.card-radio {
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
    cursor: pointer;
    user-select: none;
}
.card-radio:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
}
.animate-bounce {
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0.8,0,1,1);
    }
    50% {
        transform: translateY(-25%);
        animation-timing-function: cubic-bezier(0,0,0.2,1);
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Gói mua lượt UP Tin
    var confirmBuyModal = document.getElementById('confirmBuyModal');
    if (confirmBuyModal) {
        confirmBuyModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var turns = button.getAttribute('data-package');
            var price = button.getAttribute('data-price');
            
            document.getElementById('modalPackageTurns').innerText = turns;
            document.getElementById('modalPackagePrice').innerText = price;
            document.getElementById('modalPackageId').value = turns;
        });
    }

    // 2. Gia hạn VIP Tin Đăng
    var renewVipModal = document.getElementById('renewVipModal');
    if (renewVipModal) {
        var userBalance = parseInt('<?= $userBalance ?>') || 0;
        var discountPercent = parseInt('<?= $discountPercentForUser ?>') || 0;
        var bangGia = <?= json_encode($data['vipPrices'] ?? [], JSON_UNESCAPED_UNICODE) ?>;

        var currentVipLevel = 0;
        
        function formatMoney(amount) {
            return amount.toLocaleString('vi-VN') + ' đ';
        }

        function calculateRenewalPrice() {
            var selectedDaysInput = document.querySelector('input[name="days"]:checked');
            var days = selectedDaysInput ? parseInt(selectedDaysInput.value) : 7;
            var dailyPrice = bangGia[currentVipLevel] || 0;
            var basePrice = dailyPrice * days;
            var discountAmount = Math.floor(basePrice * discountPercent / 100);
            var finalPrice = basePrice - discountAmount;

            document.getElementById('calcDailyPrice').innerText = formatMoney(dailyPrice);
            document.getElementById('calcBasePrice').innerText = formatMoney(basePrice);
            document.getElementById('calcDiscountPercent').innerText = discountPercent;
            document.getElementById('calcDiscountAmount').innerText = '-' + formatMoney(discountAmount);
            document.getElementById('calcFinalPrice').innerText = formatMoney(finalPrice);

            var walletStatusBadge = document.getElementById('walletStatusBadge');
            var btnConfirmRenew = document.getElementById('btnConfirmRenew');
            var insufficientBalanceAlert = document.getElementById('insufficientBalanceAlert');

            if (userBalance >= finalPrice) {
                walletStatusBadge.innerText = 'Đủ số dư';
                walletStatusBadge.className = 'badge bg-success text-white';
                insufficientBalanceAlert.classList.add('d-none');
                btnConfirmRenew.removeAttribute('disabled');
                btnConfirmRenew.style.opacity = '1';
                btnConfirmRenew.style.cursor = 'pointer';
            } else {
                walletStatusBadge.innerText = 'Không đủ số dư';
                walletStatusBadge.className = 'badge bg-danger text-white';
                insufficientBalanceAlert.classList.remove('d-none');
                btnConfirmRenew.setAttribute('disabled', 'true');
                btnConfirmRenew.style.opacity = '0.6';
                btnConfirmRenew.style.cursor = 'not-allowed';
            }
        }

        // Add change listeners for radio buttons
        var dayRadios = renewVipModal.querySelectorAll('input[name="days"]');
        dayRadios.forEach(function(radio) {
            radio.addEventListener('change', calculateRenewalPrice);
        });

        renewVipModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var projectId = button.getAttribute('data-project-id');
            var projectTitle = button.getAttribute('data-project-title');
            var vipLevel = parseInt(button.getAttribute('data-vip-level')) || 0;
            var expiryDate = button.getAttribute('data-expiry-date');

            currentVipLevel = vipLevel;
            
            // Populate modal fields
            document.getElementById('renewProjectId').value = projectId;
            document.getElementById('renewProjectTitle').innerText = projectTitle;
            document.getElementById('renewExpiryDateText').innerText = expiryDate;

            // Reset selected radio button to 7 days
            var defaultRadio = renewVipModal.querySelector('input[name="days"][value="7"]');
            if (defaultRadio) {
                defaultRadio.checked = true;
            }

            // Dynamic header gradient and badges based on VIP level
            var renewModalHeader = document.getElementById('renewModalHeader');
            var renewVipLevelBadge = document.getElementById('renewVipLevelBadge');
            
            // Remove previous classes
            renewVipLevelBadge.className = 'badge';

            if (vipLevel === 1) {
                renewModalHeader.style.background = 'linear-gradient(135deg, #2b6cb0, #4299e1)';
                renewVipLevelBadge.classList.add('bg-primary', 'text-white');
                renewVipLevelBadge.innerText = 'VIP 1';
            } else if (vipLevel === 2) {
                renewModalHeader.style.background = 'linear-gradient(135deg, #702459, #b83280)';
                renewVipLevelBadge.classList.add('bg-info', 'text-white');
                renewVipLevelBadge.innerText = 'VIP 2';
            } else if (vipLevel === 3) {
                renewModalHeader.style.background = 'linear-gradient(135deg, #c05621, #dd6b20)';
                renewVipLevelBadge.classList.add('bg-warning', 'text-dark');
                renewVipLevelBadge.innerText = 'VIP 3';
            } else if (vipLevel === 4) {
                renewModalHeader.style.background = 'linear-gradient(135deg, #1a202c, #4a5568)';
                renewVipLevelBadge.classList.add('bg-dark', 'text-white');
                renewVipLevelBadge.innerText = 'VIP 4';
            } else if (vipLevel === 5) {
                renewModalHeader.style.background = 'linear-gradient(135deg, #22543d, #38a169)';
                renewVipLevelBadge.classList.add('bg-success', 'text-white');
                renewVipLevelBadge.innerText = 'VIP 5';
            } else {
                renewModalHeader.style.background = 'linear-gradient(135deg, #4a5568, #718096)';
                renewVipLevelBadge.classList.add('bg-secondary', 'text-white');
                renewVipLevelBadge.innerText = 'VIP ' + vipLevel;
            }

            // Run price calculation
            calculateRenewalPrice();
        });
    }

    // 3. Xử lý bật/tắt Tự động gia hạn VIP bằng AJAX
    var autoRenewToggles = document.querySelectorAll('.auto-renew-toggle');
    autoRenewToggles.forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var projectId = this.getAttribute('data-project-id');
            var isChecked = this.checked ? 1 : 0;
            var self = this;

            self.disabled = true;

            var formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('status', isChecked);
            
            var csrfInput = document.querySelector('input[name="_csrf_token"]');
            if (csrfInput) {
                formData.append('_csrf_token', csrfInput.value);
            }

            fetch('<?= URL_ROOT ?>/nguoi-dung/toggleAutoRenewVip', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                self.disabled = false;
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    self.checked = !isChecked; // Revert state
                    showToast(data.message, 'danger');
                }
            })
            .catch(function(error) {
                self.disabled = false;
                self.checked = !isChecked; // Revert state
                showToast('Không thể kết nối đến máy chủ. Vui lòng thử lại!', 'danger');
                console.error('Error:', error);
            });
        });
    });

    // Hàm hiển thị Toast thông báo nhanh (Micro-animation)
    function showToast(message, type) {
        var toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        var toast = document.createElement('div');
        toast.className = 'alert alert-' + type + ' shadow-lg border-0 rounded-3 p-3 mb-2 animate-slide-in';
        toast.style.minWidth = '280px';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.transition = 'all 0.3s ease';
        toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
        toast.style.color = '#fff';
        
        var icon = type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation';
        toast.innerHTML = '<i class="' + icon + ' me-2 fs-5"></i><div class="fw-semibold small flex-grow-1">' + message + '</div>';
        
        toastContainer.appendChild(toast);

        // Slide-in CSS
        var style = document.getElementById('toastStyle');
        if (!style) {
            style = document.createElement('style');
            style.id = 'toastStyle';
            style.innerHTML = '\
                .animate-slide-in {\
                    animation: slideIn 0.3s forwards;\
                }\
                @keyframes slideIn {\
                    from { transform: translateX(100%); opacity: 0; }\
                    to { transform: translateX(0); opacity: 1; }\
                }\
                .animate-fade-out {\
                    transform: translateX(100%);\
                    opacity: 0;\
                }\
            ';
            document.head.appendChild(style);
        }

        setTimeout(function() {
            toast.classList.add('animate-fade-out');
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, 3500);
    }
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
