<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4 no-print-wrapper">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Hóa Đơn Thanh Toán</h4>
                <div class="d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-primary btn-sm" style="border-radius: 8px;">
                        <i class="fa-solid fa-print me-1"></i>In Hóa Đơn
                    </button>
                    <a href="<?= URL_ROOT ?>/vi-dien-tu/history" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                        <i class="fa-solid fa-arrow-left me-1"></i>Quay lại Lịch sử
                    </a>
                </div>
            </div>

            <!-- Invoice print layout container -->
            <div class="card border shadow-sm p-5" id="printable-invoice" style="border-radius: 16px; background-color: #fff;">
                
                <!-- Invoice Header -->
                <div class="row border-bottom pb-4 mb-4 align-items-center">
                    <div class="col-sm-6">
                        <h3 class="fw-extrabold text-primary mb-1"><?= SITE_NAME ?></h3>
                        <p class="text-secondary small mb-0">Địa chỉ: Tòa nhà CITC, Quận 1, TP. Hồ Chí Minh</p>
                        <p class="text-secondary small mb-0">Email: support@timnhadat.site | Hotline: 0368180923</p>
                    </div>
                    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                        <h4 class="text-secondary-emphasis fw-bold text-uppercase mb-1">Hóa Đơn Điện Tử</h4>
                        <p class="text-secondary fw-semibold mb-0">Số hóa đơn: #INV<?= str_pad((string)$data['invoice']->id, 7, '0', STR_PAD_LEFT) ?></p>
                        <p class="text-secondary small mb-0">Ngày lập: <?= date('d/m/Y H:i', strtotime($data['invoice']->ngay_tao)) ?></p>
                    </div>
                </div>

                <!-- Payer Info -->
                <div class="row mb-5">
                    <div class="col-sm-6">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Thông tin đơn vị cung cấp</h6>
                        <h6 class="fw-bold text-dark mb-1">Công ty Cổ phần Công nghệ CITC Việt Nam</h6>
                        <p class="text-secondary small mb-0">Mã số thuế: 0102030405</p>
                        <p class="text-secondary small mb-0">Người đại diện: Ban Quản Trị TimNhaDat.site</p>
                    </div>
                    <div class="col-sm-6 text-sm-end mt-4 mt-sm-0">
                        <h6 class="text-muted text-uppercase small fw-bold mb-2">Thông tin khách hàng</h6>
                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($data['invoice']->user_name) ?></h6>
                        <p class="text-secondary small mb-0">Email: <?= htmlspecialchars($data['invoice']->user_email) ?></p>
                        <p class="text-secondary small mb-0">Số điện thoại: <?= htmlspecialchars($data['invoice']->user_phone ?: 'Chưa cập nhật') ?></p>
                    </div>
                </div>

                <!-- Transaction Details Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 80px;">STT</th>
                                <th>Chi tiết dịch vụ / Giao dịch</th>
                                <th class="text-center" style="width: 120px;">Số lượng</th>
                                <th class="text-end" style="width: 180px;">Đơn giá (VND)</th>
                                <th class="text-end" style="width: 180px;">Thành tiền (VND)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>
                                    <h6 class="fw-bold text-dark mb-1">
                                        <?php if ($data['invoice']->loai === 'mua_up'): ?>
                                            Mua gói lượt UP tin đăng
                                        <?php elseif ($data['invoice']->loai === 'mua_vip'): ?>
                                            Nâng cấp gói dịch vụ VIP tin đăng
                                        <?php elseif ($data['invoice']->loai === 'rut_thuong'): ?>
                                            Rút hoa hồng tiếp thị liên kết
                                        <?php else: ?>
                                            <?= htmlspecialchars($data['invoice']->loai) ?>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-secondary"><?= htmlspecialchars($data['invoice']->mo_ta) ?></small>
                                </td>
                                <td class="text-center">1</td>
                                <td class="text-end"><?= number_format(abs($data['invoice']->so_tien)) ?> đ</td>
                                <td class="text-end fw-bold"><?= number_format(abs($data['invoice']->so_tien)) ?> đ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row justify-content-end mb-5">
                    <div class="col-md-5 text-end">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Cộng tiền dịch vụ:</span>
                            <span class="text-dark fw-semibold"><?= number_format(abs($data['invoice']->so_tien)) ?> đ</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Thuế GTGT (VAT 0%):</span>
                            <span class="text-dark fw-semibold">0 đ</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-bold text-dark">Tổng cộng thanh toán:</h5>
                            <h5 class="fw-bold text-danger"><?= number_format(abs($data['invoice']->so_tien)) ?> đ</h5>
                        </div>
                    </div>
                </div>

                <!-- Signatures -->
                <div class="row pt-4 text-center" style="font-size: 0.9rem;">
                    <div class="col-6">
                        <h6 class="fw-bold text-dark">Người mua dịch vụ</h6>
                        <small class="text-muted">(Ký, ghi rõ họ tên)</small>
                        <div style="height: 80px;"></div>
                        <p class="fw-bold text-dark mb-0"><?= htmlspecialchars($data['invoice']->user_name) ?></p>
                    </div>
                    <div class="col-6">
                        <h6 class="fw-bold text-dark">Đại diện đơn vị cung cấp</h6>
                        <small class="text-muted">(Ký tên và đóng dấu)</small>
                        <div class="text-success fw-bold d-flex flex-column align-items-center justify-content-center border border-success p-2 mx-auto my-3" style="width: 180px; font-size: 0.72rem; border-radius: 8px; line-height: 1.2;">
                            <span class="fw-extrabold text-uppercase">ĐÃ KÝ ĐIỆN TỬ</span>
                            <span style="font-size: 0.65rem; font-weight: normal;"><?= SITE_NAME ?></span>
                            <span style="font-size: 0.6rem; font-weight: normal;"><?= date('d/m/Y H:i', strtotime($data['invoice']->ngay_tao)) ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-invoice, #printable-invoice * {
        visibility: visible;
    }
    #printable-invoice {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .no-print-wrapper {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>

<?php require_once '../app/views/layouts/footer.php'; ?>
