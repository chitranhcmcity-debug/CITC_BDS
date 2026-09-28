<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h4 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Lịch Sử Giao Dịch</h4>
                <a href="<?= URL_ROOT ?>/vi-dien-tu" class="btn btn-outline-secondary btn-sm" style="border-radius: 8px;">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại Ví
                </a>
            </div>

            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <?php if (empty($data['history'])): ?>
                    <div class="text-center py-5 text-muted">
                        <span class="fs-1"><i class="fa-solid fa-receipt"></i></span>
                        <h6 class="mt-3">Bạn chưa thực hiện bất kỳ giao dịch nào.</h6>
                        <a href="<?= URL_ROOT ?>/vi-dien-tu/deposit" class="btn btn-primary btn-sm px-4 fw-semibold mt-2" style="border-radius: 6px;">Nạp tiền ngay</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="font-size: 0.9rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th>Thời gian</th>
                                    <th>Loại giao dịch</th>
                                    <th>Nội dung / Phương thức</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Hóa đơn</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                <?php foreach ($data['history'] as $item): ?>
                                    <tr>
                                        <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($item->ngay_tao)) ?></td>
                                        <td>
                                            <?php if ($item->type === 'nap_prep' || $item->type === 'nap_tien'): ?>
                                                <span class="badge bg-success-subtle text-success px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-down me-1"></i>Nạp tiền</span>
                                            <?php elseif ($item->type === 'mua_up'): ?>
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1.5 fw-semibold"><i class="fa-solid fa-arrow-up me-1"></i>Mua gói UP</span>
                                            <?php elseif ($item->type === 'mua_vip'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1.5 fw-semibold"><i class="fa-solid fa-crown me-1"></i>Mua gói VIP</span>
                                            <?php elseif ($item->type === 'thuong_chia_se'): ?>
                                                <span class="badge bg-info-subtle text-info-emphasis px-2 py-1.5 fw-semibold"><i class="fa-solid fa-share-nodes me-1"></i>Thưởng chia sẻ</span>
                                            <?php elseif ($item->type === 'rut_thuong'): ?>
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-up me-1"></i>Rút hoa hồng</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1.5 fw-semibold"><?= htmlspecialchars($item->type) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold text-secondary">
                                            <?= htmlspecialchars($item->method_or_desc) ?>
                                        </td>
                                        <td class="fw-bold <?= ($item->type === 'nap_tien' || $item->type === 'thuong_chia_se') ? 'text-success' : 'text-danger' ?>">
                                            <?= ($item->type === 'nap_tien' || $item->type === 'thuong_chia_se') ? '+' : '-' ?>
                                            <?= number_format($item->amount) ?> đ
                                        </td>
                                        <td>
                                            <?= match($item->status) {
                                                'moi', 'cho_duyet' => '<span class="badge bg-warning text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-clock-rotate-left fa-spin me-1"></i>Đang chờ</span>',
                                                'da_duyet', 'thanh_cong' => '<span class="badge bg-success px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Thành công</span>',
                                                'tu_choi', 'that_bai' => '<span class="badge bg-danger px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-xmark me-1"></i>Bị từ chối</span>',
                                                'hoan_tien' => '<span class="badge bg-info text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-undo me-1"></i>Hoàn tiền</span>',
                                                'huy' => '<span class="badge bg-secondary px-2 py-1.5 fw-semibold"><i class="fa-solid fa-xmark me-1"></i>Đã hủy</span>',
                                                default => '<span class="badge bg-secondary px-2 py-1.5">' . $item->status . '</span>'
                                            } ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($item->type !== 'nap_tien' && $item->status === 'da_duyet'): ?>
                                                <a href="<?= URL_ROOT ?>/vi-dien-tu/invoice/<?= $item->id ?>" class="btn btn-link text-primary p-0 fw-semibold" style="font-size: 0.85rem;"><i class="fa-solid fa-file-invoice-dollar me-1"></i>Xem hóa đơn</a>
                                            <?php else: ?>
                                                <span class="text-muted small">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($data['totalPages'] > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $data['currentPage'] - 1 ?>">Trước</a>
                                </li>
                                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                                    <li class="page-item <?= $data['currentPage'] === $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $data['currentPage'] + 1 ?>">Sau</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
