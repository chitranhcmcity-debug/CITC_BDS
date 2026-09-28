@include('admin.layouts.header')

<!-- CRM Analytics dashboard -->
<div class="row g-3 mb-4">
    <!-- Total leads -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 5px solid #2563eb;">
            <div class="card-body py-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-secondary small mb-1">Tổng Số Liên Hệ</h6>
                    <h3 class="fw-bold text-dark mb-0"><?= $data['totalItems'] ?></h3>
                </div>
                <span class="fs-2 text-primary opacity-75"><i class="fa-solid fa-headset"></i></span>
            </div>
        </div>
    </div>
    
    <!-- New contacts -->
    <?php
    $newCount = 0;
    $procCount = 0;
    $doneCount = 0;
    if (isset($data['analytics']['status'])) {
        foreach ($data['analytics']['status'] as $stat) {
            if ($stat->status === 'moi') $newCount = $stat->count;
            if ($stat->status === 'dang_xu_ly' || $stat->status === 'da_lien_he') $procCount += $stat->count;
            if ($stat->status === 'hoan_thanh') $doneCount = $stat->count;
        }
    }
    ?>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #fef2f2, #fee2e2); border-left: 5px solid #dc2626;">
            <div class="card-body py-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-secondary small mb-1">Yêu Cầu Mới</h6>
                    <h3 class="fw-bold text-danger mb-0"><?= $newCount ?></h3>
                </div>
                <span class="fs-2 text-danger opacity-75"><i class="fa-solid fa-bell"></i></span>
            </div>
        </div>
    </div>

    <!-- Processing -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #fffbeb, #fef3c7); border-left: 5px solid #d97706;">
            <div class="card-body py-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-secondary small mb-1">Đang Xử Lý</h6>
                    <h3 class="fw-bold text-warning mb-0"><?= $procCount ?></h3>
                </div>
                <span class="fs-2 text-warning opacity-75"><i class="fa-solid fa-spinner fa-spin"></i></span>
            </div>
        </div>
    </div>

    <!-- Completed -->
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-left: 5px solid #16a34a;">
            <div class="card-body py-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-secondary small mb-1">Đã Hoàn Thành</h6>
                    <h3 class="fw-bold text-success mb-0"><?= $doneCount ?></h3>
                </div>
                <span class="fs-2 text-success opacity-75"><i class="fa-solid fa-circle-check"></i></span>
            </div>
        </div>
    </div>
</div>

