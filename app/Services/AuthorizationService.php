<?php

namespace App\Services;

use App\Repositories\RbacRepository;

/** Kiểm tra quyền RBAC và cache kết quả trong vòng đời request. */
class AuthorizationService
{
    private static array $cache = [];

    public function __construct(private ?RbacRepository $repository = null)
    {
        $this->repository ??= new RbacRepository;
    }

    public function can(int $roleId, string $permission): bool
    {
        $cacheKey = $roleId.':'.$permission;
        if (! array_key_exists($cacheKey, self::$cache)) {
            self::$cache[$cacheKey] = $this->repository->has($roleId, $permission);
        }

        return self::$cache[$cacheKey];
    }

    public function require(string $permission): void
    {
        if ($this->can((int) Session::get('user_role_id'), $permission)) {
            return;
        }

        Auth::logSecurityEvent('permission_denied', $permission);
        http_response_code(403);
        exit('Bạn không có quyền thực hiện thao tác này.');
    }
}
