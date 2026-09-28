<?php
/**
 * Component bộ lọc Analytics.
 * Tích hợp lọc theo thời gian (Hôm nay, Hôm qua, 7 ngày, 30 ngày, 90 ngày, Năm nay, Tùy chọn) và loại tin.
 */
$period = $analytics['range']['period'] ?? '30';
$from = $analytics['range']['from'] ?? '';
$to = $analytics['range']['to'] ?? '';
// Định dạng từ ngày và đến ngày hiển thị trong ô input (chỉ lấy phần date Y-m-d)
$fromDateVal = substr($from, 0, 10);
$toDateVal = substr($to, 0, 10);
$postFilter = $analytics['post_filter'] ?? '';
?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form id="analyticsFilterForm" method="GET" class="row g-2 align-items-end">
            <!-- Khoảng thời gian -->
            <div class="col-lg-3 col-md-6 col-12">
                <label for="filterPeriod" class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-calendar-days text-primary me-1"></i> Khoảng thời gian
                </label>
                <select name="period" id="filterPeriod" class="form-select form-select-sm">
                    <?php
                    $periods = [
                        'today'     => 'Hôm nay',
                        'yesterday' => 'Hôm qua',
                        '7'         => '7 ngày qua',
                        '30'        => '30 ngày qua',
                        '90'        => '90 ngày qua',
                        'year'      => 'Năm nay',
                        'custom'    => 'Tùy chọn (Tự nhập)'
                    ];
                    foreach ($periods as $key => $lbl): ?>
                        <option value="<?= $key ?>" <?= $period === $key ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Từ ngày -->
            <div class="col-lg-2 col-md-3 col-6 date-range-input" style="<?= $period === 'custom' ? '' : 'opacity: 0.6;' ?>">
                <label for="filterFrom" class="form-label small fw-bold text-secondary mb-1">Từ ngày</label>
                <input type="date" name="from" id="filterFrom" value="<?= $fromDateVal ?>" 
                       class="form-control form-control-sm" <?= $period === 'custom' ? '' : 'readonly' ?>>
            </div>

            <!-- Đến ngày -->
            <div class="col-lg-2 col-md-3 col-6 date-range-input" style="<?= $period === 'custom' ? '' : 'opacity: 0.6;' ?>">
                <label for="filterTo" class="form-label small fw-bold text-secondary mb-1">Đến ngày</label>
                <input type="date" name="to" id="filterTo" value="<?= $toDateVal ?>" 
                       class="form-control form-control-sm" <?= $period === 'custom' ? '' : 'readonly' ?>>
            </div>

            <!-- Loại tin đăng -->
            <div class="col-lg-3 col-md-8 col-8">
                <label for="filterPostType" class="form-label small fw-bold text-secondary mb-1">
                    <i class="fa-solid fa-star text-warning me-1"></i> Loại tin đăng
                </label>
                <select name="post_filter" id="filterPostType" class="form-select form-select-sm">
                    <option value="" <?= $postFilter === '' ? 'selected' : '' ?>>Tất cả tin đăng</option>
                    <option value="vip" <?= $postFilter === 'vip' ? 'selected' : '' ?>>Tin VIP</option>
                    <option value="normal" <?= $postFilter === 'normal' ? 'selected' : '' ?>>Tin thường</option>
                    <option value="expired" <?= $postFilter === 'expired' ? 'selected' : '' ?>>VIP đã hết hạn</option>
                </select>
            </div>

            <!-- Nút lọc -->
            <div class="col-lg-2 col-md-4 col-4">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                    <i class="fa-solid fa-filter me-1"></i> Lọc kết quả
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const periodSelect = document.getElementById("filterPeriod");
    const fromInput = document.getElementById("filterFrom");
    const toInput = document.getElementById("filterTo");
    const dateRangeInputs = document.querySelectorAll(".date-range-input");

    periodSelect.addEventListener("change", function() {
        if (this.value === 'custom') {
            fromInput.removeAttribute("readonly");
            toInput.removeAttribute("readonly");
            dateRangeInputs.forEach(el => el.style.opacity = "1");
        } else {
            fromInput.setAttribute("readonly", true);
            toInput.setAttribute("readonly", true);
            dateRangeInputs.forEach(el => el.style.opacity = "0.6");
            
            // Tự động submit form khi chọn các thời gian định nghĩa sẵn
            document.getElementById("analyticsFilterForm").submit();
        }
    });
});
</script>