<!-- Main CRM Workspace -->
<div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-white border-0 pt-4 pb-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>CRM - Yêu Cầu Liên Hệ & Tư Vấn</h4>
            
            <!-- Actions -->
            <div class="d-flex gap-2">
                <a href="<?= URL_ROOT ?>/admin/contact/export?<?= http_build_query($data['filters']) ?>" class="btn btn-success fw-semibold shadow-xs" style="border-radius: 8px;">
                    <i class="fa-solid fa-file-excel me-1.5"></i>Xuất Excel
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Advanced Filters -->
        <form method="GET" action="<?= URL_ROOT ?>/admin/contact" class="bg-light p-3 rounded-4 mb-4">
            <div class="row g-3">
                <!-- Search term -->
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary small fw-semibold">Từ khóa tìm kiếm</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control text-dark border-start-0" placeholder="Tìm theo Tên, SĐT, Email, Nội dung..." value="<?= htmlspecialchars($data['filters']['search']) ?>">
                    </div>
                </div>

                <!-- Request Type -->
                <div class="col-6 col-md-2">
                    <label class="form-label text-secondary small fw-semibold">Nhu cầu</label>
                    <select name="type" class="form-select form-select-sm text-dark">
                        <option value="">Tất cả loại</option>
                        <option value="mua_nha" <?= $data['filters']['type'] === 'mua_nha' ? 'selected' : '' ?>>Mua nhà</option>
                        <option value="thue_nha" <?= $data['filters']['type'] === 'thue_nha' ? 'selected' : '' ?>>Thuê nhà</option>
                        <option value="dang_ban" <?= $data['filters']['type'] === 'dang_ban' ? 'selected' : '' ?>>Đăng bán</option>
                        <option value="dang_cho_thue" <?= $data['filters']['type'] === 'dang_cho_thue' ? 'selected' : '' ?>>Đăng cho thuê</option>
                        <option value="hop_tac" <?= $data['filters']['type'] === 'hop_tac' ? 'selected' : '' ?>>Hợp tác</option>
                        <option value="khieu_nai" <?= $data['filters']['type'] === 'khieu_nai' ? 'selected' : '' ?>>Khiếu nại</option>
                        <option value="khac" <?= $data['filters']['type'] === 'khac' ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-6 col-md-2">
                    <label class="form-label text-secondary small fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select form-select-sm text-dark">
                        <option value="">Tất cả trạng thái</option>
                        <option value="moi" <?= $data['filters']['status'] === 'moi' ? 'selected' : '' ?>>Mới nhận</option>
                        <option value="dang_xu_ly" <?= $data['filters']['status'] === 'dang_xu_ly' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="da_lien_he" <?= $data['filters']['status'] === 'da_lien_he' ? 'selected' : '' ?>>Đã liên hệ</option>
                        <option value="hoan_thanh" <?= $data['filters']['status'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                        <option value="huy" <?= $data['filters']['status'] === 'huy' ? 'selected' : '' ?>>Hủy</option>
                    </select>
                </div>

                <!-- Assigned CSKH -->
                <div class="col-12 col-md-2">
                    <label class="form-label text-secondary small fw-semibold">Người xử lý</label>
                    <select name="assigned_admin" class="form-select form-select-sm text-dark">
                        <option value="">Tất cả CSKH</option>
                        <?php foreach ($data['admins'] as $admin): ?>
                            <option value="<?= $admin->id ?>" <?= $data['filters']['assigned_admin'] == $admin->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($admin->ten) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter & Reset Buttons -->
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" style="height: 31px; border-radius: 6px;">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                    <a href="<?= URL_ROOT ?>/admin/contact" class="btn btn-outline-secondary btn-sm w-100 fw-semibold" style="height: 31px; border-radius: 6px;">
                        Xóa lọc
                    </a>
                </div>
            </div>
        </form>

        <!-- Flash Alert -->
        <?php if (Session::flash('admin_contact_success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                <i class="fa-solid fa-circle-check me-2"></i><?= Session::flash('admin_contact_success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (Session::flash('admin_contact_error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?= Session::flash('admin_contact_error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Contacts Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="font-size: 0.88rem;">
                <thead class="table-light text-secondary">
                    <tr>
                        <th style="width: 50px;">Mã</th>
                        <th>Khách hàng</th>
                        <th>Nhu cầu</th>
                        <th>Nội dung câu hỏi</th>
                        <th>Người phụ trách</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th class="text-center" style="width: 140px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="text-dark">
                    <?php if (empty($data['contacts'])): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="fs-1"><i class="fa-regular fa-folder-open"></i></span>
                                <h6 class="mt-2 mb-0">Không tìm thấy yêu cầu tư vấn nào phù hợp.</h6>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['contacts'] as $item): ?>
                            <tr>
                                <td class="fw-bold">#<?= $item['id'] ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($item['fullname']) ?></div>
                                    <div class="text-secondary small"><?= htmlspecialchars($item['phone']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($item['email'] ?: 'Chưa cung cấp') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1.5 fw-semibold">
                                        <?= match($item['type']) {
                                            'mua_nha'       => 'Mua nhà',
                                            'thue_nha'      => 'Thuê nhà',
                                            'dang_ban'      => 'Đăng bán',
                                            'dang_cho_thue' => 'Đăng cho thuê',
                                            'hop_tac'       => 'Hợp tác',
                                            'khieu_nai'     => 'Khiếu nại',
                                            default         => 'Khác'
                                        } ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($item['subject'] ?: 'Không tiêu đề') ?>
                                    </div>
                                    <div class="text-muted small text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($item['content'] ?: 'Không có nội dung.') ?>
                                    </div>
                                    <!-- Device details -->
                                    <div class="text-secondary small mt-1" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-laptop me-1"></i><?= $item['device'] ?> | <i class="fa-solid fa-globe me-1"></i><?= $item['browser'] ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($item['admin_name'])): ?>
                                        <span class="fw-semibold text-dark"><i class="fa-regular fa-user me-1 text-primary"></i><?= htmlspecialchars($item['admin_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-danger small"><i class="fa-solid fa-triangle-exclamation me-1"></i>Chưa phân công</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= match($item['status']) {
                                        'moi'          => '<span class="badge bg-danger px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-bell me-1"></i>Mới nhận</span>',
                                        'dang_xu_ly'   => '<span class="badge bg-warning text-dark px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-spinner fa-spin me-1"></i>Đang xử lý</span>',
                                        'da_lien_he'   => '<span class="badge bg-info text-dark px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-phone me-1"></i>Đã liên hệ</span>',
                                        'hoan_thanh'   => '<span class="badge bg-success px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Hoàn thành</span>',
                                        'huy'          => '<span class="badge bg-secondary px-2.5 py-1.5 fw-semibold"><i class="fa-solid fa-xmark me-1"></i>Đã hủy</span>',
                                        default        => '<span class="badge bg-secondary px-2.5 py-1.5">Khác</span>'
                                    } ?>
                                </td>
                                <td class="text-secondary" style="font-size: 0.8rem;"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1.5">
                                        <!-- Update Modal trigger -->
                                        <button class="btn btn-primary btn-sm px-2.5" style="border-radius: 6px;" data-bs-toggle="modal" data-bs-target="#edit-modal-<?= $item['id'] ?>" title="Xử lý CRM">
                                            <i class="fa-solid fa-user-gear"></i> Xử lý
                                        </button>
                                        
                                        <!-- Delete Form -->
                                        <form method="POST" action="<?= URL_ROOT ?>/admin/contact/delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu này? Hành động này sẽ xóa cả tệp đính kèm liên quan.');">
                                            <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2" style="border-radius: 6px;" title="Xóa yêu cầu">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- CRM Handling Modal -->
                            <div class="modal fade" id="edit-modal-<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content text-dark" style="border-radius: 16px; overflow: hidden;">
                                        <form method="POST" action="<?= URL_ROOT ?>/admin/contact/update">
                                            <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">

                                            <div class="modal-header border-0 bg-light py-3">
                                                <h5 class="modal-title fw-bold"><i class="fa-solid fa-headset text-primary me-2"></i>Xử Lý Yêu Cầu CRM #<?= $item['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                <div class="row g-4">
                                                    <!-- Left section: request details -->
                                                    <div class="col-12 col-lg-7">
                                                        <h6 class="fw-bold mb-2.5 text-primary border-bottom pb-1.5">Thông tin chi tiết yêu cầu</h6>
                                                        
                                                        <div class="mb-3">
                                                            <span class="small text-secondary fw-semibold d-block">Khách hàng</span>
                                                            <div class="fw-bold text-dark mt-0.5"><?= htmlspecialchars($item['fullname']) ?></div>
                                                            <div class="text-secondary small">SĐT: <strong><?= htmlspecialchars($item['phone']) ?></strong> | Email: <?= htmlspecialchars($item['email'] ?: 'N/A') ?></div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <span class="small text-secondary fw-semibold d-block">Tiêu đề liên hệ</span>
                                                            <div class="fw-semibold text-dark mt-0.5"><?= htmlspecialchars($item['subject'] ?: 'Không có') ?></div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <span class="small text-secondary fw-semibold d-block">Nội dung chi tiết</span>
                                                            <div class="p-3 bg-light rounded-3 mt-1 small" style="white-space: pre-wrap; font-size: 0.85rem; border: 1px solid #e2e8f0;"><?= htmlspecialchars($item['content'] ?: 'Không có nội dung chi tiết.') ?></div>
                                                        </div>

                                                        <!-- File attachments -->
                                                        <?php if (!empty($item['attachments'])): ?>
                                                            <div class="mb-3">
                                                                <span class="small text-secondary fw-semibold d-block mb-1.5"><i class="fa-solid fa-paperclip me-1"></i>Tài liệu đính kèm của khách</span>
                                                                <div class="d-flex flex-column gap-2">
                                                                    <?php foreach ($item['attachments'] as $file): ?>
                                                                        <a href="<?= URL_ROOT ?>/public/uploads/<?= $file->file_path ?>" target="_blank" class="d-flex align-items-center text-decoration-none bg-light p-2 border rounded" style="font-size: 0.8rem; color: #1e40af;">
                                                                            <i class="fa-solid fa-file-arrow-down me-2 fs-5 text-primary"></i>
                                                                            <div class="text-truncate" style="max-width: 250px;"><?= htmlspecialchars($file->file_name) ?></div>
                                                                            <div class="text-muted small ms-auto"><?= round($file->file_size / 1024, 1) ?> KB</div>
                                                                        </a>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Right section: CRM actions -->
                                                    <div class="col-12 col-lg-5">
                                                        <h6 class="fw-bold mb-2.5 text-primary border-bottom pb-1.5">Nghiệp vụ CSKH / Phân công</h6>

                                                        <!-- Assigned CSKH -->
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Chuyên viên xử lý</label>
                                                            <select name="assigned_admin" class="form-select form-select-sm text-dark">
                                                                <option value="">Chưa phân công</option>
                                                                <?php foreach ($data['admins'] as $admin): ?>
                                                                    <option value="<?= $admin->id ?>" <?= $item['assigned_admin'] == $admin->id ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($admin->ten) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <!-- Status -->
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-secondary mb-1">Trạng thái xử lý</label>
                                                            <select name="status" class="form-select form-select-sm text-dark">
                                                                <option value="moi" <?= $item['status'] === 'moi' ? 'selected' : '' ?>>Mới nhận</option>
                                                                <option value="dang_xu_ly" <?= $item['status'] === 'dang_xu_ly' ? 'selected' : '' ?>>Đang xử lý</option>
                                                                <option value="da_lien_he" <?= $item['status'] === 'da_lien_he' ? 'selected' : '' ?>>Đã liên hệ</option>
                                                                <option value="hoan_thanh" <?= $item['status'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                                                                <option value="huy" <?= $item['status'] === 'huy' ? 'selected' : '' ?>>Đã hủy</option>
                                                            </select>
                                                        </div>

                                                        <!-- Ghi chú nội bộ -->
                                                        <div class="mb-3">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <label class="form-label small fw-semibold text-secondary mb-0">Ghi chú & Phản hồi</label>
                                                                <!-- AI Response Button -->
                                                                <button type="button" class="btn btn-link text-primary p-0 small fw-semibold text-decoration-none" onclick="getAISuggestion(<?= $item['id'] ?>, '<?= htmlspecialchars(json_encode($item['content'] ?: ''), ENT_QUOTES, 'UTF-8') ?>')">
                                                                    🤖 AI Suggestion
                                                                </button>
                                                            </div>
                                                            <textarea name="note" id="note-field-<?= $item['id'] ?>" rows="4" class="form-control form-control-sm text-dark" placeholder="Ghi chú cuộc gọi, phương án tư vấn hoặc phản hồi khách hàng..."><?= htmlspecialchars($item['note'] ?: '') ?></textarea>
                                                            <div class="form-text text-muted" style="font-size: 0.72rem;">Ghi chú nội bộ hỗ trợ quản lý hiệu quả cuộc gọi CSKH.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer bg-light border-0">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-primary btn-sm px-4">Lưu cập nhật</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($data['totalPages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($data['filters'], ['page' => $data['currentPage'] - 1])) ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                        <li class="page-item <?= $data['currentPage'] === $i ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($data['filters'], ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($data['filters'], ['page' => $data['currentPage'] + 1])) ?>">Sau</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<!-- AI suggested response script -->
<script>
async function getAISuggestion(contactId, requestContent) {
    const textarea = document.getElementById(`note-field-${contactId}`);
    if (!textarea) return;

    if (textarea.value.trim() !== '') {
        const accepted = await AdminDialog.confirm('Phản hồi gợi ý bằng AI sẽ thay thế ghi chú hiện tại. Bạn có muốn tiếp tục không?', { title: 'Thay thế ghi chú?', type: 'warning', confirmText: 'Tiếp tục' });
        if (!accepted) return;
    }

    textarea.value = "🤖 Đang kết nối AI để tạo gợi ý phản hồi tư vấn...";
    textarea.disabled = true;

    fetch(`<?= URL_ROOT ?>/admin/contact/aiSuggest?content=${encodeURIComponent(requestContent)}`)
        .then(response => response.json())
        .then(data => {
            textarea.disabled = false;
            if (data.success) {
                textarea.value = data.suggestion;
            } else {
                textarea.value = "";
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(err => {
            textarea.disabled = false;
            textarea.value = "";
            alert('Không thể kết nối đến máy chủ AI.');
        });
}
</script>

@include('admin.layouts.footer')
