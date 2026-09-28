@include('admin.layouts.header')

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Cài Đặt Báo Giá & Khuyến Mãi</h1>
</div>

<form action="<?= URL_ROOT ?>/admin/bang-gia/save" method="POST">
    <?= Csrf::field() ?>
    <div class="row g-4">
        <!-- Báo giá tin VIP -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h5 class="mb-0 text-primary"><i class="fa-solid fa-crown me-2"></i> Giá Tin VIP (VNĐ/Ngày)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">VIP 5</label>
                        <input type="number" name="price_vip5" class="form-control" value="<?= htmlspecialchars($data['settings']['price_vip5'] ?? '5000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">VIP 4</label>
                        <input type="number" name="price_vip4" class="form-control" value="<?= htmlspecialchars($data['settings']['price_vip4'] ?? '10000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">VIP 3</label>
                        <input type="number" name="price_vip3" class="form-control" value="<?= htmlspecialchars($data['settings']['price_vip3'] ?? '20000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">VIP 2</label>
                        <input type="number" name="price_vip2" class="form-control" value="<?= htmlspecialchars($data['settings']['price_vip2'] ?? '30000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">VIP 1</label>
                        <input type="number" name="price_vip1" class="form-control" value="<?= htmlspecialchars($data['settings']['price_vip1'] ?? '50000') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Các gói UP tin -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h5 class="mb-0 text-primary"><i class="fa-solid fa-arrow-up me-2"></i> Các Gói UP Tin (VNĐ)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">60 lượt (30 ngày)</label>
                        <input type="number" name="price_up60" class="form-control" value="<?= htmlspecialchars($data['settings']['price_up60'] ?? '30000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">150 lượt (30 ngày)</label>
                        <input type="number" name="price_up150" class="form-control" value="<?= htmlspecialchars($data['settings']['price_up150'] ?? '50000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">500 lượt (30 ngày)</label>
                        <input type="number" name="price_up500" class="form-control" value="<?= htmlspecialchars($data['settings']['price_up500'] ?? '100000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">750 lượt (30 ngày)</label>
                        <input type="number" name="price_up750" class="form-control" value="<?= htmlspecialchars($data['settings']['price_up750'] ?? '150000') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">1500 lượt (30 ngày)</label>
                        <input type="number" name="price_up1500" class="form-control" value="<?= htmlspecialchars($data['settings']['price_up1500'] ?? '200000') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Khuyến mãi nạp tiền -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h5 class="mb-0 text-primary"><i class="fa-solid fa-percent me-2"></i> Khuyến Mãi Nạp Tiền (%)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">50.000đ - 100.000đ</label>
                        <input type="number" name="bonus_tier1" class="form-control" value="<?= htmlspecialchars($data['settings']['bonus_tier1'] ?? '20') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">100.000đ - 200.000đ</label>
                        <input type="number" name="bonus_tier2" class="form-control" value="<?= htmlspecialchars($data['settings']['bonus_tier2'] ?? '50') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">200.000đ - 500.000đ</label>
                        <input type="number" name="bonus_tier3" class="form-control" value="<?= htmlspecialchars($data['settings']['bonus_tier3'] ?? '100') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">500.000đ - 1.000.000đ</label>
                        <input type="number" name="bonus_tier4" class="form-control" value="<?= htmlspecialchars($data['settings']['bonus_tier4'] ?? '150') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">1.000.000đ - 3.000.000đ</label>
                        <input type="number" name="bonus_tier5" class="form-control" value="<?= htmlspecialchars($data['settings']['bonus_tier5'] ?? '200') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">> 3.000.000đ</label>
                        <input type="number" name="bonus_tier6" class="form-control" value="<?= htmlspecialchars($data['settings']['bonus_tier6'] ?? '250') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="text-end mt-4 mb-5">
        <button type="submit" class="btn btn-success btn-lg px-4"><i class="fa-solid fa-floppy-disk me-2"></i> Lưu Bảng Giá</button>
    </div>
</form>

@include('admin.layouts.footer')
