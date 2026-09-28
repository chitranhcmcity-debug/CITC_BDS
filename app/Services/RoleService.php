<?php

namespace App\Services;

use App\Repositories\RbacRepository;

class RoleService
{
    public function __construct(private ?RbacRepository $repo = null)
    {
        $this->repo ??= new RbacRepository;
    }

    public function page(?int $role = null): array
    {
        return ['roles' => $this->repo->roles(), 'permissions' => $this->repo->permissions(), 'selectedRole' => $role ? $this->repo->role($role) : null, 'selectedPermissions' => $role ? $this->repo->permissionIds($role) : []];
    }

    public function save(?int $id, array $input, int $admin): array
    {
        $v = RbacValidation::role($input);
        if ($v['errors']) {
            return ['success' => false, 'message' => implode(' ', $v['errors'])];
        }$ok = $id ? $this->repo->updateRole($id, $v['data']) : (bool) $this->repo->createRole($v['data']);
        if ($ok) {
            SystemLogger::admin($id ? 'update' : 'create', 'rbac', 'role', $id, 'Lưu vai trò', [], $v['data'], $admin);
        }

        return ['success' => $ok, 'message' => $ok ? 'Đã lưu vai trò.' : 'Tên hoặc slug vai trò đã tồn tại.'];
    }

    public function delete(int $id, int $admin): bool
    {
        $role = $this->repo->role($id);
        $ok = $role && $this->repo->deleteRole($id);
        if ($ok) {
            SystemLogger::admin('delete', 'rbac', 'role', $id, 'Xóa vai trò', (array) $role, [], $admin);
        }

        return $ok;
    }

    public function permissions(int $role, array $ids, int $admin): bool
    {
        if ($role === 1) {
            return false;
        }$ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $ok = $this->repo->syncPermissions($role, $ids);
        if ($ok) {
            SystemLogger::admin('assign', 'rbac', 'role_permissions', $role, 'Gán quyền cho vai trò', [], ['permissions' => $ids], $admin);
        }

        return $ok;
    }

    public function assignUser(int $user, int $role, int $admin): bool
    {
        $ok = $this->repo->assignRole($user, $role);
        if ($ok) {
            SystemLogger::admin('assign', 'rbac', 'user_role', $user, 'Gán vai trò người dùng', [], ['role_id' => $role], $admin);
        }

        return $ok;
    }
}
