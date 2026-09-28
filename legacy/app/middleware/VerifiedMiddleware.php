<?php

/** Chỉ cho phép tài khoản đã xác thực email. */
class VerifiedMiddleware
{
    public static function handle(): void
    {
        AuthMiddleware::handle(true);
    }
}
