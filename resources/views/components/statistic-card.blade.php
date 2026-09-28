<?php
/**
 * Component Statistic Card - Hiển thị các ô chỉ số đẹp mắt, cao cấp.
 * Các tham số truyền vào: $icon, $label, $value, $color, $extra (nếu có).
 */
?>
<div class="col-xl-3 col-md-6 col-12">
    <div class="card border-0 shadow-sm rounded-3 h-100 analytics-stat-card" style="border-left: 4px solid var(--bs-<?= $color ?? 'primary' ?>) !important;">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-uppercase small fw-bold text-muted d-block mb-1"><?= $label ?></span>
                    <h3 class="fw-bold mb-0 text-dark"><?= is_numeric($value) ? number_format($value) : $value ?></h3>
                    <?php if (!empty($extra)): ?>
                        <div class="small mt-1 text-muted"><?= $extra ?></div>
                    <?php endif; ?>
                </div>
                <div class="analytics-icon-box bg-<?= $color ?? 'primary' ?> bg-opacity-10 text-<?= $color ?? 'primary' ?> p-3 rounded-circle">
                    <i class="fa-solid <?= $icon ?? 'fa-chart-simple' ?> fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.analytics-icon-box {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.analytics-stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.analytics-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
}
</style>
