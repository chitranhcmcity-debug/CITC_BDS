<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-gift me-2"></i>Chương Trình Giới Thiệu Bạn Bè</h4>
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

            <!-- Share block -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <div class="row g-4 align-items-center">
                    <div class="col-md-7">
                        <h5 class="fw-bold text-dark mb-2">Mời bạn bè – Nhận ngay hoa hồng</h5>
                        <p class="text-secondary small mb-3">Mời bạn bè tham gia TimNhaDat.site bằng mã giới thiệu của bạn. Bạn sẽ nhận được **10% hoa hồng** giá trị giao dịch mỗi khi họ nâng cấp gói VIP hoặc mua lượt UP tin!</p>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary mb-1">Mã giới thiệu của bạn</label>
                            <div class="input-group">
                                <input type="text" id="my-ref-code" class="form-control fw-bold text-primary bg-light" value="<?= htmlspecialchars($data['refStats']['referral_code'] ?? '') ?>" readonly style="border-radius: 8px 0 0 8px;">
                                <button class="btn btn-primary" onclick="copyRefCode()" style="border-radius: 0 8px 8px 0;">
                                    <i class="fa-regular fa-copy me-1"></i>Copy
                                </button>
                            </div>
                        </div>

                        <!-- Link sharing -->
                        <div>
                            <label class="form-label small fw-semibold text-secondary mb-1">Link giới thiệu nhanh</label>
                            <input type="text" class="form-control text-secondary bg-light small" value="<?= URL_ROOT ?>/nguoi-dung/dang-ky?ref=<?= htmlspecialchars($data['refStats']['referral_code'] ?? '') ?>" readonly style="border-radius: 8px;">
                        </div>
                    </div>

                    <div class="col-md-5 text-center bg-light p-4" style="border-radius: 16px;">
                        <h6 class="fw-semibold text-secondary mb-1">Số dư hoa hồng tích lũy</h6>
                        <h2 class="display-6 fw-bold text-success mb-3"><?= number_format($data['refStats']['referral_balance'] ?? 0) ?> đ</h2>

                        <form action="<?= URL_ROOT ?>/vi-dien-tu/withdrawReward" method="POST" onsubmit="return confirm('Bạn muốn rút toàn bộ số tiền hoa hồng này về Ví điện tử?')">
                            <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                            <button type="submit" class="btn btn-success w-100 py-2.5 fw-bold" <?= ($data['refStats']['referral_balance'] ?? 0) < 50000 ? 'disabled' : '' ?> style="border-radius: 10px;">
                                <i class="fa-solid fa-money-bill-transfer me-1"></i>Rút về Ví chính
                            </button>
                        </form>
                        <small class="text-secondary d-block mt-2">Số tiền tối thiểu được rút là **50,000 đ**</small>
                    </div>
                </div>
            </div>

            <!-- Stats grids -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 text-center h-100" style="border-radius: 16px;">
                        <span class="fs-1 text-primary mb-2"><i class="fa-solid fa-users"></i></span>
                        <h4 class="fw-bold text-dark mb-1"><?= number_format($data['refStats']['total_referred'] ?? 0) ?></h4>
                        <small class="text-secondary">Người đã giới thiệu thành công</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Apply referrer code block -->
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px;">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-user-plus me-1 text-primary"></i>Nhập mã giới thiệu của người mời</h6>
                        
                        <?php if (empty($data['refStats']['referred_by'])): ?>
                            <p class="text-secondary small mb-3">Nếu bạn được người khác giới thiệu, hãy nhập mã giới thiệu của họ để xác nhận.</p>
                            <form action="<?= URL_ROOT ?>/vi-dien-tu/applyReferralCode" method="POST">
                                <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                <div class="input-group">
                                    <input type="text" name="referral_code" class="form-control" placeholder="Ví dụ: REF123456" required style="border-radius: 8px 0 0 8px;">
                                    <button type="submit" class="btn btn-outline-primary" style="border-radius: 0 8px 8px 0;">Áp dụng</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success py-2.5 px-3 mb-0 mt-2 small" style="border-radius: 8px;">
                                <i class="fa-solid fa-circle-check me-1"></i>Bạn đã liên kết với người giới thiệu (ID: #<?= $data['refStats']['referred_by'] ?>).
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- List of referral histories -->
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list-ol me-2 text-primary"></i>Danh Sách Bạn Bè Đã Mời & Nhận Thưởng</h5>

                <?php if (empty($data['rewards'])): ?>
                    <div class="text-center py-4 text-muted small">
                        <span class="fs-2"><i class="fa-solid fa-user-tag"></i></span>
                        <p class="mt-2 mb-0">Chưa có hoạt động nhận thưởng giới thiệu nào được ghi nhận.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Người được mời</th>
                                    <th>Email</th>
                                    <th>Số tiền thưởng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['rewards'] as $reward): ?>
                                    <tr>
                                        <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($reward->created_at)) ?></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($reward->referee_name) ?></td>
                                        <td class="text-secondary"><?= htmlspecialchars($reward->referee_email) ?></td>
                                        <td class="fw-bold text-success">+<?= number_format($reward->amount) ?> đ</td>
                                        <td>
                                            <span class="badge bg-success px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Thành công</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyRefCode() {
    const copyText = document.getElementById("my-ref-code");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    
    // Alert using bootstrap alert or temporary text change
    alert("Đã sao chép mã giới thiệu: " + copyText.value);
}
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>
