<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-wallet me-2"></i>Nạp Tiền Vào Ví</h4>
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

            <div class="row g-4">
                <!-- Left panel: form -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                        <h5 class="fw-bold text-dark mb-3">Nhập Số Tiền Cần Nạp</h5>
                        <p class="text-secondary small mb-4">Hệ thống hỗ trợ nạp tiền tự động 24/7 qua cổng PayOS. Tiền sẽ được cộng vào Ví chính của bạn ngay sau khi thanh toán thành công.</p>

                        <form id="deposit-form" action="<?= URL_ROOT ?>/vi-dien-tu/deposit" method="POST">
                            <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">

                            <!-- Amount input -->
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary mb-1">Số tiền nạp (VND) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light text-muted fw-bold">đ</span>
                                    <input type="number" id="amount-input" name="amount" class="form-control text-dark fw-bold" placeholder="Tối thiểu 50,000" min="50000" step="10000" required>
                                </div>
                                <div id="bonus-info" class="form-text text-success fw-semibold mt-2 small d-none">
                                    <i class="fa-solid fa-gift me-1"></i>Bạn sẽ nhận thêm <span id="bonus-percent">0</span>% khuyến mãi nạp tiền (+<span id="bonus-amount">0</span> đ)
                                </div>
                            </div>

                            <!-- Fast select buttons -->
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary mb-2">Chọn nhanh số tiền nạp</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary py-2 px-3 fw-semibold flex-grow-1" onclick="setAmount(50000)" style="border-radius: 10px;">50,000 đ</button>
                                    <button type="button" class="btn btn-outline-primary py-2 px-3 fw-semibold flex-grow-1" onclick="setAmount(100000)" style="border-radius: 10px;">100,000 đ</button>
                                    <button type="button" class="btn btn-outline-primary py-2 px-3 fw-semibold flex-grow-1" onclick="setAmount(200000)" style="border-radius: 10px;">200,000 đ</button>
                                    <button type="button" class="btn btn-outline-primary py-2 px-3 fw-semibold flex-grow-1" onclick="setAmount(500000)" style="border-radius: 10px;">500,000 đ</button>
                                    <button type="button" class="btn btn-outline-primary py-2 px-3 fw-semibold flex-grow-1" onclick="setAmount(1000000)" style="border-radius: 10px;">1,000,000 đ</button>
                                </div>
                            </div>

                            <!-- Payment methods -->
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary mb-2">Phương thức thanh toán</label>
                                <div class="card border border-primary p-3" style="border-radius: 12px; background-color: #eff6ff;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="text-primary me-3 fs-3"><i class="fa-solid fa-qrcode"></i></span>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark">Thanh toán qua PayOS (QR Banking)</h6>
                                                <small class="text-secondary">Chuyển khoản QR siêu tốc, nhận tiền ngay lập tức</small>
                                            </div>
                                        </div>
                                        <span class="text-primary"><i class="fa-solid fa-circle-check fs-5"></i></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px;">
                                <i class="fa-solid fa-credit-card me-2"></i>Tiến Hành Thanh Toán
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right panel: Promotion details -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm p-4" style="border-radius: 16px; background-color: #fafafa;">
                        <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-award text-warning me-2"></i>Bảng Tỷ Lệ Khuyến Mãi</h5>
                        <p class="text-secondary small mb-3">Nạp số tiền lớn hơn để nhận mức chiết khấu/tặng thêm hấp dẫn hơn từ sàn TimNhaDat.site.</p>

                        <div class="list-group list-group-flush" style="font-size: 0.9rem;">
                            <?php
                            $bonusTiers = $data['bonusTiers'] ?? [];
                            $b1 = (int)($bonusTiers[1] ?? 0);
                            $b2 = (int)($bonusTiers[2] ?? 0);
                            $b3 = (int)($bonusTiers[3] ?? 0);
                            $b4 = (int)($bonusTiers[4] ?? 0);
                            $b5 = (int)($bonusTiers[5] ?? 0);
                            $b6 = (int)($bonusTiers[6] ?? 0);
                            
                            $tiersList = [
                                ['label' => 'Từ 50,000 đ - 100,000 đ', 'pct' => $b1],
                                ['label' => 'Từ 100,000 đ - 200,000 đ', 'pct' => $b2],
                                ['label' => 'Từ 200,000 đ - 500,000 đ', 'pct' => $b3],
                                ['label' => 'Từ 500,000 đ - 1,000,000 đ', 'pct' => $b4],
                                ['label' => 'Từ 1,000,000 đ - 3,000,000 đ', 'pct' => $b5],
                                ['label' => 'Trên 3,000,000 đ', 'pct' => $b6]
                            ];
                            ?>

                            <?php foreach ($tiersList as $item): ?>
                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-2.5 px-0 border-light">
                                    <span class="text-secondary"><?= $item['label'] ?></span>
                                    <span class="badge bg-success-subtle text-success px-2 py-1.5 fw-bold" style="font-size: 0.82rem;">+<?= $item['pct'] ?>% số dư</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const bonusTiers = <?= json_encode($bonusTiers) ?>;

function getBonusPercent(amount) {
    if (amount > 3000000) return parseInt(bonusTiers[6] || 0);
    if (amount >= 1000000) return parseInt(bonusTiers[5] || 0);
    if (amount >= 500000) return parseInt(bonusTiers[4] || 0);
    if (amount >= 200000) return parseInt(bonusTiers[3] || 0);
    if (amount >= 100000) return parseInt(bonusTiers[2] || 0);
    if (amount >= 50000) return parseInt(bonusTiers[1] || 0);
    return 0;
}

function setAmount(val) {
    document.getElementById('amount-input').value = val;
    updateBonusInfo(val);
}

function updateBonusInfo(amount) {
    const bonusInfo = document.getElementById('bonus-info');
    const bonusPercent = document.getElementById('bonus-percent');
    const bonusAmount = document.getElementById('bonus-amount');
    
    const pct = getBonusPercent(amount);
    if (pct > 0) {
        const bonusVal = Math.floor(amount * pct / 100);
        bonusPercent.textContent = pct;
        bonusAmount.textContent = new Intl.NumberFormat('vi-VN').format(bonusVal);
        bonusInfo.classList.remove('d-none');
    } else {
        bonusInfo.classList.add('d-none');
    }
}

document.getElementById('amount-input').addEventListener('input', function(e) {
    const val = parseInt(e.target.value) || 0;
    updateBonusInfo(val);
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
