@include('admin.layouts.header')

<style>
    /* Premium Dashboard Styles */
    .dashboard-container {
        padding: 0.5rem 0 2rem 0;
    }
    
    /* Welcome Banner - Light Theme */
    .welcome-banner {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
        border: 1px solid rgba(59, 130, 246, 0.15) !important;
        border-left: 5px solid #3b82f6 !important;
        position: relative;
        overflow: hidden;
        border-radius: 16px;
    }
    
    .welcome-banner::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }
    
    .welcome-title {
        color: #1e3a8a !important;
        font-weight: 700;
    }
    
    .welcome-text {
        color: #4b5563 !important;
        font-weight: 500;
    }
    
    /* Stat Cards - Premium Dark Gradient Theme */
    .stat-card {
        border-radius: 16px !important;
        border: none !important;
        overflow: hidden;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12) !important;
    }
    
    .stat-card-blue {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    }
    .stat-card-blue:hover {
        box-shadow: 0 15px 30px rgba(37, 99, 235, 0.35) !important;
    }
    
    .stat-card-green {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    }
    .stat-card-green:hover {
        box-shadow: 0 15px 30px rgba(16, 185, 129, 0.35) !important;
    }
    
    .stat-card-amber {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
    }
    .stat-card-amber:hover {
        box-shadow: 0 15px 30px rgba(217, 119, 6, 0.35) !important;
    }
    
    .stat-card-purple {
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
    }
    .stat-card-purple:hover {
        box-shadow: 0 15px 30px rgba(124, 58, 237, 0.35) !important;
    }
    
    .stat-card-body {
        padding: 1.75rem 1.5rem !important;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        position: relative;
        z-index: 1;
    }
    
    .stat-card-icon {
        position: absolute;
        right: 1.25rem;
        top: 1.25rem;
        font-size: 3rem;
        opacity: 0.18;
        transition: all 0.3s ease;
        color: #ffffff;
    }
    
    .stat-card:hover .stat-card-icon {
        opacity: 0.3;
        transform: scale(1.15) rotate(8deg);
    }
    
    .stat-card-label {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 0.6rem;
    }
    
    .stat-card-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #ffffff;
        line-height: 1;
        margin-bottom: 1.25rem;
        font-family: 'Source Sans 3', sans-serif;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .stat-card-footer {
        margin-top: auto;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        padding: 0.85rem 1.5rem !important;
        background: rgba(0, 0, 0, 0.1);
    }
    
    .stat-card-link {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .stat-card-link:hover {
        color: #ffffff;
    }
    
    .stat-card-link i {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card-link:hover i {
        transform: translateX(6px);
    }
    
    /* Charts & Table Dashboard specific styles */
    .dashboard-card {
        background: #fff;
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }
    .dashboard-card-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
        font-weight: 700;
        color: #1e293b;
    }
    .dashboard-card-body {
        padding: 1.5rem;
    }
    .table-responsive {
        border-radius: 0 0 16px 16px;
    }
    .table thead th {
        background-color: #f8fafc;
        border-bottom-width: 1px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
    }
    .table tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        color: #334155;
        font-size: 0.875rem;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .action-btn-approve:hover { background: #dcfce7; color: #166534; }
    .action-btn-reject:hover { background: #fee2e2; color: #991b1b; }
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard-container">
    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-banner p-4 shadow-sm">
                <div class="position-relative z-index-1">
                    <h4 class="welcome-title mb-1">Chào mừng quay trở lại, <?= Session::get('user_name') ?>! 👋</h4>
                    <p class="welcome-text mb-0">Hôm nay là <?= date('d/m/Y') ?>. Hệ thống đang hoạt động ổn định và sẵn sàng hỗ trợ bạn quản lý.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4">
        <!-- Total Projects Card -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-blue">
                <div class="stat-card-body">
                    <i class="fa-solid fa-building stat-card-icon"></i>
                    <div class="stat-card-label">Tổng Số Dự Án</div>
                    <div class="stat-card-value"><?= number_format($data['total_projects']) ?></div>
                </div>
                <div class="stat-card-footer">
                    <a href="<?= URL_ROOT ?>/admin/du-an" class="stat-card-link">
                        <span>Xem Chi Tiết</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- New Leads Card -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-green">
                <div class="stat-card-body">
                    <i class="fa-solid fa-users stat-card-icon"></i>
                    <div class="stat-card-label">Khách Hàng Mới</div>
                    <div class="stat-card-value"><?= number_format($data['total_leads']) ?></div>
                </div>
                <div class="stat-card-footer">
                    <a href="<?= URL_ROOT ?>/admin/khach-hang" class="stat-card-link">
                        <span>Xem Chi Tiết</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Posts Card -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-amber">
                <div class="stat-card-body">
                    <i class="fa-solid fa-square-check stat-card-icon"></i>
                    <div class="stat-card-label">Tin Đang Hoạt Động</div>
                    <div class="stat-card-value"><?= number_format($data['active_projects']) ?></div>
                </div>
                <div class="stat-card-footer">
                    <a href="<?= URL_ROOT ?>/admin/du-an" class="stat-card-link">
                        <span>Xem Chi Tiết</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Users Card -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-card-purple">
                <div class="stat-card-body">
                    <i class="fa-solid fa-user-shield stat-card-icon"></i>
                    <div class="stat-card-label">Tổng Số Người Dùng</div>
                    <div class="stat-card-value"><?= number_format($data['total_users']) ?></div>
                </div>
                <div class="stat-card-footer">
                    <a href="<?= URL_ROOT ?>/admin/nguoi-dung" class="stat-card-link">
                        <span>Xem Chi Tiết</span>
                        <i class="fa-solid fa-arrow-right-long"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fs-6"><i class="fa-solid fa-chart-line text-primary me-2"></i>Tin đăng & Dự án mới (6 Tháng qua)</h5>
                </div>
                <div class="dashboard-card-body">
                    <canvas id="mainChart" style="min-height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header">
                    <h5 class="mb-0 fs-6"><i class="fa-solid fa-chart-pie text-success me-2"></i>Tỷ lệ Danh mục</h5>
                </div>
                <div class="dashboard-card-body d-flex justify-content-center align-items-center">
                    <canvas id="pieChart" style="max-height: 280px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         ADMIN ADVANCED ANALYTICS SECTION
         ========================================== -->
    <div class="row g-4 mb-4">
        <!-- Biểu đồ doanh thu -->
        <div class="col-lg-6">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header">
                    <h5 class="mb-0 fs-6"><i class="fa-solid fa-money-bill-trend-up text-success me-2"></i>Thống kê doanh thu nạp tiền (5 tháng qua)</h5>
                </div>
                <div class="dashboard-card-body">
                    <canvas id="revenueChart" style="min-height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Biểu đồ View, Chat, Call toàn trang -->
        <div class="col-lg-6">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header">
                    <h5 class="mb-0 fs-6"><i class="fa-solid fa-chart-line text-info me-2"></i>Lượt tương tác toàn hệ thống</h5>
                </div>
                <div class="dashboard-card-body">
                    <canvas id="interactionsChart" style="min-height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Hàng Top Rankings -->
    <div class="row g-4 mb-4">
        <!-- Top bài đăng xem nhiều nhất -->
        <div class="col-md-6 col-lg-3">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header"><h5 class="mb-0 fs-6 small fw-bold">Top Tin Xem Nhiều Nhất</h5></div>
                <div class="dashboard-card-body p-2">
                    <ul class="list-group list-group-flush">
                        <?php foreach($top_posts as $post): ?>
                            <li class="list-group-item px-1 py-2 small d-flex justify-content-between align-items-center">
                                <span class="text-truncate me-2" style="max-width: 150px;"><?= htmlspecialchars($post->tieu_de) ?></span>
                                <span class="badge bg-primary rounded-pill"><?= number_format($post->views) ?> view</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Top người đăng tin nhiều nhất -->
        <div class="col-md-6 col-lg-3">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header"><h5 class="mb-0 fs-6 small fw-bold">Top Người Đăng Tin</h5></div>
                <div class="dashboard-card-body p-2">
                    <ul class="list-group list-group-flush">
                        <?php foreach($top_authors as $author): ?>
                            <li class="list-group-item px-1 py-2 small d-flex justify-content-between align-items-center">
                                <span class="text-truncate me-2" style="max-width: 150px;"><?= htmlspecialchars($author->ten) ?></span>
                                <span class="badge bg-success rounded-pill"><?= $author->total_posts ?> tin</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Top khu vực -->
        <div class="col-md-6 col-lg-3">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header"><h5 class="mb-0 fs-6 small fw-bold">Top Tỉnh Thành</h5></div>
                <div class="dashboard-card-body p-2">
                    <ul class="list-group list-group-flush">
                        <?php foreach($top_regions as $region): ?>
                            <li class="list-group-item px-1 py-2 small d-flex justify-content-between align-items-center">
                                <span><?= htmlspecialchars($region->tinh_thanh) ?></span>
                                <span class="badge bg-info rounded-pill"><?= $region->total_posts ?> tin</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Top loại BDS -->
        <div class="col-md-6 col-lg-3">
            <div class="dashboard-card h-100 mb-0">
                <div class="dashboard-card-header"><h5 class="mb-0 fs-6 small fw-bold">Top Loại Bất Động Sản</h5></div>
                <div class="dashboard-card-body p-2">
                    <ul class="list-group list-group-flush">
                        <?php foreach($top_types as $type): ?>
                            <li class="list-group-item px-1 py-2 small d-flex justify-content-between align-items-center">
                                <span class="text-truncate me-2" style="max-width: 150px;"><?= htmlspecialchars($type->loai_bat_dong_san) ?></span>
                                <span class="badge bg-secondary rounded-pill"><?= $type->total_posts ?> tin</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Pending Posts Table -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card">
                <div class="dashboard-card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fs-6"><i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Tin đăng chờ duyệt mới nhất</h5>
                    <a href="<?= URL_ROOT ?>/admin/du-an" class="btn btn-sm btn-outline-primary rounded-pill px-3">Xem tất cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tiêu đề</th>
                                <th>Chuyên mục</th>
                                <th>Người đăng</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['pending_posts'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Không có tin đăng nào chờ duyệt</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data['pending_posts'] as $post): ?>
                                    <tr id="row-<?= $post->id ?>" style="transition: opacity 0.5s ease, transform 0.5s ease;">
                                        <td class="fw-semibold">#<?= $post->id ?></td>
                                        <td>
                                            <a href="<?= URL_ROOT ?>/du-an/detail/<?= urlencode($post->duong_dan) ?>" target="_blank" class="text-decoration-none fw-semibold text-dark">
                                                <?= htmlspecialchars($post->tieu_de) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = 'bg-primary';
                                            if (strpos(mb_strtolower($post->ten_danh_muc, 'UTF-8'), 'thuê') !== false) $badgeClass = 'bg-success';
                                            elseif (strpos(mb_strtolower($post->ten_danh_muc, 'UTF-8'), 'dự án') !== false) $badgeClass = 'bg-warning';
                                            ?>
                                            <span class="badge <?= $badgeClass ?> bg-opacity-10 text-<?= str_replace('bg-', '', $badgeClass) ?> border border-<?= str_replace('bg-', '', $badgeClass) ?>-subtle rounded-pill">
                                                <?= htmlspecialchars($post->ten_danh_muc) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php $avatar = !empty($post->anh_dai_dien) ? URL_ROOT . '/public/uploads/avatars/' . $post->anh_dai_dien : 'https://ui-avatars.com/api/?name=' . urlencode($post->ten_nguoi_dung ?? 'User') . '&background=random'; ?>
                                                <img src="<?= $avatar ?>" class="rounded-circle" width="28" height="28" alt="Avatar">
                                                <span class="fw-medium"><?= htmlspecialchars($post->ten_nguoi_dung ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($post->ngay_tao)) ?></td>
                                        <td class="text-end">
                                            <button onclick="handleAction(<?= $post->id ?>, 'approve')" class="btn btn-sm btn-success action-btn action-btn-approve me-1" title="Duyệt"><i class="fa-solid fa-check"></i></button>
                                            <button onclick="handleAction(<?= $post->id ?>, 'reject')" class="btn btn-sm btn-danger action-btn action-btn-reject" title="Từ chối"><i class="fa-solid fa-xmark"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.layouts.footer')

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Inject PHP data into JS securely
    const rawCategories = <?= json_encode($data['chart_categories'] ?? []) ?>;
    const rawMonths = <?= json_encode($data['chart_months'] ?? []) ?>;

    const pieLabels = rawCategories.map(item => item.danh_muc);
    const pieData = rawCategories.map(item => item.so_luong);

    const lineLabels = rawMonths.map(item => item.thang);
    const lineTinDang = rawMonths.map(item => item.tin_dang_moi);
    const lineDuAn = rawMonths.map(item => item.du_an_moi);

    // Default colors if categories exceed standard 4
    const bgColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#06b6d4'];

    // Check if chart contexts exist
    const mainChartElem = document.getElementById('mainChart');
    const pieChartElem = document.getElementById('pieChart');
    
    if (mainChartElem && pieChartElem) {
        // Gradient definitions for Line Chart
        const ctxMain = mainChartElem.getContext('2d');
        const gradientBlue = ctxMain.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
        gradientBlue.addColorStop(1, 'rgba(37, 99, 235, 0)');

        const gradientGreen = ctxMain.createLinearGradient(0, 0, 0, 400);
        gradientGreen.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
        gradientGreen.addColorStop(1, 'rgba(16, 185, 129, 0)');

        // Initialize Line Chart
        new Chart(ctxMain, {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: [
                    {
                        label: 'Tin Đăng Mới',
                        data: lineTinDang,
                        borderColor: '#2563eb',
                        backgroundColor: gradientBlue,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Dự Án Mới',
                        data: lineDuAn,
                        borderColor: '#059669',
                        backgroundColor: gradientGreen,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#059669',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, font: { family: 'inherit' } } },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [4, 4] } },
                    x: { grid: { display: false } }
                },
                interaction: { mode: 'index', intersect: false }
            }
        });

        // Initialize Doughnut Chart
        const ctxPie = pieChartElem.getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: bgColors.slice(0, pieLabels.length),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { family: 'inherit' } } },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });

        // 3. Doanh thu nạp tiền chart
        const rawRevenue = <?= json_encode($data['revenue_chart'] ?? []) ?>;
        const revLabels = rawRevenue.map(item => item.period);
        const revData = rawRevenue.map(item => item.total_revenue);

        const revChartElem = document.getElementById('revenueChart');
        if (revChartElem) {
            new Chart(revChartElem, {
                type: 'bar',
                data: {
                    labels: revLabels,
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: revData,
                        backgroundColor: '#10b981',
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4] } }
                    }
                }
            });
        }

        // 4. Lượt tương tác chart
        const rawInteractions = <?= json_encode($data['interactions_chart'] ?? []) ?>;
        const intLabels = rawInteractions.map(item => item.period);
        const intViews = rawInteractions.map(item => item.total_views);
        const intChats = rawInteractions.map(item => item.total_chats);
        const intCalls = rawInteractions.map(item => item.total_calls);

        const intChartElem = document.getElementById('interactionsChart');
        if (intChartElem) {
            new Chart(intChartElem, {
                type: 'line',
                data: {
                    labels: intLabels,
                    datasets: [
                        {
                            label: 'Lượt xem',
                            data: intViews,
                            borderColor: '#3b82f6',
                            borderWidth: 2.5,
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Lượt Chat',
                            data: intChats,
                            borderColor: '#06b6d4',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Cuộc gọi',
                            data: intCalls,
                            borderColor: '#ef4444',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4] } }
                    }
                }
            });
        }
    }
});

// AJAX Handle Approve / Reject
window.handleAction = async function(id, action) {
    const accepted = await AdminDialog.confirm(
        'Bạn có chắc chắn muốn ' + (action === 'approve' ? 'Duyệt' : 'Từ chối') + ' tin đăng này không?',
        { title: action === 'approve' ? 'Duyệt tin đăng?' : 'Từ chối tin đăng?', type: action === 'approve' ? 'success' : 'warning', confirmText: action === 'approve' ? 'Duyệt tin' : 'Từ chối' }
    );
    if (!accepted) return;

    fetch('<?= URL_ROOT ?>/admin/du-an/' + action + '/' + id, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const row = document.getElementById('row-' + id);
            if (row) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 500);
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối tới máy chủ!');
    });
}
</script>
