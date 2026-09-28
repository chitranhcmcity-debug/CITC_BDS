@include('admin.layouts.header')

<div class="content-wrapper p-3 bg-light">
    <!-- Nút quay lại và Tiêu đề -->
    <section class="content-header mb-4 text-start">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 mb-2">
                    <a href="<?= URL_ROOT ?>/admin/nguoi-dung" class="text-decoration-none text-secondary small fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
                    </a>
                </div>
                <div class="col-sm-6">
                    <h1 class="h3 fw-bold text-dark mb-1">Chi tiết tài khoản #<?= $user->id ?></h1>
                    <span class="badge bg-light text-secondary border">Mã ID: <?= $user->id ?></span>
                    <?php
                    $isLocked = !empty($user->locked_until) && strtotime($user->locked_until) > time();
                    if ($isLocked) {
                        echo '<span class="badge bg-danger ms-1"><i class="fa-solid fa-lock me-1"></i> Đã bị khóa đến ' . date('d/m/Y H:i', strtotime($user->locked_until)) . '</span>';
                    } elseif ($user->trang_thai === 'hoat_dong') {
                        echo '<span class="badge bg-success ms-1">Đang hoạt động</span>';
                    } else {
                        echo '<span class="badge bg-secondary ms-1">Ngừng hoạt động</span>';
                    }
                    ?>
                </div>
                <div class="col-sm-6 text-end">
                    <div class="d-flex gap-1.5 justify-content-end align-items-center">
                        <!-- Nút Cộng/Trừ tiền -->
                        <button class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addMoneyModal">
                            <i class="fa-solid fa-plus me-1"></i> Nạp tiền
                        </button>
                        <button class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#subMoneyModal">
                            <i class="fa-solid fa-minus me-1"></i> Trừ tiền
                        </button>

                        <!-- Khóa / Mở khóa -->
                        <?php if ($isLocked): ?>
                            <form action="<?= URL_ROOT ?>/admin/nguoi-dung/unlock/<?= $user->id ?>" method="POST" class="d-inline">
                                <?= Csrf::field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-success fw-bold">
                                    <i class="fa-solid fa-lock-open me-1"></i> Mở khóa tài khoản
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#lockModal">
                                <i class="fa-solid fa-lock me-1"></i> Khóa tài khoản
                            </button>
                        <?php endif; ?>

                        <!-- Đặt lại mật khẩu -->
                        <button class="btn btn-sm btn-outline-dark fw-bold" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                            <i class="fa-solid fa-key me-1"></i> Đổi mật khẩu
                        </button>

                        <!-- Gửi thông báo -->
                        <button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#sendNotifModal">
                            <i class="fa-solid fa-bell me-1"></i> Gửi thông báo
                        </button>

                        <!-- Sửa hồ sơ -->
                        <a href="<?= URL_ROOT ?>/admin/nguoi-dung/update/<?= $user->id ?>" class="btn btn-sm btn-primary fw-bold">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Sửa hồ sơ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nội dung chính -->
    <section class="content text-start">
        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Cột trái: Thông tin hồ sơ chi tiết -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-body text-center pt-4">
                        <?php if (!empty($user->anh_dai_dien)): ?>
                            <img src="<?= URL_ROOT ?>/public/uploads/avatars/<?= $user->anh_dai_dien ?>" class="rounded-circle border mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3 shadow-sm" style="width: 100px; height: 100px; font-size: 2.2rem;">
                                <?= mb_strtoupper(mb_substr($user->ten, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user->ten) ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($user->role_name ?: 'Thành viên') ?></p>
                        
                        <div class="border-top pt-3 text-start small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Số dư ví hiện tại:</span>
                                <strong class="text-danger fs-6"><?= number_format($user->so_du) ?> đ</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tổng nạp tiền ví:</span>
                                <strong class="text-success"><?= number_format($user->total_deposit) ?> đ</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Số tin đăng (tổng):</span>
                                <strong class="text-dark"><?= (int)$user->post_count ?> tin</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Số tin VIP hoạt động:</span>
                                <strong class="text-warning"><?= (int)$user->vip_post_count ?> tin</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Ngày tham gia:</span>
                                <span class="text-secondary"><?= date('d/m/Y H:i', strtotime($user->ngay_tao)) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom pt-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-address-card text-secondary me-2"></i>Thông tin liên hệ</h6>
                    </div>
                    <div class="card-body small" style="font-size: 0.85rem;">
                        <ul class="list-group list-group-flush mb-0 text-start">
                            <li class="list-group-item d-flex justify-content-between py-2.5 ps-0 pe-0">
                                <span class="text-muted">Email:</span>
                                <span class="text-secondary fw-bold text-truncate" style="max-width: 200px;"><?= htmlspecialchars($user->email) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2.5 ps-0 pe-0">
                                <span class="text-muted">Số điện thoại:</span>
                                <span class="text-secondary fw-bold"><?= htmlspecialchars($user->dien_thoai ?: 'Chưa xác thực') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2.5 ps-0 pe-0">
                                <span class="text-muted">Giới tính:</span>
                                <span class="text-secondary"><?= $user->gioi_tinh === 'nu' ? 'Nữ' : ($user->gioi_tinh === 'khac' ? 'Khác' : 'Nam') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2.5 ps-0 pe-0">
                                <span class="text-muted">Ngày sinh:</span>
                                <span class="text-secondary"><?= $user->ngay_sinh ? date('d/m/Y', strtotime($user->ngay_sinh)) : 'N/A' ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2.5 ps-0 pe-0">
                                <span class="text-muted">Nghề nghiệp:</span>
                                <span class="text-secondary"><?= htmlspecialchars($user->nghe_nghiep ?: 'N/A') ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2.5 ps-0 pe-0">
                                <span class="text-muted">Địa chỉ:</span>
                                <span class="text-secondary text-truncate" style="max-width: 200px;"><?= htmlspecialchars($user->dia_chi ?: 'N/A') ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Tabs lịch sử đăng nhập & lịch sử giao dịch ví -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 bg-white mb-4">
                    <div class="card-header bg-white border-bottom p-0">
                        <ul class="nav nav-tabs border-bottom-0" id="userTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active fw-bold text-secondary py-3 px-4 border-0" id="loginTab" data-bs-toggle="tab" data-bs-target="#loginContent" type="button">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Lịch sử đăng nhập
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link fw-bold text-secondary py-3 px-4 border-0" id="transTab" data-bs-toggle="tab" data-bs-target="#transContent" type="button">
                                    <i class="fa-solid fa-wallet me-1"></i> Lịch sử giao dịch ví
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="userTabsContent">
                            <!-- Tab: Lịch sử đăng nhập -->
                            <div class="tab-pane fade show active" id="loginContent" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light text-secondary small fw-bold">
                                            <tr>
                                                <th class="ps-3">Địa chỉ IP</th>
                                                <th>Hệ điều hành (OS)</th>
                                                <th>Trình duyệt</th>
                                                <th>Thiết bị</th>
                                                <th>Thời gian</th>
                                                <th class="pe-3 text-end">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($loginHistory)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted small">Không tìm thấy lịch sử đăng nhập nào.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($loginHistory as $lh): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-bold text-secondary"><?= htmlspecialchars($lh->ip_address) ?></td>
                                                        <td><?= htmlspecialchars($lh->os ?: 'Unknown') ?></td>
                                                        <td><?= htmlspecialchars($lh->browser ?: 'Unknown') ?></td>
                                                        <td><?= htmlspecialchars($lh->device ?: 'Desktop') ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($lh->created_at)) ?></td>
                                                        <td class="pe-3 text-end">
                                                            <span class="badge bg-<?= $lh->status === 'success' ? 'success' : 'danger' ?>">
                                                                <?= $lh->status === 'success' ? 'Thành công' : 'Thất bại' ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab: Lịch sử giao dịch ví -->
                            <div class="tab-pane fade" id="transContent" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light text-secondary small fw-bold">
                                            <tr>
                                                <th class="ps-3">Số tiền</th>
                                                <th>Phân loại</th>
                                                <th>Phương thức / Ghi chú lý do</th>
                                                <th>Ngày tạo</th>
                                                <th class="pe-3 text-end">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($transactions)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4 text-muted small">Chưa có giao dịch phát sinh.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($transactions as $t): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-bold text-danger">
                                                            <?= ($t->type === 'nap_tien' || $t->type === 'Admin cộng tiền') ? '+' : '-' ?>
                                                            <?= number_format($t->amount) ?> đ
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $typeLabel = match ($t->type) {
                                                                'nap_tien' => 'Nạp tiền',
                                                                'mua_vip' => 'Mua gói VIP',
                                                                'gia_han_vip' => 'Gia hạn VIP',
                                                                'up_tin' => 'UP tin đăng',
                                                                'thuong_referral' => 'Thưởng hoa hồng',
                                                                'rut_tien' => 'Rút tiền thưởng',
                                                                'Admin cộng tiền' => 'Nạp thủ công',
                                                                'Admin trừ tiền' => 'Trừ thủ công',
                                                                default => $t->type
                                                            };
                                                            ?>
                                                            <span class="badge bg-light text-secondary border"><?= $typeLabel ?></span>
                                                        </td>
                                                        <td><?= htmlspecialchars($t->method_or_desc) ?></td>
                                                        <td><?= date('d/m/Y H:i', strtotime($t->ngay_tao)) ?></td>
                                                        <td class="pe-3 text-end">
                                                            <span class="badge bg-<?= $t->status === 'da_duyet' ? 'success' : 'warning text-dark' ?>">
                                                                <?= $t->status === 'da_duyet' ? 'Đã duyệt' : 'Chờ duyệt' ?>
                                                            </span>
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
            </div>
        </div>
    </section>
