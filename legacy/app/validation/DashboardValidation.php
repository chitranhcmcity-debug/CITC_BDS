<?php

/** Chuẩn hóa tham số đầu vào của Dashboard. */
class DashboardValidation
{
    public static function period(mixed $value): int
    {
        $period = (int)$value;
        return in_array($period, [7, 30, 90, 365], true) ? $period : 30;
    }

    public static function postId(mixed $value): int
    {
        return max(0, (int)$value);
    }
}
