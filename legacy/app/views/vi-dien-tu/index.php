<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <h4 class="fw-bold mb-4 border-bottom pb-2">Ví & Thanh Toán</h4>

            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= Session::get('success'); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-primary text-white shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h6 class="text-white-50 text-uppercase fw-semibold mb-2">Số dư hiện tại</h6>
                            <h2 class="display-5 fw-bold mb-3"><?= number_format($data['user']->so_du ?? 0) ?> đ</h2>
                            <button class="btn btn-light fw-bold px-4" data-bs-toggle="modal" data-bs-target="#depositModal">
                                <i class="fa-solid fa-wallet"></i> Nạp tiền ngay
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white shadow-sm border-0 h-100">
                        <div class="card-body p-4">
                            <h6 class="text-white-50 text-uppercase fw-semibold mb-2">Ưu đãi theo View</h6>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-eye fa-2x me-3"></i>
                                <div>
                                    <h4 class="fw-bold mb-0"><?= number_format($data['totalViews']) ?> views</h4>
                                    <small>Tổng lượt xem tin đăng</small>
                                </div>
                            </div>
                            
                            <?php if ($data['discount'] > 0): ?>
                                <div class="badge bg-warning text-dark fs-6 mb-2">
                                    <i class="fa-solid fa-tag"></i> Đang được giảm <?= $data['discount'] ?>% khi mua VIP
                                </div>
                            <?php else: ?>
                                <div class="badge bg-secondary fs-6 mb-2">Chưa có mã giảm giá</div>
                            <?php endif; ?>

                            <?php if ($data['nextGoal'] > 0): ?>
                                <p class="small mb-0 mt-2">
                                    <i class="fa-solid fa-circle-info"></i> Cần thêm <strong><?= number_format($data['nextGoal'] - $data['totalViews']) ?> lượt xem</strong> nữa để nhận mức giảm giá <?= $data['nextDiscount'] ?>%.
                                </p>
                            <?php else: ?>
                                <p class="small mb-0 mt-2">
                                    <i class="fa-solid fa-medal text-warning"></i> Chúc mừng! Bạn đang đạt mức ưu đãi tối đa 15%.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CHƯƠNG TRÌNH KHUYẾN MÃI NẠP TIỀN (từ cài đặt admin) -->
            <?php
            $bonusTiers = $data['bonusTiers'] ?? [];
            $b1 = (int)($bonusTiers[1] ?? 0);
            $b2 = (int)($bonusTiers[2] ?? 0);
            $b3 = (int)($bonusTiers[3] ?? 0);
            $b4 = (int)($bonusTiers[4] ?? 0);
            $b5 = (int)($bonusTiers[5] ?? 0);
            $b6 = (int)($bonusTiers[6] ?? 0);
            $hotline = (string)($data['layout']['settings']['hotline'] ?? '0368180923');
            $bonusTiers = [
                ['label' => 'Từ <b class="text-danger">50.000đ</b> đến <b class="text-danger">100.000đ</b>',  'pct' => $b1],
                ['label' => 'Từ <b class="text-danger">100.000đ</b> đến <b class="text-danger">200.000đ</b>', 'pct' => $b2],
                ['label' => 'Từ <b class="text-danger">200.000đ</b> đến <b class="text-danger">500.000đ</b>', 'pct' => $b3],
                ['label' => 'Từ <b class="text-danger">500.000đ</b> đến <b class="text-danger">1.000.000đ</b>','pct' => $b4],
                ['label' => 'Từ <b class="text-danger">1.000.000đ</b> đến <b class="text-danger">3.000.000đ</b>','pct' => $b5],
                ['label' => 'Lớn hơn <b class="text-danger">3.000.000đ</b>',                                  'pct' => $b6],
            ];
            ?>
            <h5 class="fw-bold mb-3 mt-2 text-info text-uppercase">Chương trình khuyến mãi nạp tiền</h5>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle mb-0 bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Số tiền nạp (đ)</th>
                                    <th class="py-3">Khuyến mãi (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bonusTiers as $tier): ?>
                                <tr>
                                    <td><?= $tier['label'] ?></td>
                                    <td class="fw-bold <?= $tier['pct'] > 0 ? 'text-success' : 'text-muted' ?>">
                                        <?= $tier['pct'] ?> %
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-3 pb-3">
                    <p class="mb-0 text-muted small">Nếu bạn cần hỗ trợ thêm vui lòng liên hệ hotline/zalo: <b class="text-danger"><?= htmlspecialchars($hotline) ?></b></p>
                </div>
            </div>

            <!-- Mua Lượt UP TIN -->
            <h5 class="fw-bold mb-3 mt-4">Mua lượt UP TIN</h5>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Số lần UP</th>
                                    <th>Giá (VNĐ)</th>
                                    <th>Hạn sử dụng</th>
                                    <th>Mua lượt UP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Đọc giá UP tin từ cài đặt admin
                                $upPackages = $data['upPackages'] ?? [];
                                foreach ($upPackages as $turns => $price):
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= number_format($turns) ?> lượt</td>
                                    <td><?= number_format($price) ?> VNĐ</td>
                                    <td>30 ngày</td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm px-4" 
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
                </div>
            </div>

            <h5 class="fw-bold mb-3">Lịch sử giao dịch</h5>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Loại giao dịch</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data['history'])): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">Chưa có giao dịch nào.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($data['history'] as $index => $item): ?>
                                    <tr class="transaction-history-row" <?= $index >= 5 ? 'style="display: none;"' : '' ?>>
                                        <td><?= date('d/m/Y H:i', strtotime($item->ngay_tao)) ?></td>
                                        <td>
                                            <?php $transactionDescription = $item->method_or_desc ?? $item->description ?? ''; ?>
                                            <?php if ($item->type == 'nap_tien'): ?>
                                                <span class="text-success"><i class="fa-solid fa-arrow-down"></i> Nạp tiền (<?= htmlspecialchars($transactionDescription) ?>)</span>
                                            <?php else: ?>
                                                <span class="text-danger"><i class="fa-solid fa-arrow-up"></i> <?= htmlspecialchars($transactionDescription) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold <?= $item->type == 'nap_tien' ? 'text-success' : 'text-danger' ?>">
                                            <?= $item->type == 'nap_tien' ? '+' : '-' ?><?= number_format($item->amount) ?>đ
                                        </td>
                                        <td>
                                            <?php
                                            if ($item->status == 'cho_duyet') echo '<span class="badge bg-warning text-dark">Chờ duyệt</span>';
                                            elseif ($item->status == 'da_duyet' || $item->status == 'thanh_cong') echo '<span class="badge bg-success">Thành công</span>';
                                            elseif ($item->status == 'tu_choi') echo '<span class="badge bg-danger">Bị từ chối</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php if (count($data['history']) > 5): ?>
                <div class="text-center mt-3 mb-4">
                    <button type="button" id="showMoreHistory" class="btn btn-outline-primary px-4">
                        <i class="fa-solid fa-chevron-down me-1"></i> Xem thêm
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Nạp Tiền -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nạp Tiền Qua Mã QR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= URL_ROOT ?>/vi-dien-tu/deposit" method="POST">
                <?= Csrf::field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn phương thức nạp</label>
                        <select name="method" class="form-select" id="paymentMethod" disabled>
                            <option value="payos" selected>Thanh toán tự động PayOS (QR Code, ATM, Visa, Mastercard)</option>
                        </select>
                        <input type="hidden" name="method" value="payos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nhập số tiền cần nạp (VNĐ)</label>
                        <input type="number" name="amount" id="depositAmount" class="form-control" min="50000" step="10000" required placeholder="VD: 100000">
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnConfirmDeposit">Thanh toán qua PayOS</button>
                </div>
            </form>
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

<script>

document.addEventListener("DOMContentLoaded", function() {
    var showMoreHistoryButton = document.getElementById('showMoreHistory');
    if (showMoreHistoryButton) {
        showMoreHistoryButton.addEventListener('click', function() {
            var hiddenRows = Array.from(document.querySelectorAll('.transaction-history-row'))
                .filter(function(row) { return row.style.display === 'none'; });

            hiddenRows.slice(0, 5).forEach(function(row) {
                row.style.display = '';
            });

            if (hiddenRows.length <= 5) {
                showMoreHistoryButton.closest('div').remove();
            }
        });
    }

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
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