</div>

<!-- Modal Cộng Tiền -->
<div class="modal fade" id="addMoneyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/nguoi-dung/addMoney/<?= $user->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Nạp tiền ví thành viên</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Số tiền nạp (đ)</label>
                        <input type="number" name="amount" class="form-control form-control-sm text-success fw-bold" value="50000" min="1000" step="1000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Lý do nạp tiền</label>
                        <input type="text" name="reason" class="form-control form-control-sm" value="Admin cộng tiền khuyến mãi." required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-success w-100 fw-bold">Xác nhận nạp tiền</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Trừ Tiền -->
<div class="modal fade" id="subMoneyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/nguoi-dung/subMoney/<?= $user->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Trừ tiền ví thành viên</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Số tiền trừ (đ)</label>
                        <input type="number" name="amount" class="form-control form-control-sm text-danger fw-bold" value="10000" min="1000" step="1000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Lý do trừ tiền</label>
                        <input type="text" name="reason" class="form-control form-control-sm" value="Trừ tiền ví sai lệch giao dịch." required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">Xác nhận trừ tiền</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Khóa tài khoản -->
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/nguoi-dung/lock/<?= $user->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Khóa tài khoản thành viên</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Thời hạn khóa</label>
                        <select name="duration" class="form-select form-select-sm" required>
                            <option value="3">Khóa tạm thời 3 ngày</option>
                            <option value="7">Khóa tạm thời 7 ngày</option>
                            <option value="30">Khóa tạm thời 30 ngày</option>
                            <option value="0">Khóa vĩnh viễn tài khoản</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Lý do khóa</label>
                        <textarea name="reason" class="form-control form-control-sm" rows="3" required placeholder="Nhập lý do khóa vi phạm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-danger w-100 fw-bold">Xác nhận khóa tài khoản</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Đổi mật khẩu -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <form action="<?= URL_ROOT ?>/admin/nguoi-dung/resetPassword/<?= $user->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Đặt lại mật khẩu mới</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Nhập mật khẩu từ 6 ký tự..." required minlength="6">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-dark w-100 fw-bold">Đặt lại mật khẩu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Gửi Thông Báo -->
<div class="modal fade" id="sendNotifModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= URL_ROOT ?>/admin/nguoi-dung/sendNotification/<?= $user->id ?>" method="POST">
            <?= Csrf::field() ?>
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-start">Gửi thông báo cá nhân</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Tiêu đề thông báo</label>
                        <input type="text" name="title" class="form-control form-control-sm" value="Thông báo từ Ban quản trị" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary mb-1">Nội dung thông báo</label>
                        <textarea name="content" class="form-control form-control-sm text-secondary" rows="4" placeholder="Nhập nội dung thông báo..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">Gửi thông báo</button>
                </div>
            </div>
        </form>
    </div>
</div>

@include('admin.layouts.footer')
