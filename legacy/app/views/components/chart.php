<?php
/**
 * Component Biểu đồ - Hiển thị biểu đồ đường, cột, tròn cho Trang chi tiết và Dashboard Analytics.
 * Nhận tham số $daily (hoặc $analytics['daily']), $sources (hoặc $analytics['sources']).
 */
$dailyData = $daily ?? $analytics['daily'] ?? ['labels'=>[], 'views'=>[], 'calls'=>[], 'chats'=>[], 'saves'=>[], 'shares'=>[]];
$sourcesData = $sources ?? $analytics['sources'] ?? ['labels'=>[], 'data'=>[]];
?>

<div class="row g-3">
    <!-- Biểu đồ đường: Lượt xem theo thời gian -->
    <div class="col-lg-8 col-12">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold text-secondary mb-3">
                    <i class="fa-solid fa-chart-line text-primary me-2"></i> Xu hướng lượt xem & Tương tác theo ngày
                </h6>
                <div style="position: relative; min-height: 320px;">
                    <canvas id="dailyAnalyticsChart" style="max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ tròn: Nguồn truy cập -->
    <div class="col-lg-4 col-12">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-3">
                <h6 class="fw-bold text-secondary mb-3">
                    <i class="fa-solid fa-chart-pie text-success me-2"></i> Nguồn gốc truy cập
                </h6>
                <div style="position: relative; min-height: 280px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="sourcesAnalyticsChart" style="max-height: 280px; max-width: 280px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ cột: So sánh tương tác tổng -->
    <div class="col-12 mt-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <h6 class="fw-bold text-secondary mb-3">
                    <i class="fa-solid fa-chart-bar text-info me-2"></i> So sánh khối lượng tương tác
                </h6>
                <div style="position: relative; min-height: 250px;">
                    <canvas id="interactionsComparisonChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Đảm bảo Chart.js đã được load -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const dailyRaw = <?= json_encode($dailyData) ?>;
    const sourcesRaw = <?= json_encode($sourcesData) ?>;

    // 1. Line Chart: Daily Analytics
    const ctxDaily = document.getElementById('dailyAnalyticsChart').getContext('2d');
    const gradientViews = ctxDaily.createLinearGradient(0, 0, 0, 300);
    gradientViews.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
    gradientViews.addColorStop(1, 'rgba(37, 99, 235, 0)');

    new Chart(ctxDaily, {
        type: 'line',
        data: {
            labels: dailyRaw.labels || [],
            datasets: [
                {
                    label: 'Lượt xem (View)',
                    data: dailyRaw.views || [],
                    borderColor: '#2563eb',
                    backgroundColor: gradientViews,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5
                },
                {
                    label: 'Cuộc gọi (Call)',
                    data: dailyRaw.calls || [],
                    borderColor: '#dc2626',
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.3,
                    borderWidth: 2
                },
                {
                    label: 'Tin nhắn (Chat)',
                    data: dailyRaw.chats || [],
                    borderColor: '#06b6d4',
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.3,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { weight: '600' } } },
                tooltip: { padding: 10 }
            },
            scales: {
                y: { grid: { borderDash: [5, 5] }, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Pie Chart: Sources
    const ctxSources = document.getElementById('sourcesAnalyticsChart').getContext('2d');
    new Chart(ctxSources, {
        type: 'doughnut',
        data: {
            labels: sourcesRaw.labels || [],
            datasets: [{
                data: sourcesRaw.data || [],
                backgroundColor: ['#4285F4', '#1877F2', '#0084FF', '#0088cc', '#10b981', '#9ca3af'],
                borderWidth: 2,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 11, weight: '600' } } }
            },
            cutout: '65%'
        }
    });

    // 3. Bar Chart: Interactions Comparison
    const ctxInteractions = document.getElementById('interactionsComparisonChart').getContext('2d');
    
    // Tính tổng tất cả loại hành động
    const sum = arr => (arr || []).reduce((a, b) => a + b, 0);
    const totalViews = sum(dailyRaw.views);
    const totalCalls = sum(dailyRaw.calls);
    const totalChats = sum(dailyRaw.chats);
    const totalSaves = sum(dailyRaw.saves);
    const totalShares = sum(dailyRaw.shares);

    new Chart(ctxInteractions, {
        type: 'bar',
        data: {
            labels: ['Lượt xem', 'Cuộc gọi', 'Chat', 'Lưu tin', 'Chia sẻ'],
            datasets: [{
                label: 'Số lượng tương tác',
                data: [totalViews, totalCalls, totalChats, totalSaves, totalShares],
                backgroundColor: [
                    'rgba(37, 99, 235, 0.85)',
                    'rgba(220, 38, 38, 0.85)',
                    'rgba(6, 182, 212, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(124, 58, 237, 0.85)'
                ],
                borderRadius: 6,
                maxBarThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { grid: { borderDash: [5, 5] }, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
