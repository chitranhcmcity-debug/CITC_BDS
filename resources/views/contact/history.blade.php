@include('layouts.header')

<!-- Breadcrumb Header -->
<div class="py-4 text-white" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
    <div class="container text-center">
        <h2 class="fw-bold mb-0">Lịch Sử Yêu Cầu Tư Vấn</h2>
        <p class="text-white-50 small mb-0 mt-1">Theo dõi quá trình phản hồi và xử lý các yêu cầu của bạn</p>
    </div>
</div>

<section class="py-5 bg-light" style="min-height: 70vh;">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <?php require APP_ROOT . '/app/views/nguoi-dung/sidebar.php'; ?>
            </div>

            <!-- Content Area -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Danh sách yêu cầu đã gửi</h4>
                            <span class="badge bg-secondary px-3 py-2 fw-semibold" style="font-size: 0.85rem;"><?= $data['totalItems'] ?> yêu cầu</span>
                        </div>

                        <?php if (empty($data['history'])): ?>
                            <div class="text-center py-5">
                                <span class="text-muted" style="font-size: 4rem;"><i class="fa-solid fa-inbox"></i></span>
                                <h6 class="text-secondary mt-3">Bạn chưa gửi yêu cầu tư vấn nào.</h6>
                                <p class="text-muted small">Hãy liên hệ với chúng tôi nếu bạn cần giúp đỡ.</p>
                                <a href="<?= URL_ROOT ?>/contact" class="btn btn-primary btn-sm px-4 fw-semibold mt-2" style="border-radius: 6px;">Gửi liên hệ ngay</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" style="font-size: 0.9rem;">
                                    <thead class="table-light text-secondary">
                                        <tr>
                                            <th>Mã</th>
                                            <th>Nhu cầu</th>
                                            <th>Tiêu đề</th>
                                            <th>Ngày gửi</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-dark">
                                        <?php foreach ($data['history'] as $item): ?>
                                            <tr>
                                                <td class="fw-bold text-primary">#<?= $item['id'] ?></td>
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
                                                        <?= htmlspecialchars($item['subject'] ?: 'Không có tiêu đề') ?>
                                                    </div>
                                                    <div class="text-muted small text-truncate" style="max-width: 250px;">
                                                        <?= htmlspecialchars($item['content']) ?>
                                                    </div>
                                                </td>
                                                <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></td>
                                                <td>
                                                    <?= match($item['status']) {
                                                        'moi'          => '<span class="badge bg-primary px-2 py-1.5 fw-semibold"><i class="fa-solid fa-envelope me-1"></i>Mới</span>',
                                                        'dang_xu_ly'   => '<span class="badge bg-warning text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-spinner fa-spin me-1"></i>Đang xử lý</span>',
                                                        'da_lien_he'   => '<span class="badge bg-info text-dark px-2 py-1.5 fw-semibold"><i class="fa-solid fa-phone me-1"></i>Đã liên hệ</span>',
                                                        'hoan_thanh'   => '<span class="badge bg-success px-2 py-1.5 fw-semibold"><i class="fa-solid fa-check me-1"></i>Hoàn thành</span>',
                                                        'huy'          => '<span class="badge bg-secondary px-2 py-1.5 fw-semibold"><i class="fa-solid fa-xmark me-1"></i>Hủy</span>',
                                                        default        => '<span class="badge bg-secondary px-2 py-1.5 fw-semibold">Khác</span>'
                                                    } ?>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-outline-primary btn-sm px-3 fw-semibold" style="border-radius: 6px;" 
                                                            data-bs-toggle="modal" data-bs-target="#modal-contact-<?= $item['id'] ?>">
                                                        Chi tiết
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Chi tiết yêu cầu -->
                                            <div class="modal fade" id="modal-contact-<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content" style="border-radius: 12px;">
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-circle-info text-primary me-2"></i>Chi Tiết Yêu Cầu #<?= $item['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body pt-3">
                                                            <!-- Client Info -->
                                                            <div class="mb-3">
                                                                <span class="small text-secondary fw-semibold">Thông tin liên lạc</span>
                                                                <div class="fw-semibold text-dark mt-1"><?= htmlspecialchars($item['fullname']) ?></div>
                                                                <div class="text-secondary small"><?= htmlspecialchars($item['phone']) ?> | <?= htmlspecialchars($item['email'] ?: 'Chưa cung cấp') ?></div>
                                                            </div>

                                                            <!-- Content -->
                                                            <div class="mb-3">
                                                                <span class="small text-secondary fw-semibold">Chi tiết nội dung yêu cầu</span>
                                                                <div class="p-3 bg-light text-dark small mt-1" style="border-radius: 8px; white-space: pre-wrap; font-size: 0.85rem;"><?= htmlspecialchars($item['content'] ?: 'Không có nội dung mô tả.') ?></div>
                                                            </div>

                                                            <!-- Note from admin -->
                                                            <?php if (!empty($item['note'])): ?>
                                                                <div class="mb-3">
                                                                    <span class="small text-danger fw-semibold"><i class="fa-solid fa-comment-dots me-1"></i>Phản hồi từ chuyên viên</span>
                                                                    <div class="p-3 text-dark small mt-1" style="border-radius: 8px; background-color: #fef2f2; border: 1px solid #fecaca; font-size: 0.85rem;"><?= htmlspecialchars($item['note']) ?></div>
                                                                </div>
                                                            <?php endif; ?>

                                                            <!-- Attachments -->
                                                            <?php if (!empty($item['attachments'])): ?>
                                                                <div class="mb-0">
                                                                    <span class="small text-secondary fw-semibold"><i class="fa-solid fa-paperclip me-1"></i>Tài liệu đính kèm</span>
                                                                    <div class="d-flex flex-column gap-2 mt-1">
                                                                        <?php foreach ($item['attachments'] as $file): ?>
                                                                            <a href="<?= URL_ROOT ?>/public/uploads/<?= $file->file_path ?>" target="_blank" class="d-flex align-items-center text-decoration-none bg-light p-2 border rounded" style="font-size: 0.8rem; color: #1e40af;">
                                                                                <i class="fa-solid fa-file-arrow-down me-2 fs-5"></i>
                                                                                <div class="text-truncate" style="max-width: 320px;"><?= htmlspecialchars($file->file_name) ?></div>
                                                                                <div class="text-muted small ms-auto"><?= round($file->file_size / 1024, 1) ?> KB</div>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Phân trang -->
                            <?php if ($data['totalPages'] > 1): ?>
                                <nav class="mt-4">
                                    <ul class="pagination pagination-sm justify-content-center mb-0">
                                        <li class="page-item <?= $data['currentPage'] <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $data['currentPage'] - 1 ?>">Trước</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                                            <li class="page-item <?= $data['currentPage'] === $i ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= $data['currentPage'] >= $data['totalPages'] ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $data['currentPage'] + 1 ?>">Sau</a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('layouts.footer')
