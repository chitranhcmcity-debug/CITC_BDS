<?php require_once '../app/views/admin/layouts/header.php'; ?>

<!-- Custom Styles for Premium Look -->
<style>
    /* Stats Cards */
    .stat-card {
        border-radius: 16px;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        top: -30px;
        right: -30px;
        pointer-events: none;
    }
    .stat-icon {
        font-size: 2.2rem;
        opacity: 0.85;
    }

    /* Gradients */
    .bg-gradient-total { background: linear-gradient(135deg, #6366f1, #3b82f6); }
    .bg-gradient-new { background: linear-gradient(135deg, #06b6d4, #0ea5e9); }
    .bg-gradient-processing { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bg-gradient-success { background: linear-gradient(135deg, #10b981, #059669); }

    /* Custom Filter Bar */
    .filter-bar {
        background-color: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    /* Sub Stats Cards */
    .sub-stat-card {
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .sub-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    /* Table Hover Details */
    .lead-table tbody tr {
        transition: background-color 0.2s ease;
    }
    .lead-table tbody tr:hover {
        background-color: #f1f5f9 !important;
    }

    /* Badge Customizations */
    .status-badge {
        font-size: 0.78rem;
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 600;
        display: inline-block;
        text-align: center;
    }
    .badge-status-moi { background-color: #e0f2fe; color: #0369a1; }
    .badge-status-da_lien_he { background-color: #fef3c7; color: #b45309; }
    .badge-status-tiem_nang { background-color: #faf5ff; color: #6b21a8; }
    .badge-status-that_bai { background-color: #f1f5f9; color: #475569; }
    .badge-status-thanh_cong { background-color: #d1fae5; color: #065f46; }

    /* Float Toast Alert */
    #toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
    }
    .toast-card {
        background: white;
        color: #1e293b;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        transform: translateX(150%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border-left: 5px solid #10b981;
    }
    .toast-card.show {
        transform: translateX(0);
    }
</style>

<div id="toast-container">
    <div id="success-toast" class="toast-card">
        <i class="fa-solid fa-circle-check text-success fs-4"></i>
        <div>
            <h6 class="mb-0 fw-bold">Thành công!</h6>
            <p class="mb-0 small text-muted" id="toast-msg">Đã cập nhật dữ liệu khách hàng.</p>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 fw-bold text-slate-800">Quản Lý Khách Hàng & Yêu Cầu</h1>
        <p class="text-muted mb-0">Quản lý cơ hội, theo dõi tư vấn và phân bổ người phụ trách (CRM).</p>
    </div>
</div>

<?php Session::flash('khach_hang_msg'); ?>

<!-- KPI Metric Cards Block -->
<div class="row g-3 mb-4">
    <!-- Total Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-total shadow-sm h-100" id="kpi-card-total" title="Click để xem tất cả yêu cầu">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-white-50 fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Tổng Yêu Cầu</h6>
                    <h2 class="fw-bold mb-0 text-white"><?= $data['stats']['tong'] ?></h2>
                </div>
                <div class="stat-icon text-white-50">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- New Leads -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-new shadow-sm h-100" id="kpi-card-new" title="Click để lọc trạng thái Mới">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-white-50 fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Yêu Cầu Mới</h6>
                    <h2 class="fw-bold mb-0 text-white"><?= $data['stats']['moi'] ?></h2>
                </div>
                <div class="stat-icon text-white-50">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- In Progress -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-processing shadow-sm h-100" id="kpi-card-processing" title="Click để lọc trạng thái Đang chăm sóc">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-white-50 fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Đang Chăm Sóc</h6>
                    <h2 class="fw-bold mb-0 text-white"><?= $data['stats']['dang_cham_soc'] ?></h2>
                </div>
                <div class="stat-icon text-white-50">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Closed Won -->
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card bg-gradient-success shadow-sm h-100" id="kpi-card-success" title="Click để lọc Giao dịch thành công">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-white-50 fw-semibold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Chốt Thành Công</h6>
                    <h2 class="fw-bold mb-0 text-white"><?= $data['stats']['thanh_cong'] ?></h2>
                </div>
                <div class="stat-icon text-white-50">
                    <i class="fa-solid fa-handshake"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sub-Statistics Row for Booking vs Interaction -->
<div class="row g-3 mb-4">
    <!-- Consultation Bookings -->
    <div class="col-md-6">
        <div class="card sub-stat-card shadow-sm border-0" id="kpi-card-booking" title="Click để lọc riêng danh sách Đặt lịch tư vấn" style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border-left: 5px solid #d97706 !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Lịch Đặt Tư Vấn (Đặt Lịch Hẹn)</h6>
                    <h3 class="fw-black mb-0" style="color: #78350f; font-weight: 800;"><?= $data['stats']['dat_lich'] ?> <span class="fs-6 fw-normal text-muted">đăng ký</span></h3>
                </div>
                <div class="fs-2 text-warning opacity-75">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- General Interactions -->
    <div class="col-md-6">
        <div class="card sub-stat-card shadow-sm border-0" id="kpi-card-interaction" title="Click để lọc riêng danh sách Khách tương tác" style="background: linear-gradient(135deg, #ecfeff, #cffafe); border-left: 5px solid #0891b2 !important;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Khách Tương Tác (Tư Vấn Nhanh / Liên Hệ)</h6>
                    <h3 class="fw-black mb-0" style="color: #164e63; font-weight: 800;"><?= $data['stats']['tuong_tac'] ?> <span class="fs-6 fw-normal text-muted">lượt</span></h3>
                </div>
                <div class="fs-2 text-info opacity-75">
                    <i class="fa-solid fa-comments"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3 filter-bar">
        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="leadSearchInput" class="form-control border-start-0 ps-0" placeholder="Tìm tên, SĐT, email...">
                </div>
            </div>
            <div class="col-lg-3 col-md-3">
                <select id="leadStatusFilter" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="moi">Mới</option>
                    <option value="da_lien_he">Đã liên hệ</option>
                    <option value="tiem_nang">Tiềm năng</option>
                    <option value="thanh_cong">Thành công</option>
                    <option value="that_bai">Thất bại</option>
                    <option value="dang_cham_soc" class="d-none">Đang chăm sóc</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-3">
                <select id="leadTypeFilter" class="form-select">
                    <option value="">-- Tất cả loại yêu cầu --</option>
                    <option value="dat_lich">Lịch đặt tư vấn</option>
                    <option value="tuong_tac">Khách tương tác</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-12 d-flex align-items-center justify-content-end text-muted small">
                <i class="fa-solid fa-circle-info me-1"></i> Lọc thời gian thực.
            </div>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table lead-table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4" style="width: 70px;">Mã</th>
                        <th>Thông Tin Khách Hàng</th>
                        <th>Nội dung / BĐS Quan Tâm</th>
                        <th>Người Phụ Trách</th>
                        <th style="width: 140px;">Trạng Thái</th>
                        <th style="width: 130px;" class="text-center">Giải Quyết</th>
                        <th class="pe-4 text-end" style="width: 165px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="leadTableBody">
                    <?php if (empty($data['leads'])): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa-regular fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                Chưa có dữ liệu khách hàng liên hệ nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data['leads'] as $lead): ?>
                            <tr id="lead-row-<?= $lead->id ?>" 
                                data-name="<?= htmlspecialchars(mb_strtolower($lead->ten)) ?>"
                                data-phone="<?= htmlspecialchars($lead->dien_thoai) ?>"
                                data-email="<?= htmlspecialchars(mb_strtolower($lead->email ?? '')) ?>"
                                data-status="<?= $lead->trang_thai ?>"
                                data-type="<?= (strpos($lead->tin_nhan, '[ĐẶT LỊCH HẸN TƯ VẤN]') !== false) ? 'dat_lich' : 'tuong_tac' ?>">
                                <td class="ps-4 fw-semibold text-secondary">#<?= $lead->id ?></td>
                                <td>
                                    <div class="fw-bold text-slate-800 fs-6"><?= htmlspecialchars($lead->ten) ?></div>
                                    <div class="small text-muted mb-1"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($lead->dien_thoai) ?></div>
                                    <?php if ($lead->email): ?>
                                        <div class="small text-muted" style="font-size: 0.8rem;"><i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($lead->email) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Loại yêu cầu -->
                                    <div class="mb-2">
                                        <?php if (strpos($lead->tin_nhan, '[ĐẶT LỊCH HẸN TƯ VẤN]') !== false): ?>
                                            <span class="badge bg-warning text-dark" style="font-size: 0.75rem; font-weight: 700; padding: 5px 10px; border-radius: 6px;"><i class="fa-solid fa-calendar-days me-1"></i>Đặt lịch tư vấn</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark" style="font-size: 0.75rem; font-weight: 700; padding: 5px 10px; border-radius: 6px;"><i class="fa-regular fa-comments me-1"></i>Khách tương tác</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Dự án liên kết -->
                                    <div class="mb-1">
                                        <?php if ($lead->ma_du_an): ?>
                                            <a href="<?= URL_ROOT ?>/du-an/detail/<?= urlencode($lead->duong_dan_du_an) ?>" target="_blank" class="text-decoration-none fw-semibold" style="font-size: 0.88rem;">
                                                <i class="fa-solid fa-house-chimney text-primary me-1"></i> <?= htmlspecialchars($lead->tieu_de_du_an) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary opacity-75"><i class="fa-solid fa-circle-question me-1"></i> Liên hệ chung</span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Cắt ngắn nội dung tin nhắn -->
                                    <div class="text-muted small text-truncate" style="max-width: 320px;">
                                        <?= htmlspecialchars($lead->tin_nhan) ?>
                                    </div>
                                    <?php if (!empty($lead->bao_cao_giai_quyet)): ?>
                                        <div class="text-success small mt-1 text-truncate" style="max-width: 320px; font-size: 0.78rem;" id="lead-report-text-<?= $lead->id ?>" title="<?= htmlspecialchars($lead->bao_cao_giai_quyet) ?>">
                                            <i class="fa-solid fa-file-invoice text-success me-1"></i><strong>Báo cáo:</strong> <span class="report-content"><?= htmlspecialchars($lead->bao_cao_giai_quyet) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted small mt-1 text-truncate d-none" style="max-width: 320px; font-size: 0.78rem;" id="lead-report-text-<?= $lead->id ?>">
                                            <i class="fa-solid fa-file-invoice me-1"></i><strong>Báo cáo:</strong> <span class="report-content"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td id="cell-staff-<?= $lead->id ?>">
                                    <?php if ($lead->ten_nguoi_phu_trach): ?>
                                        <span class="fw-medium text-dark"><i class="fa-solid fa-user-tie text-muted me-1"></i><?= htmlspecialchars($lead->ten_nguoi_phu_trach) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Chưa phân công</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span id="badge-status-<?= $lead->id ?>" class="status-badge badge-status-<?= $lead->trang_thai ?>">
                                        <?php 
                                            switch($lead->trang_thai) {
                                                case 'moi': echo 'Mới'; break;
                                                case 'da_lien_he': echo 'Đã liên hệ'; break;
                                                case 'tiem_nang': echo 'Tiềm năng'; break;
                                                case 'that_bai': echo 'Thất bại'; break;
                                                case 'thanh_cong': echo 'Thành công'; break;
                                            }
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input resolve-toggle-switch" type="checkbox" role="switch" data-id="<?= $lead->id ?>" <?= $lead->da_giai_quyet ? 'checked' : '' ?> style="cursor: pointer; width: 2em; height: 1em;">
                                    </div>
                                    <div class="small" id="resolve-text-<?= $lead->id ?>" style="font-size: 0.75rem;">
                                        <?= $lead->da_giai_quyet ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Đã xong</span>' : '<span class="text-secondary"><i class="fa-regular fa-circle-xmark"></i> Chưa</span>' ?>
                                    </div>
                                </td>
                                <td class="pe-4">
                                     <div class="d-flex align-items-center justify-content-end gap-2">
                                         <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#editLeadModal"
                                                 data-id="<?= $lead->id ?>"
                                                 data-name="<?= htmlspecialchars($lead->ten ?? '') ?>"
                                                 data-phone="<?= htmlspecialchars($lead->dien_thoai ?? '') ?>"
                                                 data-email="<?= htmlspecialchars($lead->email ?? '') ?>"
                                                 data-project="<?= htmlspecialchars($lead->tieu_de_du_an ?? 'Liên hệ chung') ?>"
                                                 data-msg="<?= htmlspecialchars($lead->tin_nhan ?? '') ?>"
                                                 data-status="<?= $lead->trang_thai ?>"
                                                 data-staff="<?= $lead->nguoi_phu_trach ?: '' ?>"
                                                 data-note="<?= htmlspecialchars($lead->ghi_chu ?? '') ?>"
                                                 data-resolved="<?= $lead->da_giai_quyet ?>"
                                                 data-report="<?= htmlspecialchars($lead->bao_cao_giai_quyet ?? '') ?>"
                                                 data-date="<?= date('d/m/Y H:i', strtotime($lead->ngay_tao)) ?>">
                                             <i class="fa-solid fa-circle-info"></i> Chi tiết
                                         </button>
                                         <button type="button" class="btn btn-sm btn-light text-danger rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 32px; height: 32px; transition: all 0.2s;"
                                                 data-bs-toggle="modal"
                                                 data-bs-target="#deleteLeadModal"
                                                 data-id="<?= $lead->id ?>"
                                                 data-name="<?= htmlspecialchars($lead->ten ?? '') ?>"
                                                 title="Xóa yêu cầu">
                                             <i class="fa-solid fa-trash-can"></i>
                                         </button>
                                     </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Chi Tiết & Xử Lý Khách Hàng (AJAX Form) -->
<div class="modal fade" id="editLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-3">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-clipboard-user me-2"></i>Chi Tiết & Xử Lý Khách Hàng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLeadForm" action="" method="POST">
                <?= Csrf::field() ?>
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <!-- Thông tin cơ bản khách -->
                        <div class="col-md-6 border-end">
                            <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-user-tag me-1"></i>Thông tin Khách hàng</h6>
                            <table class="table table-borderless table-sm small align-middle">
                                <tr>
                                    <td class="text-muted fw-semibold" style="width: 110px;">Họ tên:</td>
                                    <td id="modal-lead-name" class="fw-bold text-dark">--</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Điện thoại:</td>
                                    <td><a href="" id="modal-lead-phone-link" class="fw-bold text-decoration-none"><i class="fa-solid fa-phone me-1"></i><span id="modal-lead-phone">--</span></a></td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Email:</td>
                                    <td id="modal-lead-email">--</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Ngày gửi:</td>
                                    <td id="modal-lead-date">--</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">BĐS quan tâm:</td>
                                    <td id="modal-lead-project" class="text-primary fw-semibold">--</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Tin nhắn của khách -->
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-message me-1"></i>Nội dung lời nhắn</h6>
                            <div class="bg-light p-3 rounded-3 text-dark small border overflow-auto" style="max-height: 140px; min-height: 100px; line-height: 1.5;" id="modal-lead-msg">
                                --
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted">

                    <!-- Xử lý nghiệp vụ chăm sóc khách -->
                    <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-user-gear me-1"></i>Tiến Trình Chăm Sóc & Tư Vấn</h6>
                    <div class="row g-3">
                        <!-- Trạng thái -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Trạng thái chăm sóc</label>
                            <select name="trang_thai" id="modal-lead-status" class="form-select" required>
                                <option value="moi">Mới nhận (Chưa liên hệ)</option>
                                <option value="da_lien_he">Đã liên hệ điện thoại</option>
                                <option value="tiem_nang">Khách hàng Tiềm năng</option>
                                <option value="thanh_cong">Giao dịch Thành công (Đã chốt)</option>
                                <option value="that_bai">Thất bại / Hủy bỏ</option>
                            </select>
                        </div>

                        <!-- Người phụ trách -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Người phụ trách (Sale/Staff)</label>
                            <select name="nguoi_phu_trach" id="modal-lead-staff" class="form-select">
                                <option value="">-- Chưa phân công --</option>
                                <?php foreach($data['staff'] as $member): ?>
                                    <option value="<?= $member->id ?>"><?= htmlspecialchars($member->ten) ?> (<?= $member->ma_vai_tro == 1 ? 'Admin' : 'Client' ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Kết Quả Giải Quyết Yêu Cầu -->
                        <div class="col-md-12 mt-2">
                            <div class="bg-light p-3 rounded-3 border">
                                <h6 class="fw-bold mb-3 text-slate-800 d-flex align-items-center">
                                    <i class="fa-solid fa-circle-check text-success me-2"></i> Kết Quả Giải Quyết Yêu Cầu
                                </h6>
                                <div class="row g-2">
                                    <div class="col-12 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" name="da_giai_quyet" id="modal-lead-resolved" value="1" style="cursor: pointer;">
                                            <label class="form-check-label fw-bold small" for="modal-lead-resolved">Đã giải quyết xong yêu cầu của khách hàng</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Báo cáo giải quyết cụ thể</label>
                                        <textarea name="bao_cao_giai_quyet" id="modal-lead-report" rows="3" class="form-control" placeholder="Mô tả cụ thể phương hướng giải quyết..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ghi chú nội bộ -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Ghi chú tư vấn (Nội bộ)</label>
                            <textarea name="ghi_chu" id="modal-lead-note" rows="3" class="form-control" placeholder="Ví dụ: Đã gọi điện, khách đang cân nhắc tài chính. Hẹn gọi lại vào ngày mai..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-center py-3">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Cập Nhật Tiến Độ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xóa Khách Hàng -->
<div class="modal fade" id="deleteLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white p-3">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xác nhận Xóa Yêu Cầu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteLeadForm" action="" method="POST">
                <?= Csrf::field() ?>
                <div class="modal-body text-center py-4">
                    <i class="fa-regular fa-trash-can text-danger mb-3" style="font-size: 3.5rem; opacity: 0.8;"></i>
                    <h5 class="fw-bold mb-3 text-slate-800">Bạn có muốn xóa yêu cầu của khách hàng?</h5>
                    <p class="mb-0 fs-5 text-dark fw-semibold" id="deleteLeadName"></p>
                    <p class="small text-muted mt-2">Dữ liệu liên hệ sẽ bị xóa vĩnh viễn khỏi CSDL.</p>
                </div>
                <div class="modal-footer bg-light justify-content-center py-3">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold"><i class="fa-solid fa-trash-can me-1"></i> Đồng ý xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interactivity Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ----------------------------------------------
    // SEARCH & FILTER FUNCTIONALITY (Client Side)
    // ----------------------------------------------
    const searchInput = document.getElementById('leadSearchInput');
    const statusFilter = document.getElementById('leadStatusFilter');
    const typeFilter = document.getElementById('leadTypeFilter');
    const tableRows = document.querySelectorAll('#leadTableBody tr[id^="lead-row-"]');

    function filterLeads() {
        const query = searchInput.value.toLowerCase().trim();
        const selectedStatus = statusFilter.value;
        const selectedType = typeFilter ? typeFilter.value : '';

        tableRows.forEach(row => {
            const name = row.getAttribute('data-name');
            const phone = row.getAttribute('data-phone');
            const email = row.getAttribute('data-email');
            const status = row.getAttribute('data-status');
            const type = row.getAttribute('data-type');

            const matchesSearch = !query || name.includes(query) || phone.includes(query) || email.includes(query);
            
            let matchesStatus = false;
            if (selectedStatus === 'dang_cham_soc') {
                matchesStatus = (status === 'da_lien_he' || status === 'tiem_nang');
            } else {
                matchesStatus = !selectedStatus || status === selectedStatus;
            }
            
            const matchesType = !selectedType || type === selectedType;

            if (matchesSearch && matchesStatus && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterLeads);
    if (statusFilter) statusFilter.addEventListener('change', filterLeads);
    if (typeFilter) typeFilter.addEventListener('change', filterLeads);

    // ----------------------------------------------
    // INTERACTIVE KPI & SUB-STAT CARD FILTERING
    // ----------------------------------------------
    const kpiCards = {
        'kpi-card-total': { status: '', type: null },
        'kpi-card-new': { status: 'moi', type: null },
        'kpi-card-processing': { status: 'dang_cham_soc', type: null },
        'kpi-card-success': { status: 'thanh_cong', type: null },
        'kpi-card-booking': { status: null, type: 'dat_lich' },
        'kpi-card-interaction': { status: null, type: 'tuong_tac' }
    };

    Object.keys(kpiCards).forEach(cardId => {
        const cardEl = document.getElementById(cardId);
        if (cardEl) {
            cardEl.addEventListener('click', function() {
                const config = kpiCards[cardId];
                
                // Set status filter if configured
                if (config.status !== null) {
                    statusFilter.value = config.status;
                }
                
                // Set type filter if configured
                if (config.type !== null) {
                    typeFilter.value = config.type;
                }
                
                // Run filter instantly
                filterLeads();

                // Highlight animation feedback
                cardEl.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    cardEl.style.transform = '';
                }, 150);

                // Smooth scroll to table headers to see filtered results
                const tableEl = document.querySelector('.lead-table');
                if (tableEl) {
                    tableEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        }
    });

    // ----------------------------------------------
    // EDIT MODAL AUTO-FILL
    // ----------------------------------------------
    const editLeadModal = document.getElementById('editLeadModal');
    let currentLeadId = null;

    if (editLeadModal) {
        editLeadModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            currentLeadId = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const phone = button.getAttribute('data-phone');
            const email = button.getAttribute('data-email');
            const project = button.getAttribute('data-project');
            const msg = button.getAttribute('data-msg');
            const status = button.getAttribute('data-status');
            const staff = button.getAttribute('data-staff');
            const note = button.getAttribute('data-note');
            const date = button.getAttribute('data-date');

            // Fill text content
            document.getElementById('modal-lead-name').textContent = name;
            document.getElementById('modal-lead-phone').textContent = phone;
            document.getElementById('modal-lead-phone-link').href = 'tel:' + phone;
            document.getElementById('modal-lead-email').textContent = email || 'Chưa cập nhật';
            document.getElementById('modal-lead-date').textContent = date;
            document.getElementById('modal-lead-project').textContent = project;
            document.getElementById('modal-lead-msg').textContent = msg;

            // Fill form fields
            document.getElementById('modal-lead-status').value = status;
            document.getElementById('modal-lead-staff').value = staff;
            document.getElementById('modal-lead-note').value = note;

            const resolved = button.getAttribute('data-resolved') === '1';
            const report = button.getAttribute('data-report');
            document.getElementById('modal-lead-resolved').checked = resolved;
            document.getElementById('modal-lead-report').value = report || '';
        });
    }

    // ----------------------------------------------
    // AJAX LEAD UPDATE HANDLER
    // ----------------------------------------------
    const editLeadForm = document.getElementById('editLeadForm');
    const toast = document.getElementById('success-toast');

    function showToast(message) {
        document.getElementById('toast-msg').textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    if (editLeadForm) {
        editLeadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!currentLeadId) return;

            const formData = new FormData(editLeadForm);

            fetch('<?= URL_ROOT ?>/admin/khach-hang/update/' + currentLeadId, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update table row in UI instantly without full page reload
                    const row = document.getElementById('lead-row-' + currentLeadId);
                    
                    // 1. Update status badge
                    const badge = document.getElementById('badge-status-' + currentLeadId);
                    badge.className = 'status-badge badge-status-' + data.lead.trang_thai;
                    
                    let statusLabel = '';
                    switch(data.lead.trang_thai) {
                        case 'moi': statusLabel = 'Mới'; break;
                        case 'da_lien_he': statusLabel = 'Đã liên hệ'; break;
                        case 'tiem_nang': statusLabel = 'Tiềm năng'; break;
                        case 'that_bai': statusLabel = 'Thất bại'; break;
                        case 'thanh_cong': statusLabel = 'Thành công'; break;
                    }
                    badge.textContent = statusLabel;
                    row.setAttribute('data-status', data.lead.trang_thai);

                    // 2. Update staff cell
                    const staffCell = document.getElementById('cell-staff-' + currentLeadId);
                    if (data.lead.ten_nguoi_phu_trach) {
                        staffCell.innerHTML = `<span class="fw-medium text-dark"><i class="fa-solid fa-user-tie text-muted me-1"></i>${data.lead.ten_nguoi_phu_trach}</span>`;
                    } else {
                        staffCell.innerHTML = `<span class="text-muted small italic">Chưa phân công</span>`;
                    }

                    // 3. Update resolved checkbox & text inline
                    const inlineSwitch = row.querySelector('.resolve-toggle-switch');
                    if (inlineSwitch) {
                        inlineSwitch.checked = data.lead.da_giai_quyet == 1;
                    }
                    const textSpan = document.getElementById('resolve-text-' + currentLeadId);
                    if (textSpan) {
                        textSpan.innerHTML = data.lead.da_giai_quyet == 1 
                            ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Đã xong</span>' 
                            : '<span class="text-secondary"><i class="fa-regular fa-circle-xmark"></i> Chưa</span>';
                    }

                    // 4. Update resolution report text in row
                    const reportDiv = document.getElementById('lead-report-text-' + currentLeadId);
                    if (reportDiv) {
                        if (data.lead.bao_cao_giai_quyet) {
                            reportDiv.classList.remove('d-none');
                            reportDiv.title = data.lead.bao_cao_giai_quyet;
                            const reportContentSpan = reportDiv.querySelector('.report-content');
                            if (reportContentSpan) {
                                reportContentSpan.textContent = data.lead.bao_cao_giai_quyet;
                            }
                        } else {
                            reportDiv.classList.add('d-none');
                            const reportContentSpan = reportDiv.querySelector('.report-content');
                            if (reportContentSpan) {
                                reportContentSpan.textContent = '';
                            }
                        }
                    }

                    // 5. Update data attributes on trigger button so future clicks open with fresh data
                    const triggerBtn = row.querySelector('[data-bs-toggle="modal"][data-bs-target="#editLeadModal"]');
                    triggerBtn.setAttribute('data-status', data.lead.trang_thai);
                    triggerBtn.setAttribute('data-staff', data.lead.nguoi_phu_trach || '');
                    triggerBtn.setAttribute('data-note', data.lead.ghi_chu || '');
                    triggerBtn.setAttribute('data-resolved', data.lead.da_giai_quyet);
                    triggerBtn.setAttribute('data-report', data.lead.bao_cao_giai_quyet || '');

                    // Close modal & notify
                    const bootstrapModal = bootstrap.Modal.getInstance(editLeadModal);
                    bootstrapModal.hide();
                    showToast(data.message);
                } else {
                    alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Có lỗi kết nối xảy ra!');
            });
        });
    }

    // ----------------------------------------------
    // DELETE LEAD MODAL AUTO-FILL
    // ----------------------------------------------
    const deleteLeadModal = document.getElementById('deleteLeadModal');
    if (deleteLeadModal) {
        deleteLeadModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');

            document.getElementById('deleteLeadName').innerText = name;
            document.getElementById('deleteLeadForm').action = '<?= URL_ROOT ?>/admin/khach-hang/delete/' + id;
        });
    }

    // ----------------------------------------------
    // AJAX TOGGLE RESOLVE SWITCH IN TABLE ROW
    // ----------------------------------------------
    document.querySelectorAll('.resolve-toggle-switch').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const leadId = this.getAttribute('data-id');
            const isChecked = this.checked ? 1 : 0;
            const formData = new FormData();
            formData.append('da_giai_quyet', isChecked);
            formData.append('_csrf_token', '<?= Csrf::token() ?>');
            
            fetch('<?= URL_ROOT ?>/admin/khach-hang/toggle-resolve/' + leadId, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const textSpan = document.getElementById('resolve-text-' + leadId);
                    if (textSpan) {
                        textSpan.innerHTML = isChecked ? '<span class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Đã xong</span>' : '<span class="text-secondary"><i class="fa-regular fa-circle-xmark"></i> Chưa</span>';
                    }
                    const btn = document.querySelector(`#lead-row-${leadId} [data-bs-toggle="modal"]`);
                    if (btn) btn.setAttribute('data-resolved', isChecked);
                    showToast(data.message);
                } else {
                    alert(data.message);
                    this.checked = !this.checked;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Lỗi kết nối xảy ra!');
                this.checked = !this.checked;
            });
        });
    });
});
</script>

<?php require_once '../app/views/admin/layouts/footer.php'; ?>
