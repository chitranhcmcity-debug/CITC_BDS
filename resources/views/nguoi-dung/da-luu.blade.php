@include('layouts.header')

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        @include('nguoi-dung.sidebar')

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">

            <!-- Flash message -->
            <?php if (Session::get('msg')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> <?= Session::get('msg') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php Session::delete('msg'); ?>
            <?php endif; ?>

            <!-- Header -->
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                <h5 class="fw-bold mb-0 text-danger">
                    <i class="fa-solid fa-heart me-2"></i>Tin BĐS Đã Lưu
                    <span class="badge bg-danger ms-2 fs-6"><?= count($saved_projects) ?></span>
                </h5>
                <a href="<?= URL_ROOT ?>/du-an" class="btn btn-sm btn-outline-danger rounded-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Khám phá thêm BĐS
                </a>
            </div>
            <p class="text-muted small mb-4">Danh sách các tin đăng bạn đã đánh dấu quan tâm. Bấm nút <span class="text-danger fw-bold">Xóa</span> để bỏ lưu tin đó.</p>

            <?php if (empty($saved_projects)): ?>
                <!-- Empty State -->
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <i class="fa-regular fa-heart text-danger" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h5 class="mt-3 text-muted">Bạn chưa lưu tin đăng nào!</h5>
                    <p class="text-muted mb-4">Hãy duyệt qua các dự án và lưu lại để dễ dàng xem lại sau.</p>
                    <a href="<?= URL_ROOT ?>/du-an" class="btn btn-danger px-5 rounded-1">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Khám phá ngay
                    </a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                    <?php foreach ($saved_projects as $project): ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden position-relative" style="transition: box-shadow .2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.13)'" onmouseout="this.style.boxShadow=''">

                            <!-- Ảnh -->
                            <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>">
                                <img src="<?= img_url($project->anh_thu_nho ?? '') ?>"
                                    class="card-img-top object-fit-cover" height="180"
                                    alt="<?= htmlspecialchars($project->tieu_de) ?>"
                                    onerror="this.src='https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&q=80'">
                            </a>

                            <!-- Badge VIP nếu có -->
                            <?php if (!empty($project->goi_vip) && $project->goi_vip > 0): ?>
                            <span class="position-absolute top-0 start-0 m-2 badge vip-badge">
                                <i class="fa-solid fa-star me-1"></i>VIP <?= $project->goi_vip ?>
                            </span>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column p-3">
                                <!-- Tiêu đề -->
                                <h6 class="card-title fw-bold text-danger mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 42px;">
                                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>" class="text-danger text-decoration-none">
                                        <?= htmlspecialchars($project->tieu_de) ?>
                                    </a>
                                </h6>

                                <!-- Giá & Diện tích -->
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-danger small">
                                        <i class="fa-solid fa-tag me-1"></i>
                                        <?= $project->gia ? number_format((float)$project->gia) . ' đ' : 'Thỏa thuận' ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fa-solid fa-vector-square me-1"></i>
                                        <?= $project->dien_tich ? htmlspecialchars($project->dien_tich) . ' m²' : 'Đang cập nhật' ?>
                                    </span>
                                </div>

                                <!-- Vị trí -->
                                <p class="text-muted small mb-2 text-truncate">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                    <?= htmlspecialchars($project->vi_tri ?: 'Đang cập nhật') ?>
                                </p>

                                <!-- Ngày lưu -->
                                <p class="text-muted small mt-auto mb-3">
                                    <i class="fa-regular fa-clock me-1"></i>Đã lưu: <?= date('d/m/Y H:i', strtotime($project->ngay_luu)) ?>
                                </p>

                                <!-- Actions -->
                                <div class="d-flex gap-2">
                                    <a href="<?= URL_ROOT ?>/du-an/detail/<?= $project->duong_dan ?>" class="btn btn-sm btn-outline-primary flex-grow-1 rounded-1">
                                        <i class="fa-solid fa-eye me-1"></i>Xem tin
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger rounded-1"
                                        onclick="confirmDelete(<?= $project->id ?>, '<?= htmlspecialchars($project->duong_dan) ?>', '<?= htmlspecialchars(addslashes($project->tieu_de), ENT_QUOTES) ?>')"
                                        title="Bỏ lưu tin này">
                                        <i class="fa-solid fa-trash-can me-1"></i>Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Xác nhận Xóa -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Xác nhận bỏ lưu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa-regular fa-heart-crack text-danger mb-3" style="font-size: 3rem;"></i>
                <p class="mb-1">Bạn có chắc muốn bỏ lưu tin:</p>
                <p class="fw-bold text-danger" id="modalProjectTitle">—</p>
                <p class="text-muted small">Bạn vẫn có thể lưu lại tin này bất cứ lúc nào.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4 rounded-1" data-bs-dismiss="modal">Hủy bỏ</button>
                <form id="deleteForm" action="" method="POST" class="d-inline">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="slug" id="deleteSlug" value="">
                    <input type="hidden" name="redirect_to" value="daLuu">
                    <button type="submit" class="btn btn-danger px-4 rounded-1">
                        <i class="fa-solid fa-trash-can me-1"></i>Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(projectId, slug, title) {
    document.getElementById('modalProjectTitle').textContent = title;
    document.getElementById('deleteSlug').value = slug;
    document.getElementById('deleteForm').action = '<?= URL_ROOT ?>/nguoi-dung/toggleLuu/' + projectId;
    var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    modal.show();
}
</script>

@include('layouts.footer')
