<?php require_once '../app/views/admin/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Quản lý Nạp tiền chờ duyệt</h2>
</div>

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

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Mã GD</th>
                        <th class="border-0">Người dùng</th>
                        <th class="border-0">Số tiền nạp</th>
                        <th class="border-0">Thực nhận (sau KM)</th>
                        <th class="border-0">Phương thức</th>
                        <th class="border-0">Ghi chú</th>
                        <th class="border-0">Thời gian</th>
                        <th class="border-0 text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['deposits'])): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Không có yêu cầu nạp tiền nào chờ duyệt.</td></tr>
                    <?php else: ?>
                        <?php foreach($data['deposits'] as $deposit): ?>
                        <tr>
                            <td><span class="badge bg-secondary font-monospace"><?= $deposit->ma_giao_dich ?></span></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($deposit->ten_nguoi_dung) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($deposit->email) ?></div>
                            </td>
                            <td class="fw-semibold"><?= number_format($deposit->so_tien) ?>đ</td>
                            <td>
                                <span class="text-success fw-bold">+<?= number_format($deposit->tong_cong) ?>đ</span>
                                <?php $bonus = $deposit->tong_cong - $deposit->so_tien; ?>
                                <?php if ($bonus > 0): ?>
                                    <br><small class="text-warning"><i class="fa-solid fa-gift"></i> +<?= number_format($bonus) ?>đ KM</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($deposit->phuong_thuc == 'chuyen_khoan'): ?>
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-building-columns"></i> Chuyển khoản</span>
                                <?php else: ?>
                                    <span class="badge bg-primary"><i class="fa-solid fa-wallet"></i> <?= $deposit->phuong_thuc ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted" style="max-width:150px;"><?= htmlspecialchars($deposit->ghi_chu ?? '—') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($deposit->ngay_tao)) ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-success btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#approveDepositModal"
                                        data-id="<?= $deposit->id ?>"
                                        data-username="<?= htmlspecialchars($deposit->ten_nguoi_dung) ?>"
                                        data-amount="<?= number_format($deposit->tong_cong) ?>">
                                    <i class="fa-solid fa-check"></i> Duyệt
                                </button>
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectDepositModal"
                                        data-id="<?= $deposit->id ?>"
                                        data-username="<?= htmlspecialchars($deposit->ten_nguoi_dung) ?>"
                                        data-amount="<?= number_format($deposit->tong_cong) ?>">
                                    <i class="fa-solid fa-xmark"></i> Từ chối
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Duyệt Nạp Tiền -->
<div class="modal fade" id="approveDepositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa-solid fa-check-circle me-2"></i>Xác nhận Duyệt Nạp tiền</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-money-bill-wave text-success mb-3" style="font-size: 3rem;"></i>
                <h5 class="mb-3">Xác nhận nạp tiền cho người dùng này?</h5>
                <p class="mb-1 fs-5">Người dùng: <b id="approveUserName" class="text-dark"></b></p>
                <p class="mb-0 fs-5">Số tiền: <b id="approveUserAmount" class="text-success"></b> VNĐ</p>
            </div>
            <div class="modal-footer bg-light justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="approveForm" method="POST" action="">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-success px-4"><i class="fa-solid fa-check"></i> Đồng ý duyệt</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Từ chối Nạp Tiền -->
<div class="modal fade" id="rejectDepositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xác nhận Từ chối</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-solid fa-ban text-danger mb-3" style="font-size: 3rem;"></i>
                <h5 class="mb-3">Bạn chắc chắn muốn từ chối yêu cầu này?</h5>
                <p class="mb-1 fs-5">Người dùng: <b id="rejectUserName" class="text-dark"></b></p>
                <p class="mb-0 fs-5">Số tiền: <b id="rejectUserAmount" class="text-danger"></b> VNĐ</p>
            </div>
            <div class="modal-footer bg-light justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="rejectForm" method="POST" action="">
                    <?= Csrf::field() ?>
                    <button type="submit" class="btn btn-danger px-4"><i class="fa-solid fa-xmark"></i> Đồng ý từ chối</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var approveDepositModal = document.getElementById('approveDepositModal');
    if (approveDepositModal) {
        approveDepositModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-username');
            var amount = button.getAttribute('data-amount');
            
            document.getElementById('approveUserName').innerText = name;
            document.getElementById('approveUserAmount').innerText = amount;
            document.getElementById('approveForm').action = '<?= URL_ROOT ?>/admin/nap-tien/approve/' + id;
        });
    }

    var rejectDepositModal = document.getElementById('rejectDepositModal');
    if (rejectDepositModal) {
        rejectDepositModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-username');
            var amount = button.getAttribute('data-amount');
            
            document.getElementById('rejectUserName').innerText = name;
            document.getElementById('rejectUserAmount').innerText = amount;
            document.getElementById('rejectForm').action = '<?= URL_ROOT ?>/admin/nap-tien/reject/' + id;
        });
    }
});
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
