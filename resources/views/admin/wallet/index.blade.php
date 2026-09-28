@include('admin.layouts.header')

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="m-0 fw-bold text-dark"><i class="fa-solid fa-wallet me-2 text-primary"></i>CRM Ví & Doanh Thu</h1>
                    <p class="text-muted small mb-0">Quản lý giao dịch, duyệt nạp tiền, hoàn tiền dịch vụ và thống kê doanh thu</p>
                </div>
                <a href="<?= URL_ROOT ?>/admin/wallet/export?<?= http_build_query($_GET) ?>" class="btn btn-success fw-bold px-4" style="border-radius: 8px;">
                    <i class="fa-solid fa-file-excel me-1"></i>Xuất Excel (CSV)
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Alerts -->
            <?php if (Session::flash('admin_wallet_success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                    <i class="fa-solid fa-circle-check me-2"></i><?= Session::flash('admin_wallet_success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (Session::flash('admin_wallet_error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                    <i class="fa-solid fa-circle-exclamation me-2"></i><?= Session::flash('admin_wallet_error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 12px; border-left: 5px solid #10b981 !important;">
                        <h6 class="text-secondary small text-uppercase fw-bold mb-1">Doanh thu nạp tiền tích lũy</h6>
                        <h2 class="fw-extrabold text-success mb-0"><?= number_format($data['totalRevenue']) ?> đ</h2>
                        <small class="text-muted">Tổng số tiền khách nạp thành công</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 12px; border-left: 5px solid #3b82f6 !important;">
                        <h6 class="text-secondary small text-uppercase fw-bold mb-1">Số dư lưu hành trên hệ thống</h6>
                        <h2 class="fw-extrabold text-primary mb-0"><?= number_format($data['totalUserBalances']) ?> đ</h2>
                        <small class="text-muted">Tổng số dư khả dụng trong ví các user</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 12px; border-left: 5px solid #f59e0b !important;">
                        <h6 class="text-secondary small text-uppercase fw-bold mb-1">Số lượng giao dịch trong bộ lọc</h6>
                        <h2 class="fw-extrabold text-warning mb-0"><?= number_format($data['totalItems']) ?></h2>
                        <small class="text-muted">Các bản ghi khớp với điều kiện lọc hiện tại</small>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics charts -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px;">
                        <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-chart-line text-primary me-2"></i>Biểu đồ doanh thu nạp tiền (30 ngày qua)</h5>
                        <div style="height: 300px; position: relative;">
                            <canvas id="revenueDailyChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px;">
                        <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-chart-pie text-success me-2"></i>Phân loại chi tiêu</h5>
                        <div style="height: 300px; position: relative;">
                            <canvas id="expenseTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filter Panel -->
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 16px;">
                <form action="<?= URL_ROOT ?>/admin/wallet" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Tìm kiếm khách hàng / mô tả</label>
                        <input type="text" name="search" class="form-control" placeholder="Tên, Email hoặc nội dung..." value="<?= htmlspecialchars($data['filters']['search']) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Loại giao dịch</label>
                        <select name="type" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="nap_tien" <?= $data['filters']['type'] === 'nap_tien' ? 'selected' : '' ?>>Nạp tiền</option>
                            <option value="mua_up" <?= $data['filters']['type'] === 'mua_up' ? 'selected' : '' ?>>Mua gói UP</option>
                            <option value="mua_vip" <?= $data['filters']['type'] === 'mua_vip' ? 'selected' : '' ?>>Nâng VIP</option>
                            <option value="thuong_chia_se" <?= $data['filters']['type'] === 'thuong_chia_se' ? 'selected' : '' ?>>Thưởng chia sẻ</option>
                            <option value="rut_thuong" <?= $data['filters']['type'] === 'rut_thuong' ? 'selected' : '' ?>>Rút hoa hồng</option>
                            <option value="hoan_tien" <?= $data['filters']['type'] === 'hoan_tien' ? 'selected' : '' ?>>Hoàn tiền</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Trạng thái nạp tiền</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="cho_duyet" <?= $data['filters']['status'] === 'cho_duyet' ? 'selected' : '' ?>>Đang chờ duyệt</option>
                            <option value="da_duyet" <?= $data['filters']['status'] === 'da_duyet' ? 'selected' : '' ?>>Thành công</option>
                            <option value="tu_choi" <?= $data['filters']['status'] === 'tu_choi' ? 'selected' : '' ?>>Bị từ chối</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold w-100" style="border-radius: 8px;">
                            <i class="fa-solid fa-filter me-1"></i>Lọc kết quả
                        </button>
                        <a href="<?= URL_ROOT ?>/admin/wallet" class="btn btn-outline-secondary w-50" style="border-radius: 8px;">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Transactions list -->
            <div class="card border-0 shadow-sm p-4" style="border-radius: 16px;">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-list me-2 text-primary"></i>Danh sách nhật ký giao dịch CRM</h5>

                <?php if (empty($data['transactions'])): ?>
                    <div class="text-center py-5 text-muted small">
                        <span class="fs-1"><i class="fa-solid fa-folder-open"></i></span>
                        <p class="mt-3 mb-0">Không có giao dịch nào khớp với điều kiện lọc hiện tại.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="font-size: 0.88rem;">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th>STT</th>
                                    <th>Thời gian</th>
                                    <th>Khách hàng</th>
                                    <th>Loại giao dịch</th>
                                    <th>Chi tiết giao dịch</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $index = ($data['currentPage'] - 1) * 20 + 1; ?>
                                <?php foreach ($data['transactions'] as $t): ?>
                                    <tr>
                                        <td class="text-secondary"><?= $index++ ?></td>
                                        <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($t->ngay_tao)) ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($t->ten) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($t->email) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($t->type === 'nap_tien'): ?>
                                                <span class="badge bg-success-subtle text-success px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-down me-1"></i>Nạp tiền</span>
                                            <?php elseif ($t->type === 'mua_up'): ?>
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1.5 fw-semibold"><i class="fa-solid fa-arrow-up me-1"></i>Mua gói UP</span>
                                            <?php elseif ($t->type === 'mua_vip'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1.5 fw-semibold"><i class="fa-solid fa-crown me-1"></i>Mua gói VIP</span>
                                            <?php elseif ($t->type === 'thuong_chia_se'): ?>
                                                <span class="badge bg-info-subtle text-info-emphasis px-2 py-1.5 fw-semibold"><i class="fa-solid fa-share-nodes me-1"></i>Thưởng chia sẻ</span>
                                            <?php elseif ($t->type === 'rut_thuong'): ?>
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-up me-1"></i>Rút hoa hồng</span>
                                            <?php elseif ($t->type === 'hoan_tien'): ?>
                                                <span class="badge bg-info-subtle text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-undo me-1"></i>Hoàn tiền</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1.5 fw-semibold"><?= htmlspecialchars($t->type) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-secondary"><?= htmlspecialchars($t->method_or_desc) ?></td>
                                        <td class="fw-bold text-end <?= ($t->type === 'nap_tien' || $t->type === 'thuong_chia_se') ? 'text-success' : 'text-danger' ?>">
                                            <?= ($t->type === 'nap_tien' || $t->type === 'thuong_chia_se') ? '+' : '-' ?>
                                            <?= number_format($t->amount) ?> đ
                                        </td>
                                        <td>
                                            <?= match($t->status) {
                                                'cho_duyet', 'moi' => '<span class="badge bg-warning text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-notch fa-spin me-1"></i>Chờ duyệt</span>',
                                                'da_duyet', 'thanh_cong' => '<span class="badge bg-success px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Thành công</span>',
                                                'tu_choi', 'that_bai' => '<span class="badge bg-danger px-2 py-1.5 fw-semibold"><i class="fa-solid fa-circle-xmark me-1"></i>Đã từ chối</span>',
                                                'hoan_tien' => '<span class="badge bg-info text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-undo me-1"></i>Hoàn tiền</span>',
                                                'huy' => '<span class="badge bg-secondary px-2 py-1.5 fw-semibold"><i class="fa-solid fa-xmark me-1"></i>Đã hủy</span>',
                                                default => '<span class="badge bg-secondary px-2 py-1.5">' . $t->status . '</span>'
                                            } ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- Duyệt nạp tiền thủ công -->
                                            <?php if ($t->type === 'nap_tien' && $t->status === 'cho_duyet' && strpos($t->method_or_desc, 'PayOS') === false): ?>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <form action="<?= URL_ROOT ?>/admin/wallet/approve/<?= $t->id ?>" method="POST" onsubmit="return confirm('Phê duyệt cộng tiền cho giao dịch này?')">
                                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                                        <button type="submit" class="btn btn-success btn-xs fw-bold px-2 py-1" style="font-size: 0.75rem; border-radius: 4px;">Duyệt</button>
                                                    </form>
                                                    <form action="<?= URL_ROOT ?>/admin/wallet/reject/<?= $t->id ?>" method="POST" onsubmit="return confirm('Từ chối giao dịch nạp tiền này?')">
                                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                                        <button type="submit" class="btn btn-danger btn-xs fw-bold px-2 py-1" style="font-size: 0.75rem; border-radius: 4px;">Từ chối</button>
                                                    </form>
                                                </div>
                                            <!-- Hoàn tiền chi tiêu -->
                                            <?php elseif (($t->type === 'mua_vip' || $t->type === 'mua_up') && $t->status === 'da_duyet'): ?>
                                                <form action="<?= URL_ROOT ?>/admin/wallet/refund/<?= $t->id ?>" method="POST" onsubmit="return confirm('Xác nhận hoàn tiền giao dịch mua dịch vụ này về ví người dùng?')">
                                                    <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-xs fw-semibold px-2.5 py-1" style="font-size: 0.75rem; border-radius: 4px;">
                                                        <i class="fa-solid fa-rotate-left me-1"></i>Hoàn tiền
                                                    </button>
                                                </form>
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
                                    <a class="page-link" href="?search=<?= urlencode($data['filters']['search']) ?>&type=<?= urlencode($data['filters']['type']) ?>&status=<?= urlencode($data['filters']['status']) ?>&page=<?= $data['currentPage'] - 1 ?>">Trước</a>
                                </li>
                                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                                    <li class="page-item <?= $data['currentPage'] === $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($data['filters']['search']) ?>&type=<?= urlencode($data['filters']['type']) ?>&status=<?= urlencode($data['filters']['status']) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?search=<?= urlencode($data['filters']['search']) ?>&type=<?= urlencode($data['filters']['type']) ?>&status=<?= urlencode($data['filters']['status']) ?>&page=<?= $data['currentPage'] + 1 ?>">Sau</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

        </div>
    </section>
</div>

<!-- Chart.js scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── 1. BIỂU ĐỒ DOANH THU HÀNG NGÀY (LINE CHART) ──
<?php
$dailyLabels = [];
$dailyValues = [];
// Chuyển stats daily thành thứ tự cũ nhất trước để vẽ đường thời gian đúng
$dailyStats = array_reverse($data['stats']['daily'] ?? []);
foreach ($dailyStats as $d) {
    $dailyLabels[] = date('d/m', strtotime($d->date));
    $dailyValues[] = (int)$d->revenue;
}
?>

const dailyLabels = <?= json_encode($dailyLabels) ?>;
const dailyValues = <?= json_encode($dailyValues) ?>;

const ctxDaily = document.getElementById('revenueDailyChart').getContext('2d');
new Chart(ctxDaily, {
    type: 'line',
    data: {
        labels: dailyLabels.length > 0 ? dailyLabels : ['Chưa có dữ liệu'],
        datasets: [{
            label: 'Doanh thu nạp tiền (VND)',
            data: dailyValues.length > 0 ? dailyValues : [0],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                    }
                }
            }
        }
    }
});

// ── 2. BIỂU ĐỒ PHÂN LOẠI CHI TIÊU (PIE CHART) ──
<?php
$typeLabels = [];
$typeValues = [];
$typeBgColors = [];
$colors = [
    'mua_vip' => '#f59e0b', // gold
    'mua_up'  => '#3b82f6',  // blue
    'thuong_chia_se' => '#06b6d4', // cyan
    'rut_thuong' => '#ef4444', // red
    'hoan_tien' => '#6b7280', // gray
];

$typeMap = [
    'mua_vip' => 'Mua gói VIP',
    'mua_up' => 'Mua lượt UP',
    'thuong_chia_se' => 'Thưởng chia sẻ',
    'rut_thuong' => 'Rút hoa hồng',
    'hoan_tien' => 'Hoàn tiền'
];

foreach (($data['stats']['by_type'] ?? []) as $t) {
    if ($t->type === 'nap_tien') continue; // Loại nạp tiền không phải chi tiêu
    $typeLabels[] = $typeMap[$t->type] ?? $t->type;
    $typeValues[] = (int)abs($t->total);
    $typeBgColors[] = $colors[$t->type] ?? '#9ca3af';
}
?>

const typeLabels = <?= json_encode($typeLabels) ?>;
const typeValues = <?= json_encode($typeValues) ?>;
const typeBgColors = <?= json_encode($typeBgColors) ?>;

const ctxType = document.getElementById('expenseTypeChart').getContext('2d');
new Chart(ctxType, {
    type: 'doughnut',
    data: {
        labels: typeLabels.length > 0 ? typeLabels : ['Chưa có dữ liệu'],
        datasets: [{
            data: typeValues.length > 0 ? typeValues : [1],
            backgroundColor: typeBgColors.length > 0 ? typeBgColors : ['#e5e7eb'],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 12, font: { size: 11 } }
            }
        }
    }
});
</script>

@include('admin.layouts.footer')
