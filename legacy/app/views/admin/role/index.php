<?php
require_once APP_ROOT . '/app/views/admin/layouts/header.php';
$grouped = [];
foreach ($permissions as $permission) $grouped[$permission->module][] = $permission;
$moduleLabels = [
    'dashboard'=>'Tổng quan', 'users'=>'Người dùng', 'properties'=>'Tin đăng bất động sản',
    'news'=>'Tin tức', 'categories'=>'Danh mục & dữ liệu nền', 'chat'=>'Chat chăm sóc khách hàng',
    'wallet'=>'Ví điện tử', 'payments'=>'Nạp tiền & thanh toán',
    'reports'=>'Báo cáo & thống kê', 'settings'=>'Cài đặt hệ thống',
    'system_logs'=>'Nhật ký hệ thống', 'roles'=>'Vai trò & phân quyền'
];
$actionLabels = [
    'view'=>'Xem', 'create'=>'Thêm mới', 'update'=>'Chỉnh sửa', 'delete'=>'Xóa',
    'approve'=>'Phê duyệt', 'reject'=>'Từ chối', 'export'=>'Xuất dữ liệu',
    'import'=>'Nhập dữ liệu', 'restore'=>'Khôi phục', 'assign'=>'Gán quyền/vai trò'
];
?>
<style>
.rbac-page .card{border-radius:.75rem}.rbac-page .card-header{padding:.65rem .8rem}.rbac-page .list-group-item{padding:.55rem .75rem;border-left:3px solid transparent}.rbac-page .list-group-item.active{border-left-color:#fff}.rbac-page .card-body{padding:.75rem}.rbac-page .rbac-module{padding:0!important;margin:0!important;height:100%;overflow:hidden;background:#fff}.rbac-page .rbac-module-title{margin:0!important;padding:.55rem .7rem;background:#eef5ff;border-bottom:1px solid #dbe8fa;color:#0d6efd!important;letter-spacing:.02em}.rbac-page .rbac-module-body{padding:.5rem}.rbac-page .permission-option{display:flex;align-items:center;gap:.45rem;margin:0;padding:.38rem .5rem!important;min-height:2rem;font-size:.92rem;border:1px solid #edf0f3;border-radius:.45rem;background:#fafbfc;cursor:pointer;transition:.15s}.rbac-page .permission-option:hover{border-color:#9ec5fe;background:#f0f6ff}.rbac-page .permission-option:has(.form-check-input:checked){border-color:#86b7fe;background:#eaf3ff;color:#084298;font-weight:600}.rbac-page .permission-option .form-check-input{float:none;margin:0;width:1.05rem;height:1.05rem;flex:0 0 auto}.rbac-page .alert{padding:.5rem .7rem;margin-bottom:.6rem}.rbac-page .form-label{margin-bottom:.2rem}.rbac-page .role-description{line-height:1.2;margin-top:.12rem}.rbac-page .rbac-permission-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.rbac-page .role-sidebar{position:sticky;top:.75rem}.rbac-page .rbac-actions{position:sticky;bottom:0;background:rgba(255,255,255,.96);padding:.65rem 0 .1rem;z-index:2;border-top:1px solid #e5e7eb;backdrop-filter:blur(4px)}
@media(min-width:1500px){.rbac-page .rbac-permission-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:991.98px){.rbac-page .rbac-permission-grid{grid-template-columns:1fr}.rbac-page .role-sidebar{position:static}}
</style>
<div class="content-wrapper p-2 bg-light rbac-page">
  <div class="mb-2"><h1 class="h4 fw-bold mb-0"><i class="fa-solid fa-user-shield text-primary me-2"></i>Phân quyền & Vai trò</h1><div class="text-muted small"><?= count($roles) ?> vai trò · <?= count($permissions) ?> quyền phù hợp với hệ thống</div></div>
  <?php Session::flash('rbac_msg'); ?>
  <div class="row g-3">
    <div class="col-lg-4"><div class="role-sidebar">
      <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">Vai trò</div><div class="list-group list-group-flush">
        <?php foreach ($roles as $role): ?><a href="?role=<?= $role->id ?>" class="list-group-item list-group-item-action d-flex justify-content-between <?= $selectedRole && $selectedRole->id==$role->id?'active':'' ?>">
          <span><b><?= htmlspecialchars($role->ten) ?></b><small class="d-block opacity-75 role-description"><?= htmlspecialchars($role->mo_ta??'') ?></small></span>
          <span class="text-end"><span class="badge bg-secondary"><?= $role->user_count ?> người</span><small class="d-block mt-1"><?= $role->permission_count ?> quyền</small></span>
        </a><?php endforeach; ?>
      </div></div>
      <div class="card border-0 shadow-sm mt-2"><div class="card-header bg-white fw-bold">Tạo vai trò mới</div><form class="card-body" method="POST" action="<?= URL_ROOT ?>/admin/roles/create"><?= Csrf::field() ?><input class="form-control form-control-sm mb-1" name="name" placeholder="Tên vai trò" required><textarea class="form-control form-control-sm mb-1" name="description" rows="2" placeholder="Mô tả nhiệm vụ"></textarea><button class="btn btn-primary btn-sm w-100">Tạo vai trò</button></form></div>
      </div>
    </div>
    <div class="col-lg-8">
      <?php if ($selectedRole): ?>
      <div class="card border-0 shadow-sm"><div class="card-header bg-white d-flex justify-content-between align-items-center"><span class="fw-bold">Quyền của <?= htmlspecialchars($selectedRole->ten) ?></span><?php if (!(int)$selectedRole->is_system): ?><form method="POST" action="<?= URL_ROOT ?>/admin/roles/delete/<?= $selectedRole->id ?>" onsubmit="return confirm('Xóa vai trò này?')"><?= Csrf::field() ?><button class="btn btn-outline-danger btn-sm">Xóa vai trò</button></form><?php endif; ?></div>
        <form method="POST" action="<?= URL_ROOT ?>/admin/roles/edit/<?= $selectedRole->id ?>" class="card-body border-bottom bg-light">
          <?= Csrf::field() ?>
          <div class="d-flex justify-content-between align-items-center mb-2"><span class="fw-semibold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Chỉnh sửa vai trò</span><?php if ((int)$selectedRole->is_system): ?><span class="badge bg-secondary">Vai trò hệ thống</span><?php endif; ?></div>
          <div class="row g-2">
            <div class="col-md-4"><label class="form-label small">Tên vai trò</label><input class="form-control" name="name" value="<?= htmlspecialchars($selectedRole->ten) ?>" required></div>
            <div class="col-md-6"><label class="form-label small">Mô tả nhiệm vụ</label><input class="form-control" name="description" value="<?= htmlspecialchars($selectedRole->mo_ta??'') ?>"></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu</button></div>
          </div>
        </form>
        <form method="POST" action="<?= URL_ROOT ?>/admin/roles/assign-permission" class="card-body"><?= Csrf::field() ?><input type="hidden" name="role_id" value="<?= $selectedRole->id ?>">
          <?php if ($selectedRole->id!=1): ?><div class="alert alert-light border small"><i class="fa-solid fa-circle-info text-primary me-2"></i>Để thu hồi quyền, bỏ chọn quyền tương ứng rồi nhấn <strong>Lưu phân quyền</strong>.</div><?php endif; ?>
          <div class="rbac-permission-grid"><?php foreach ($grouped as $module=>$items): ?><div class="border rounded-3 rbac-module"><div class="fw-bold text-uppercase small rbac-module-title"><i class="fa-solid fa-folder-open me-1"></i><?= htmlspecialchars($moduleLabels[$module]??$module) ?><span class="badge bg-primary-subtle text-primary float-end"><?= count($items) ?></span></div><div class="rbac-module-body"><div class="row g-1">
            <?php foreach ($items as $permission): ?><div class="col-6"><label class="permission-option"><input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $permission->id ?>" <?= in_array((int)$permission->id,$selectedPermissions,true)||$selectedRole->id==1?'checked':'' ?> <?= $selectedRole->id==1?'disabled':'' ?>><span><?= htmlspecialchars($actionLabels[$permission->action]??$permission->action) ?></span></label></div><?php endforeach; ?>
          </div></div></div><?php endforeach; ?></div>
          <div class="rbac-actions"><?php if ($selectedRole->id!=1): ?><button class="btn btn-success btn-sm px-4"><i class="fa-solid fa-floppy-disk me-1"></i>Lưu phân quyền</button><?php else: ?><div class="alert alert-info mb-0"><i class="fa-solid fa-circle-info me-2"></i>Quản trị tối cao luôn có toàn bộ quyền.</div><?php endif; ?></div>
        </form>
      </div>
      <?php else: ?><div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="fa-solid fa-arrow-left fs-2 mb-3"></i><div>Chọn một vai trò để xem và gán quyền.</div></div></div><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/app/views/admin/layouts/footer.php'; ?>
