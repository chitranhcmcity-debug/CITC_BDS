@include('admin.layouts.header')

<!-- Include html2pdf API library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Báo Cáo Hoạt Động Kinh Doanh</h1>
    <button type="button" class="btn btn-sm btn-danger shadow-sm" onclick="exportPDF()">
        <i class="fa-solid fa-file-pdf me-1"></i> Xuất File PDF
    </button>
</div>

<!-- Nội dung báo cáo -->
<div id="report-content" class="bg-white p-4 rounded-3 shadow-sm border border-light">

    <div class="text-center mb-4">
        <h2 class="fw-bold text-uppercase" style="color: #0b4e82;">Báo Cáo Hoạt Động Kinh Doanh</h2>
        <p class="text-muted mb-0">Nền tảng Bất Động Sản CITC FullHouse</p>
        <p class="fst-italic small text-muted">Thời gian trích xuất: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <!-- ===== 1. Thống Kê Tổng Quan ===== -->
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Thống Kê Hoạt Động Hệ Thống</h5>
    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded border">
                <div class="fs-2 text-info"><i class="fa-solid fa-users"></i></div>
                <div class="small fw-bold mt-1">Tổng Thành Viên</div>
                <div class="fs-4 fw-bolder"><?= number_format($data['total_users']) ?></div>
                <div class="small text-success">+<?= number_format($data['new_users_month']) ?> tháng này</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded border">
                <div class="fs-2 text-warning"><i class="fa-solid fa-house-flag"></i></div>
                <div class="small fw-bold mt-1">Tổng Tin Đăng</div>
                <div class="fs-4 fw-bolder"><?= number_format($data['total_posts']) ?></div>
                <div class="small text-muted"><?= number_format($data['active_posts']) ?> đang hoạt động</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded border">
                <div class="fs-2 text-danger"><i class="fa-solid fa-clock"></i></div>
                <div class="small fw-bold mt-1">Tin Chờ Duyệt</div>
                <div class="fs-4 fw-bolder"><?= number_format($data['pending_posts']) ?></div>
                <div class="small text-muted">cần xử lý</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded border">
                <div class="fs-2 text-success"><i class="fa-solid fa-eye"></i></div>
                <div class="small fw-bold mt-1">Tổng Lượt Xem</div>
                <div class="fs-4 fw-bolder"><?= number_format($data['total_views']) ?></div>
                <div class="small text-muted">tất cả tin đăng</div>
            </div>
        </div>
    </div>

    <!-- Thống kê khách hàng -->
    <div class="row g-3 mb-5 text-center">
        <div class="col-6 col-md-4">
            <div class="p-3 rounded border" style="background:#e8f5e9;">
                <div class="fs-2 text-success"><i class="fa-solid fa-headset"></i></div>
                <div class="small fw-bold mt-1">Tổng Khách Liên Hệ</div>
                <div class="fs-4 fw-bolder"><?= number_format($data['total_leads']) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="p-3 rounded border" style="background:#fff3e0;">
                <div class="fs-2 text-warning"><i class="fa-solid fa-user-plus"></i></div>
                <div class="small fw-bold mt-1">Khách Mới Tháng Này</div>
                <div class="fs-4 fw-bolder">+<?= number_format($data['new_leads_month']) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="p-3 rounded border" style="background:#e3f2fd;">
                <div class="fs-2 text-primary"><i class="fa-solid fa-handshake"></i></div>
                <div class="small fw-bold mt-1">Giao Dịch Thành Công</div>
                <div class="fs-4 fw-bolder"><?= number_format($data['leads_success']) ?></div>
            </div>
        </div>
    </div>

    <!-- ===== 2. Doanh Thu & Chi Tiêu ===== -->
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">2. Báo Cáo Doanh Thu & Dịch Vụ Cung Cấp</h5>

    <!-- Tổng quan tài chính -->
    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-md-3">
            <div class="p-3 rounded border" style="background:#e8f5e9;">
                <div class="small fw-bold text-success"><i class="fa-solid fa-circle-dollar-to-slot me-1"></i>Tổng Thực Thu</div>
                <div class="fs-5 fw-bolder text-success"><?= number_format($data['total_revenue']) ?> đ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded border" style="background:#e3f2fd;">
                <div class="small fw-bold text-primary"><i class="fa-solid fa-wallet me-1"></i>Thực Thu Tháng Này</div>
                <div class="fs-5 fw-bolder text-primary"><?= number_format($data['revenue_month']) ?> đ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded border" style="background:#fff3e0;">
                <div class="small fw-bold text-warning"><i class="fa-solid fa-layer-group me-1"></i>Tổng Giá Trị Dịch Vụ Đã Bán</div>
                <div class="fs-5 fw-bolder text-warning"><?= number_format($data['total_spending']) ?> đ</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded border" style="background:#fce4ec;">
                <div class="small fw-bold text-danger"><i class="fa-solid fa-clock me-1"></i>Giao Dịch Chờ Duyệt</div>
                <div class="fs-5 fw-bolder text-danger"><?= number_format($data['pending_payments']) ?></div>
            </div>
        </div>
    </div>

    <!-- Bảng doanh thu theo tháng -->
    <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-money-bill-wave me-1"></i> Bảng Doanh Thu Nạp Tiền (Đã Khấu Trừ Chi Phí & Thuế)</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead style="background:#0b4e82;" class="text-white">
                <tr>
                    <th>Tháng</th>
                    <th>Số GD Nạp</th>
                    <th class="text-success">Tiền Khách Nạp</th>
                    <th class="text-info">Trừ Chi Phí Khuyến Mãi</th>
                    <th class="text-warning">Trừ Thuế (10%)</th>
                    <th class="text-primary fw-bold">Thực Thu (Ròng)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                if (!empty($data['monthly_revenue'])):
                    foreach ($data['monthly_revenue'] as $rev):
                        $khuyen_mai = $rev->tong_khuyen_mai;
                        $thue = $rev->tong_nap * 0.10; // Thuế 10% tính trên tổng tiền khách nạp
                        $rong = $rev->tong_nap - $khuyen_mai - $thue; // Doanh thu ròng thực sự đút túi
                        $grand_total += $rong;
                ?>
                <tr>
                    <td class="fw-bold">Tháng <?= htmlspecialchars($rev->thang) ?></td>
                    <td><?= number_format($rev->so_giao_dich) ?></td>
                    <td class="text-success fw-bold">+<?= number_format($rev->tong_nap) ?> đ</td>
                    <td class="text-info">-<?= number_format($khuyen_mai) ?> đ</td>
                    <td class="text-warning">-<?= number_format($thue) ?> đ</td>
                    <td class="text-primary fw-bolder"><?= number_format($rong) ?> đ</td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-muted fst-italic">Chưa có dữ liệu giao dịch nạp tiền.</td></tr>
                <?php endif; ?>
                <?php if (!empty($data['monthly_revenue'])): ?>
                <tr class="table-success fw-bold">
                    <td colspan="5" class="text-end text-uppercase">Tổng Doanh Thu Ròng Lũy Kế:</td>
                    <td class="text-success fs-5">+<?= number_format($grand_total) ?> đ</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bảng Dịch Vụ Đã Bán theo tháng -->
    <h6 class="fw-bold text-warning mb-2"><i class="fa-solid fa-layer-group me-1"></i> Bảng Thống Kê Dịch Vụ Đã Bán (Tiền Ảo Khách Tiêu)</h6>
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead style="background:#e67e22;" class="text-white">
                <tr>
                    <th>Tháng</th>
                    <th>Giá Trị Gói VIP Đã Bán</th>
                    <th>Giá Trị Gói UP Đã Bán</th>
                    <th class="fw-bold">Tổng Giá Trị Đã Cung Cấp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Build spending lookup by month
                $spending_map = [];
                foreach ($data['monthly_spending'] as $s) {
                    $spending_map[$s->thang] = $s;
                }
                
                $total_services = 0;
                if (!empty($data['monthly_spending'])):
                    foreach ($data['monthly_spending'] as $sp):
                        $chi_vip = $sp->chi_vip;
                        $chi_up  = $sp->chi_up;
                        $tong_thang = $chi_vip + $chi_up;
                        $total_services += $tong_thang;
                ?>
                <tr>
                    <td class="fw-bold">Tháng <?= htmlspecialchars($sp->thang) ?></td>
                    <td class="text-warning fw-bold"><?= number_format($chi_vip) ?> đ</td>
                    <td class="text-warning fw-bold"><?= number_format($chi_up) ?> đ</td>
                    <td class="text-danger fw-bolder"><?= number_format($tong_thang) ?> đ</td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-muted fst-italic">Chưa có dữ liệu mua gói dịch vụ.</td></tr>
                <?php endif; ?>
                <?php if (!empty($data['monthly_spending'])): ?>
                <tr class="table-warning fw-bold">
                    <td colspan="3" class="text-end text-uppercase">Tổng Giá Trị Dịch Vụ Cung Cấp Lũy Kế:</td>
                    <td class="text-danger fs-5"><?= number_format($total_services) ?> đ</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Giao dịch gần nhất -->
    <h6 class="fw-bold mb-2">Giao Dịch Nạp Tiền Gần Nhất</h6>
    <div class="table-responsive mb-5">
        <table class="table table-sm table-bordered align-middle">
            <thead class="bg-light">
                <tr>
                    <th>Người Dùng</th>
                    <th>Số Tiền Nạp</th>
                    <th>Khuyến Mãi</th>
                    <th>Phương Thức</th>
                    <th>Trạng Thái</th>
                    <th>Ngày</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['recent_tx'])): ?>
                    <?php foreach ($data['recent_tx'] as $tx): ?>
                    <tr>
                        <td><?= htmlspecialchars($tx->ten_nguoi_dung) ?><br><small class="text-muted"><?= htmlspecialchars($tx->email) ?></small></td>
                        <td class="text-success fw-bold"><?= number_format($tx->so_tien) ?> đ</td>
                        <td class="text-info">+<?= number_format($tx->so_tien_khuyen_mai) ?> đ</td>
                        <td><span class="badge bg-secondary"><?= strtoupper(str_replace('_', ' ', $tx->phuong_thuc)) ?></span></td>
                        <td>
                            <?php if ($tx->trang_thai == 'da_duyet'): ?>
                                <span class="badge bg-success">Đã Duyệt</span>
                            <?php elseif ($tx->trang_thai == 'tu_choi'): ?>
                                <span class="badge bg-danger">Từ Chối</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Chờ Duyệt</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?= date('d/m/Y', strtotime($tx->ngay_tao)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-muted fst-italic text-center">Chưa có giao dịch nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== 3. Tin Đăng Theo Tháng ===== -->
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Báo Cáo Tin Đăng Theo Tháng (6 Tháng Gần Nhất)</h5>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Tháng</th>
                    <th>Tổng Tin Mới</th>
                    <th>Đã Duyệt</th>
                    <th>Chờ Duyệt</th>
                    <th>Lượt Xem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['monthly_posts'])): ?>
                    <?php foreach ($data['monthly_posts'] as $row): ?>
                    <tr>
                        <td class="fw-bold">Tháng <?= htmlspecialchars($row->thang) ?></td>
                        <td><?= number_format($row->tong_tin) ?></td>
                        <td class="text-success fw-bold"><?= number_format($row->da_duyet) ?></td>
                        <td class="text-warning fw-bold"><?= number_format($row->cho_duyet) ?></td>
                        <td><?= number_format($row->luot_xem) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-muted fst-italic">Chưa có dữ liệu trong 6 tháng gần nhất.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== 3. Khách Hàng Theo Trạng Thái ===== -->
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">3. Thống Kê Khách Hàng Theo Trạng Thái</h5>
    <?php
    $status_labels = [
        'moi'       => ['Mới', 'info'],
        'da_lien_he'=> ['Đã Liên Hệ', 'primary'],
        'tiem_nang' => ['Tiềm Năng', 'warning'],
        'thanh_cong'=> ['Thành Công', 'success'],
        'that_bai'  => ['Thất Bại', 'danger'],
    ];
    ?>
    <div class="table-responsive mb-5">
        <table class="table table-bordered text-center align-middle">
            <thead class="bg-success text-white">
                <tr>
                    <th>Trạng Thái</th>
                    <th>Số Lượng Khách</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['leads_by_status'])): ?>
                    <?php foreach ($data['leads_by_status'] as $row): 
                        $lbl = $status_labels[$row->trang_thai] ?? [$row->trang_thai, 'secondary'];
                    ?>
                    <tr>
                        <td><span class="badge bg-<?= $lbl[1] ?> px-3 py-2"><?= $lbl[0] ?></span></td>
                        <td class="fw-bold fs-5"><?= number_format($row->so_luong) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-muted fst-italic">Chưa có dữ liệu khách hàng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== 4. Top 5 Tin Xem Nhiều Nhất ===== -->
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">4. Top 5 Tin Đăng Được Xem Nhiều Nhất</h5>
    <div class="table-responsive mb-5">
        <table class="table table-bordered align-middle">
            <thead class="bg-warning text-dark">
                <tr>
                    <th>#</th>
                    <th>Tiêu Đề</th>
                    <th>Vị Trí</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Lượt Xem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['top_viewed'])): ?>
                    <?php foreach ($data['top_viewed'] as $i => $row): ?>
                    <tr>
                        <td class="fw-bold text-warning"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($row->tieu_de) ?></td>
                        <td class="small text-muted"><?= htmlspecialchars($row->vi_tri ?? '---') ?></td>
                        <td>
                            <?php if ($row->trang_thai == 'xuat_ban'): ?>
                                <span class="badge bg-success">Đang Chạy</span>
                            <?php elseif ($row->trang_thai == 'da_ban'): ?>
                                <span class="badge bg-secondary">Đã Bán</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Nháp</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-bold text-primary"><?= number_format($row->luot_xem) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-muted fst-italic">Chưa có dữ liệu.</td></tr>
                <?php endif; ?>
            </tbody>
    </div>

    <!-- ===== 5. Thống Kê Yêu Thích & So Sánh ===== -->
    <h5 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4">5. Báo Cáo Tương Tác Tin Đăng (Yêu thích & So sánh)</h5>
    
    <div class="mb-4">
        <a href="<?= URL_ROOT ?>/admin/bao-cao/exportCsv" class="btn btn-success btn-sm rounded-pill shadow-xs px-3">
            <i class="fa-solid fa-file-excel me-1.5"></i> Xuất Báo Cáo Tương Tác (Excel/CSV)
        </a>
    </div>

    <!-- Biểu đồ phân tích -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border shadow-xs p-3">
                <h6 class="fw-bold text-dark text-center mb-3">Top Tin Đăng Được Lưu Nhiều Nhất</h6>
                <div style="height: 280px; position: relative;">
                    <canvas id="chartSaved"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border shadow-xs p-3">
                <h6 class="fw-bold text-dark text-center mb-3">Top Tin Đăng Được So Sánh Nhiều Nhất</h6>
                <div style="height: 280px; position: relative;">
                    <canvas id="chartCompared"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng chi tiết -->
    <div class="row g-4 mb-4">
        <!-- Bảng Top Lưu -->
        <div class="col-md-6">
            <h6 class="fw-bold text-secondary mb-2"><i class="fa-solid fa-heart text-danger me-1"></i> Chi tiết Top Tin Được Lưu</h6>
            <div class="table-responsive border rounded animate-fade-in" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0 text-center">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th>Mã Tin</th>
                            <th class="text-start">Tiêu Đề</th>
                            <th>Lượt Lưu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['top_saved'])): ?>
                            <?php foreach ($data['top_saved'] as $row): ?>
                            <tr>
                                <td class="fw-bold"><?= $row->id ?></td>
                                <td class="text-start text-truncate" style="max-width: 200px;">
                                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= $row->duong_dan ?>" target="_blank" class="text-decoration-none text-dark fw-semibold">
                                        <?= htmlspecialchars($row->tieu_de) ?>
                                    </a>
                                </td>
                                <td class="fw-bold text-danger"><?= number_format($row->save_count) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-muted fst-italic">Chưa có dữ liệu.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bảng Top So Sánh -->
        <div class="col-md-6">
            <h6 class="fw-bold text-secondary mb-2"><i class="fa-solid fa-code-compare text-primary me-1"></i> Chi tiết Top Tin Được So Sánh</h6>
            <div class="table-responsive border rounded animate-fade-in" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0 text-center">
                    <thead class="bg-light sticky-top">
                        <tr>
                            <th>Mã Tin</th>
                            <th class="text-start">Tiêu Đề</th>
                            <th>Lượt So Sánh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['top_compared'])): ?>
                            <?php foreach ($data['top_compared'] as $row): ?>
                            <tr>
                                <td class="fw-bold"><?= $row->id ?></td>
                                <td class="text-start text-truncate" style="max-width: 200px;">
                                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= $row->duong_dan ?>" target="_blank" class="text-decoration-none text-dark fw-semibold">
                                        <?= htmlspecialchars($row->tieu_de) ?>
                                    </a>
                                </td>
                                <td class="fw-bold text-primary"><?= number_format($row->compare_count) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-muted fst-italic">Chưa có dữ liệu.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Script vẽ biểu đồ và tích hợp Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Dữ liệu biểu đồ Lưu tin
        const savedLabels = [];
        const savedValues = [];
        <?php if (!empty($data['top_saved'])): ?>
            <?php foreach ($data['top_saved'] as $row): ?>
                savedLabels.push("ID: " + <?= $row->id ?>);
                savedValues.push(<?= $row->save_count ?>);
            <?php endforeach; ?>
        <?php endif; ?>

        if (savedValues.length > 0) {
            new Chart(document.getElementById('chartSaved'), {
                type: 'bar',
                data: {
                    labels: savedLabels,
                    datasets: [{
                        label: 'Số lượt lưu',
                        data: savedValues,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // 2. Dữ liệu biểu đồ So sánh
        const comparedLabels = [];
        const comparedValues = [];
        <?php if (!empty($data['top_compared'])): ?>
            <?php foreach ($data['top_compared'] as $row): ?>
                comparedLabels.push("ID: " + <?= $row->id ?>);
                comparedValues.push(<?= $row->compare_count ?>);
            <?php endforeach; ?>
        <?php endif; ?>

        if (comparedValues.length > 0) {
            new Chart(document.getElementById('chartCompared'), {
                type: 'bar',
                data: {
                    labels: comparedLabels,
                    datasets: [{
                        label: 'Số lượt so sánh',
                        data: comparedValues,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
    </script>

    </div>

    <div class="mt-4 pt-3 border-top text-end">
        <p class="mb-5 small">Ngày ...... tháng ...... năm <?= date('Y') ?><br>
        <strong>Người Lập Báo Cáo</strong><br>(Ký và ghi rõ họ tên)</p>
    </div>
</div>

<!-- ==========================================
     CSS CHUYÊN BIỆT CHO PDF HÀNH CHÍNH SANG TRỌNG
     ========================================== -->
<style>
    .pdf-template-wrapper {
        display: none;
        width: 794px; /* Khổ rộng A4 tại 96 DPI */
        background: #ffffff; /* Nền trắng sạch sẽ */
        box-sizing: border-box;
    }
    
    .pdf-page {
        width: 794px;
        min-height: 1122px; /* Chiều cao A4 chuẩn tại 96 DPI */
        background: #ffffff;
        box-sizing: border-box;
        padding: 20mm 15mm 20mm 25mm; /* Lề hành chính chuẩn: Trái 25mm, Phải 15mm, Trên 20mm, Dưới 20mm */
        position: relative;
        font-family: 'Times New Roman', Times, serif;
        line-height: 1.5;
        color: #111827;
    }
    
    .pdf-page-break {
        page-break-before: always;
    }
    
    .pdf-header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        border: none !important;
    }
    
    .pdf-header-table td {
        border: none !important;
        padding: 0 !important;
        vertical-align: top;
    }
    
    .pdf-org-title {
        font-size: 11.5px;
        font-weight: bold;
        text-transform: uppercase;
        color: #0f2b5c;
        line-height: 1.3;
    }
    
    .pdf-org-sub {
        font-size: 10px;
        font-weight: bold;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 2px;
    }
    
    .pdf-org-line {
        width: 85px;
        height: 0.8px;
        background-color: #4b5563;
        margin: 4px 0 0 0;
    }
    
    .pdf-doc-no {
        font-size: 11px;
        color: #1f2937;
        margin-top: 8px;
        font-family: 'Times New Roman', Times, serif;
    }
    
    .pdf-national-title {
        font-size: 11.5px;
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
        color: #0f2b5c;
        letter-spacing: 0.2px;
    }
    
    .pdf-national-sub {
        font-size: 11px;
        font-weight: bold;
        text-align: center;
        color: #0f2b5c;
        margin-top: 3px;
    }
    
    .pdf-national-line {
        width: 135px;
        height: 1px;
        background-color: #0f2b5c;
        margin: 4px auto 0 auto;
    }
    
    .pdf-date-place {
        font-size: 11px;
        font-style: italic;
        text-align: right;
        color: #4b5563;
        margin-top: 8px;
        padding-right: 5px;
    }
    
    .pdf-report-title-container {
        text-align: center;
        margin-top: 30px;
        margin-bottom: 25px;
    }
    
    .pdf-report-main-title {
        font-size: 18px;
        font-weight: bold;
        color: #0f2b5c;
        text-transform: uppercase;
        margin: 0;
        letter-spacing: 0.5px;
        line-height: 1.3;
    }
    
    .pdf-report-sub-title {
        font-size: 11.5px;
        color: #4b5563;
        font-style: italic;
        margin-top: 6px;
    }
    
    .pdf-salutation {
        font-size: 13px;
        font-weight: bold;
        color: #0f2b5c;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .pdf-intro-text {
        font-size: 12px;
        line-height: 1.5;
        text-align: justify;
        color: #1f2937;
        margin-bottom: 15px;
        text-indent: 1cm;
    }
    
    .pdf-section-title {
        font-size: 13px;
        font-weight: bold;
        color: #0f2b5c;
        text-transform: uppercase;
        border-bottom: 1.8px solid #0f2b5c;
        padding-bottom: 3px;
        margin-top: 22px;
        margin-bottom: 12px;
        letter-spacing: 0.3px;
    }
    
    .pdf-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
        font-size: 11.5px;
    }
    
    .pdf-table th {
        background-color: #0f2b5c !important;
        color: #ffffff !important;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 10.5px;
        padding: 6px 8px;
        border: 1px solid #0f2b5c;
        letter-spacing: 0.2px;
        text-align: center;
        vertical-align: middle;
    }
    
    .pdf-table td {
        padding: 5px 8px;
        border: 1px solid #cbd5e1;
        color: #1f2937;
        vertical-align: middle;
    }
    
    .pdf-table tr:nth-child(even) {
        background-color: #f8fafc;
    }
    
    .pdf-table-summary {
        background-color: #f1f5f9 !important;
        font-weight: bold;
    }
    
    .pdf-table-summary td {
        border-top: 1.5px solid #0f2b5c;
        border-bottom: 1.5px solid #0f2b5c;
    }
    
    .text-left { text-align: left; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .fw-bold { font-weight: bold; }
    .text-success { color: #16a34a !important; }
    .text-primary { color: #2563eb !important; }
    .text-danger { color: #dc2626 !important; }
    .text-warning { color: #d97706 !important; }
    
    .pdf-badge {
        display: inline-block;
        padding: 2px 6px;
        font-weight: bold;
        font-size: 9px;
        text-transform: uppercase;
        border-radius: 3px;
        border: 1px solid currentColor;
    }
    
    .pdf-badge-info { color: #1e3a8a; background-color: #eff6ff; }
    .pdf-badge-primary { color: #0369a1; background-color: #f0f9ff; }
    .pdf-badge-warning { color: #b45309; background-color: #fffbeb; }
    .pdf-badge-success { color: #15803d; background-color: #f0fdf4; }
    .pdf-badge-danger { color: #b91c1c; background-color: #fef2f2; }
    .pdf-badge-secondary { color: #4b5563; background-color: #f9fafb; }
    
    .pdf-signature-section {
        margin-top: 30px;
        width: 100%;
        border-collapse: collapse;
        border: none !important;
    }
    
    .pdf-signature-section td {
        border: none !important;
        width: 50%;
        text-align: center;
        vertical-align: top;
        font-size: 12px;
        padding: 0 !important;
        line-height: 1.4;
    }
</style>

<!-- ==========================================
     TEMPLATE PDF HÀNH CHÍNH SANG TRỌNG
     ========================================== -->
<div id="report-pdf-template" class="pdf-template-wrapper">
    <!-- ================= PAGE 1 ================= -->
    <div class="pdf-page">
        <!-- Header: Quốc hiệu, Tiêu ngữ & Đơn vị chủ quản -->
        <table class="pdf-header-table">
            <tr>
                <td style="width: 50%; text-align: left;">
                    <div class="pdf-org-title">CÔNG TY CỔ PHẦN BẤT ĐỘNG SẢN CITC</div>
                    <div class="pdf-org-sub">HỆ THỐNG VẬN HÀNH FULLHOUSE</div>
                    <div class="pdf-org-line"></div>
                    <div class="pdf-doc-no">Số: <?= date('Ymd') ?>/BC-CITC</div>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="pdf-national-title">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                    <div class="pdf-national-sub">Độc lập - Tự do - Hạnh phúc</div>
                    <div class="pdf-national-line"></div>
                    <div class="pdf-date-place">Hà Nội, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?></div>
                </td>
            </tr>
        </table>

        <!-- Tên Báo cáo -->
        <div class="pdf-report-title-container">
            <h1 class="pdf-report-main-title">BÁO CÁO KẾT QUẢ HOẠT ĐỘNG</h1>
            <h2 class="pdf-report-main-title" style="font-size: 14px; margin-top: 5px;">KINH DOANH & PHÁT TRIỂN HỆ THỐNG</h2>
            <div class="pdf-report-sub-title">(Kỳ báo cáo: Từ đầu năm 2026 đến ngày trích xuất: <?= date('d/m/Y H:i:s') ?>)</div>
        </div>

        <!-- Kính gửi -->
        <div class="pdf-salutation">
            Kính gửi: Ban Giám đốc Công ty Cổ phần Bất động sản CITC
        </div>

        <!-- Lời mở đầu -->
        <p class="pdf-intro-text">
            Căn cứ vào chức năng quản trị vận hành và dữ liệu kinh doanh thực tế trên nền tảng công nghệ Bất động sản CITC FullHouse, Ban Quản trị hệ thống trân trọng báo cáo Ban Giám đốc chi tiết về kết quả hoạt động kinh doanh, thống kê tài chính doanh thu nạp tiền và tình hình phân bố sản phẩm tin đăng tính đến ngày <?= date('d/m/Y') ?> như sau:
        </p>

        <!-- Phần I: Hoạt động hệ thống -->
        <div class="pdf-section-title">I. Thống Kê Hoạt Động & Chỉ Số Tăng Trưởng Hệ Thống</div>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="width: 45%; text-align: left;">Chỉ Số Phát Triển Hệ Thống</th>
                    <th style="width: 20%; text-align: center;">Số Liệu Lũy Kế</th>
                    <th style="width: 35%; text-align: left;">Chi Tiết Phát Sinh / Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left fw-bold">1. Tổng số thành viên đăng ký</td>
                    <td class="text-center fw-bold"><?= number_format($data['total_users']) ?></td>
                    <td class="text-left text-success fw-bold">+<?= number_format($data['new_users_month']) ?> tài khoản mới tháng này</td>
                </tr>
                <tr>
                    <td class="text-left fw-bold">2. Tổng số tin đăng bất động sản</td>
                    <td class="text-center fw-bold"><?= number_format($data['total_posts']) ?></td>
                    <td class="text-left"><?= number_format($data['active_posts']) ?> tin đăng đang hiển thị hoạt động</td>
                </tr>
                <tr>
                    <td class="text-left fw-bold">3. Tin đăng đang chờ phê duyệt</td>
                    <td class="text-center fw-bold text-danger"><?= number_format($data['pending_posts']) ?></td>
                    <td class="text-left text-danger">Yêu cầu cần xử lý duyệt trên Admin</td>
                </tr>
                <tr>
                    <td class="text-left fw-bold">4. Tổng lượt xem tin (Traffic)</td>
                    <td class="text-center fw-bold"><?= number_format($data['total_views']) ?></td>
                    <td class="text-left">Lũy kế toàn bộ lượt xem tin đăng</td>
                </tr>
                <tr>
                    <td class="text-left fw-bold">5. Tổng số khách hàng gửi liên hệ</td>
                    <td class="text-center fw-bold"><?= number_format($data['total_leads']) ?></td>
                    <td class="text-left">Thu thập qua Form tư vấn nhanh</td>
                </tr>
                <tr>
                    <td class="text-left fw-bold">6. Giao dịch tư vấn thành công</td>
                    <td class="text-center fw-bold text-success"><?= number_format($data['leads_success']) ?></td>
                    <td class="text-left text-success">Số lượng lead đã chốt thành công</td>
                </tr>
            </tbody>
        </table>

        <!-- Phần II: Báo cáo tài chính doanh thu -->
        <div class="pdf-section-title">II. Báo Cáo Tài Chính & Doanh Thu Dịch Vụ Cung Cấp</div>
        
        <!-- Tóm tắt tài chính -->
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tổng Thực Thu Lũy Kế</th>
                    <th style="width: 25%;">Thực Thu Trong Tháng</th>
                    <th style="width: 25%;">Giá Trị Dịch Vụ Đã Bán</th>
                    <th style="width: 25%;">Giao Dịch Chờ Duyệt</th>
                </tr>
            </thead>
            <tbody>
                <tr class="text-center fw-bold" style="font-size: 12.5px;">
                    <td class="text-success"><?= number_format($data['total_revenue']) ?> đ</td>
                    <td class="text-primary"><?= number_format($data['revenue_month']) ?> đ</td>
                    <td class="text-warning"><?= number_format($data['total_spending']) ?> đ</td>
                    <td class="text-danger"><?= number_format($data['pending_payments']) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Doanh thu nạp tiền ròng -->
        <div style="font-size: 11.5px; font-weight: bold; color: #0f2b5c; margin-top: 12px; margin-bottom: 6px;">
            1. Bảng phân bổ doanh thu nạp tiền ròng theo tháng (Đã khấu trừ khuyến mãi & thuế 10%):
        </div>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 18%;">Tháng</th>
                    <th style="text-align: center; width: 14%;">Số GD</th>
                    <th style="text-align: right; width: 22%;">Tổng Tiền Nạp</th>
                    <th style="text-align: right; width: 22%;">Tiền Khuyến Mãi</th>
                    <th style="text-align: right; width: 24%;">Doanh Thu Ròng (Thực Thu)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pdf_grand_total = 0;
                if (!empty($data['monthly_revenue'])):
                    foreach ($data['monthly_revenue'] as $rev):
                        $khuyen_mai = $rev->tong_khuyen_mai;
                        $thue = $rev->tong_nap * 0.10;
                        $rong = $rev->tong_nap - $khuyen_mai - $thue;
                        $pdf_grand_total += $rong;
                ?>
                <tr>
                    <td class="text-center fw-bold">Tháng <?= htmlspecialchars($rev->thang) ?></td>
                    <td class="text-center"><?= number_format($rev->so_giao_dich) ?></td>
                    <td class="text-right text-success">+<?= number_format($rev->tong_nap) ?> đ</td>
                    <td class="text-right text-primary">-<?= number_format($khuyen_mai) ?> đ</td>
                    <td class="text-right fw-bold" style="color: #0f2b5c;"><?= number_format($rong) ?> đ</td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center text-muted fst-italic">Chưa có dữ liệu nạp tiền.</td></tr>
                <?php endif; ?>
                <?php if (!empty($data['monthly_revenue'])): ?>
                <tr class="pdf-table-summary">
                    <td colspan="4" class="text-right" style="text-transform: uppercase; font-size: 10px; padding: 6px 8px;">Tổng thực thu ròng lũy kế:</td>
                    <td class="text-right text-success fw-bold" style="font-size: 12px;"><?= number_format($pdf_grand_total) ?> đ</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ================= PAGE 2 ================= -->
    <div class="pdf-page pdf-page-break">
        <!-- Tiêu đề lặp lại ở trang 2 để bảo đảm tính nhất quán hành chính -->
        <table class="pdf-header-table" style="margin-bottom: 15px;">
            <tr>
                <td style="width: 60%; text-align: left;">
                    <div class="pdf-org-title">CÔNG TY CỔ PHẦN BẤT ĐỘNG SẢN CITC</div>
                    <div class="pdf-org-sub">HỆ THỐNG VẬN HÀNH FULLHOUSE</div>
                    <div class="pdf-org-line" style="width: 80px; background-color: #cbd5e1;"></div>
                </td>
                <td style="width: 40%; text-align: right; font-size: 11px; font-style: italic; color: #4b5563;">
                    Báo cáo kết quả hoạt động kinh doanh (Trang 2)
                </td>
            </tr>
        </table>

        <!-- Bảng Dịch vụ đã bán -->
        <div style="font-size: 11.5px; font-weight: bold; color: #0f2b5c; margin-bottom: 6px;">
            2. Bảng thống kê giá trị gói dịch vụ tin đăng đã cung cấp (Tiền ảo khách tiêu dùng):
        </div>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 25%;">Tháng báo cáo</th>
                    <th style="text-align: right; width: 25%;">Giá Trị Gói VIP Đã Bán</th>
                    <th style="text-align: right; width: 25%;">Giá Trị Gói UP Đã Bán</th>
                    <th style="text-align: right; width: 25%;">Tổng Giá Trị Dịch Vụ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $pdf_total_services = 0;
                if (!empty($data['monthly_spending'])):
                    foreach ($data['monthly_spending'] as $sp):
                        $chi_vip = $sp->chi_vip;
                        $chi_up  = $sp->chi_up;
                        $tong_thang = $chi_vip + $chi_up;
                        $pdf_total_services += $tong_thang;
                ?>
                <tr>
                    <td class="text-center fw-bold">Tháng <?= htmlspecialchars($sp->thang) ?></td>
                    <td class="text-right text-warning"><?= number_format($chi_vip) ?> đ</td>
                    <td class="text-right text-warning"><?= number_format($chi_up) ?> đ</td>
                    <td class="text-right fw-bold text-danger"><?= number_format($tong_thang) ?> đ</td>
                </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-center text-muted fst-italic">Chưa có dữ liệu giao dịch dịch vụ.</td></tr>
                <?php endif; ?>
                <?php if (!empty($data['monthly_spending'])): ?>
                <tr class="pdf-table-summary">
                    <td colspan="3" class="text-right" style="text-transform: uppercase; font-size: 10px; padding: 6px 8px;">Tổng giá trị dịch vụ lũy kế:</td>
                    <td class="text-right text-danger fw-bold" style="font-size: 12px;"><?= number_format($pdf_total_services) ?> đ</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phần III: Phân tích tin đăng & tiến độ khách hàng CRM -->
        <div class="pdf-section-title">III. Phân Tích Tin Đăng & Tiến Độ Khách Hàng CRM</div>
        
        <!-- Phân tích tin đăng theo tháng -->
        <div style="font-size: 11.5px; font-weight: bold; color: #0f2b5c; margin-bottom: 6px;">
            1. Phân bổ tin đăng mới và lượt xem hệ thống (6 tháng gần nhất):
        </div>
        <table class="pdf-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 20%;">Tháng</th>
                    <th style="text-align: center; width: 20%;">Tổng Tin Đăng Mới</th>
                    <th style="text-align: center; width: 20%;">Tin Đã Duyệt</th>
                    <th style="text-align: center; width: 20%;">Tin Chờ Phê Duyệt</th>
                    <th style="text-align: center; width: 20%;">Tổng Lượt Xem (Views)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['monthly_posts'])): ?>
                    <?php foreach ($data['monthly_posts'] as $row): ?>
                    <tr class="text-center">
                        <td class="fw-bold">Tháng <?= htmlspecialchars($row->thang) ?></td>
                        <td><?= number_format($row->tong_tin) ?></td>
                        <td class="text-success fw-bold"><?= number_format($row->da_duyet) ?></td>
                        <td class="text-warning fw-bold"><?= number_format($row->cho_duyet) ?></td>
                        <td class="fw-bold" style="color: #0f2b5c;"><?= number_format($row->luot_xem) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted fst-italic">Chưa có dữ liệu tin đăng mới.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Trạng thái khách hàng CRM và Top 5 tin xem nhiều nhất -->
        <table class="pdf-header-table" style="margin-top: 15px; margin-bottom: 0;">
            <tr>
                <td style="width: 48%; vertical-align: top;">
                    <div style="font-size: 11.5px; font-weight: bold; color: #0f2b5c; margin-bottom: 6px;">
                        2. Phân bổ khách hàng tiềm năng CRM theo trạng thái:
                    </div>
                    <table class="pdf-table">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 60%;">Trạng Thái Chăm Sóc</th>
                                <th style="text-align: center; width: 40%;">Số Lượng Khách</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['leads_by_status'])): ?>
                                <?php foreach ($data['leads_by_status'] as $row): 
                                    $lbl = $status_labels[$row->trang_thai] ?? [$row->trang_thai, 'secondary'];
                                ?>
                                <tr class="text-center">
                                    <td><span class="pdf-badge pdf-badge-<?= $lbl[1] ?>"><?= $lbl[0] ?></span></td>
                                    <td class="fw-bold" style="font-size: 12px;"><?= number_format($row->so_luong) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center text-muted fst-italic">Chưa có dữ liệu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top;">
                    <div style="font-size: 11.5px; font-weight: bold; color: #0f2b5c; margin-bottom: 6px;">
                        3. Top 5 tin đăng có lượt xem cao nhất hệ thống:
                    </div>
                    <table class="pdf-table" style="font-size: 10.5px;">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 15%;">Hạng</th>
                                <th style="text-align: left; width: 65%;">Tiêu Đề Tin Đăng Nổi Bật</th>
                                <th style="text-align: center; width: 20%;">Lượt Xem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['top_viewed'])): ?>
                                <?php foreach (array_slice($data['top_viewed'], 0, 5) as $i => $row): ?>
                                <tr>
                                    <td class="text-center fw-bold text-warning"><?= $i + 1 ?></td>
                                    <td class="text-left" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                                        <strong><?= htmlspecialchars(mb_substr($row->tieu_de, 0, 32)) ?>...</strong>
                                    </td>
                                    <td class="text-center fw-bold" style="color: #0f2b5c;"><?= number_format($row->luot_xem) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted fst-italic">Chưa có dữ liệu.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Phần IV: Ý kiến phê duyệt & chữ ký -->
        <div class="pdf-section-title" style="margin-top: 15px;">IV. Kết Luận & Đề Xuất Phê Duyệt</div>
        <p class="pdf-intro-text" style="margin-bottom: 20px;">
            Ban Quản trị nền tảng trân trọng kính trình Ban Giám đốc Công ty xem xét, phê duyệt kết quả hoạt động kinh doanh lũy kế của hệ thống. Chúng tôi xin cam kết tiếp tục hoàn thiện, nâng cấp chất lượng kỹ thuật của hệ thống và gia tăng chất lượng dịch vụ chăm sóc khách hàng trong các kỳ kinh doanh tiếp theo.
        </p>

        <!-- Ký tên -->
        <table class="pdf-signature-section">
            <tr>
                <td style="width: 50%; text-align: left; padding-left: 20px !important;">
                    <div style="font-weight: bold; text-transform: uppercase; color: #0f2b5c;">PHÊ DUYỆT CỦA BAN GIÁM ĐỐC</div>
                    <div style="font-size: 10px; font-style: italic; color: #4b5563; margin-top: 3px;">(Ký, đóng dấu và ghi rõ họ tên)</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; color: #cbd5e1;">............................................................</div>
                    
                    <!-- Nơi nhận -->
                    <div style="margin-top: 20px; font-size: 10px; line-height: 1.4; text-align: left;">
                        <div style="font-weight: bold; font-style: italic; text-decoration: underline;">Nơi nhận:</div>
                        <div style="margin-left: 5px;">
                            - Như trên;<br>
                            - HĐQT (để b/c);<br>
                            - Lưu: VT, BQT hệ thống.
                        </div>
                    </div>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div style="font-weight: bold; text-transform: uppercase; color: #0f2b5c;">NGƯỜI LẬP BÁO CÁO</div>
                    <div style="font-size: 10px; font-style: italic; color: #4b5563; margin-top: 3px;">(Ký và ghi rõ họ tên)</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; color: #0f2b5c; font-size: 13px;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Trưởng Ban Vận Hành') ?></div>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
function exportPDF() {
    // 1. Tạo và hiển thị Loading Overlay sang trọng màu Navy đậm đồng bộ thương hiệu
    const loading = document.createElement('div');
    loading.style.position = 'fixed';
    loading.style.left = '0';
    loading.style.top = '0';
    loading.style.width = '100vw';
    loading.style.height = '100vh';
    loading.style.backgroundColor = 'rgba(15, 43, 92, 0.96)'; // Xanh navy đậm sang trọng
    loading.style.zIndex = '100000';
    loading.style.display = 'flex';
    loading.style.flexDirection = 'column';
    loading.style.justifyContent = 'center';
    loading.style.alignItems = 'center';
    loading.style.color = '#ffffff';
    loading.style.fontFamily = 'system-ui, -apple-system, sans-serif';
    
    loading.innerHTML = `
        <div class="spinner-border text-light mb-3" role="status" style="width: 3rem; height: 3rem; border-width: 0.25em;"></div>
        <h4 class="fw-bold m-0 text-white" style="letter-spacing: 1px;">ĐANG TẠO BÁO CÁO HÀNH CHÍNH A4</h4>
        <p class="text-white-50 mt-2 mb-0" style="font-size: 14px;">Hệ thống đang kết xuất dữ liệu và dàn trang PDF. Vui lòng chờ...</p>
    `;
    document.body.appendChild(loading);
    
    const element = document.getElementById('report-pdf-template');
    
    // 2. Kích hoạt hiển thị template trong luồng DOM bình thường để trình duyệt vẽ hoàn toàn
    element.style.display = 'block';
    
    const opt = {
        margin:       0,
        filename:     'Bao_Cao_KinhDoanh_CITC_BDS_<?= date('dmY') ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true, 
            logging: false,
            scrollX: 0,
            scrollY: 0
        },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
    };
    
    // 3. Chờ 300ms để trình duyệt thực hiện xong chu kỳ Reflow và Repaint trước khi chụp
    setTimeout(() => {
        html2pdf().set(opt).from(element).save().then(() => {
            // Dọn dẹp và ẩn template lại
            document.body.removeChild(loading);
            element.style.display = 'none';
        }).catch(err => {
            console.error('PDF export error:', err);
            if (loading.parentNode) {
                document.body.removeChild(loading);
            }
            element.style.display = 'none';
        });
    }, 300);
}
</script>

@include('admin.layouts.footer')

