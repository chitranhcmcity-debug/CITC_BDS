<?php

/** Domain constants shared by the structured logging module. */
class SystemLog
{
    public const TYPES = ['activity', 'admin', 'login', 'error'];
    public const ERROR_LEVELS = ['info', 'warning', 'error', 'critical'];
    public const LOGIN_STATUSES = ['success', 'failed', 'blocked', 'logout'];

    public static function validType(string $type): bool
    {
        return in_array($type, self::TYPES, true);
    }
}
