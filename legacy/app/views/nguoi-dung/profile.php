<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <?php require_once '../app/views/nguoi-dung/sidebar.php'; ?>

        <div class="col-lg-9 col-md-8">
            <h4 class="fw-bold mb-4 border-bottom pb-2">Sửa thông tin cá nhân</h4>

            <?php if (Session::get('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= Session::get('success'); Session::delete('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (Session::get('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= Session::get('error'); Session::delete('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?= URL_ROOT ?>/nguoi-dung/profile" method="POST">
                        <?= Csrf::field() ?>
                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label fw-bold text-md-end">Tên đăng nhập / Email:</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data['user']->email ?? '') ?>" disabled>
                                <small class="text-muted">Email dùng để đăng nhập, không thể thay đổi.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-form-label fw-bold text-md-end">Họ và tên:</label>
                            <div class="col-md-7">
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['user']->ten ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label class="col-md-3 col-form-label fw-bold text-md-end">Số điện thoại:</label>
                            <div class="col-md-7">
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['user']->dien_thoai ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-7 offset-md-3">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="fa-solid fa-save"></i> Lưu thông tin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>
