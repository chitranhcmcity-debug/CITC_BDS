<?php
/**
 * View: Admin – Quản lý bình luận tin tức
 * Route: GET /admin/tin-tuc/comments
 */
$comments = $comments ?? [];
$status   = $status ?? '';
?>
<?php require_once APP_ROOT . '/app/views/admin/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold mb-0"><i class="fas fa-comments text-primary me-2"></i>Quản lý bình luận Tin tức</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">Duyệt và xử lý bình luận người dùng</p>
        </div>
        <div class="d-flex gap-2">
            <a href="?status=hien" class="btn btn-sm <?= $status === 'hien' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill">
                <i class="fas fa-eye me-1"></i>Hiển thị
            </a>
            <a href="?status=an" class="btn btn-sm <?= $status === 'an' ? 'btn-warning' : 'btn-outline-warning' ?> rounded-pill">
                <i class="fas fa-eye-slash me-1"></i>Đã ẩn
            </a>
            <a href="?" class="btn btn-sm <?= !$status ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill">
                Tất cả
            </a>
        </div>
    </div>

    <?php if (Session::hasFlash('admin_msg')): ?>
    <div class="alert alert-info alert-dismissible">
        <?= Session::flash('admin_msg') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Người dùng</th>
                        <th>Bài viết</th>
                        <th>Nội dung</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">Không có bình luận nào</td></tr>
                    <?php endif; ?>
                    <?php foreach ($comments as $c): ?>
                    <tr>
                        <td class="text-muted">#<?= $c->id ?></td>
                        <td><?= htmlspecialchars($c->ten_nguoi_dung ?? '', ENT_QUOTES) ?></td>
                        <td>
                            <a href="<?= URL_ROOT ?>/tin-tuc/detail/<?= htmlspecialchars($c->slug_bai ?? '', ENT_QUOTES) ?>"
                               target="_blank" class="text-truncate d-block" style="max-width:180px;">
                                <?= htmlspecialchars($c->tieu_de_bai ?? '', ENT_QUOTES) ?>
                            </a>
                        </td>
                        <td style="max-width:260px;">
                            <span class="d-block text-truncate"><?= htmlspecialchars($c->noi_dung ?? '', ENT_QUOTES) ?></span>
                        </td>
                        <td>
                            <?php if ($c->trang_thai === 'hien'): ?>
                            <span class="badge bg-success">Hiển thị</span>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark">Đã ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($c->ngay_tao ?? 'now')) ?></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <?php if ($c->trang_thai === 'hien'): ?>
                                <form method="POST" action="<?= URL_ROOT ?>/admin/tin-tuc/hide-comment/<?= $c->id ?>">
                                    <?= Csrf::field() ?>
                                    <button class="btn btn-sm btn-warning rounded-pill"
                                            title="Ẩn bình luận">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" action="<?= URL_ROOT ?>/admin/tin-tuc/unhide-comment/<?= $c->id ?>">
                                    <?= Csrf::field() ?>
                                    <button class="btn btn-sm btn-success rounded-pill" title="Hiện bình luận">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" action="<?= URL_ROOT ?>/admin/tin-tuc/delete-comment/<?= $c->id ?>"
                                      onsubmit="return confirm('Xóa vĩnh viễn bình luận này?')">
                                    <?= Csrf::field() ?>
                                    <button class="btn btn-sm btn-danger rounded-pill" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once APP_ROOT . '/app/views/admin/layouts/footer.php'; ?>
