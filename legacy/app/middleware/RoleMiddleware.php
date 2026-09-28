<?php

/** Kiểm tra vai trò cho route được bảo vệ. */
class RoleMiddleware
{
    public static function handle(int|array $roles): void
    {
        Auth::requireRole($roles);
    }
}
