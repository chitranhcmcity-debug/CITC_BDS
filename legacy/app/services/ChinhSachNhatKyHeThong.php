<?php

/** Granular access policy backed by system_log_permissions. */
class SystemLogPolicy
{
    private array $permissions;

    public function __construct(int $roleId)
    {
        $this->permissions=(new SystemLogRepository())->permissionsForRole($roleId);
    }

    public function can(string $type,string $ability='view'): bool
    {
        return SystemLog::validType($type) && (bool)($this->permissions[$type][$ability]??false);
    }

    public function canAny(): bool
    {
        foreach(SystemLog::TYPES as $type)if($this->can($type))return true;
        return false;
    }

    public function canManageAll(): bool
    {
        foreach(SystemLog::TYPES as $type)if(!$this->can($type,'manage'))return false;
        return true;
    }
}
