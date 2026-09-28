<?php
/**
 * PushService – Mô phỏng đẩy thông báo ra các kênh bên ngoài (FCM, OneSignal, Telegram, Zalo OA).
 * Tuân thủ SOLID, Service Pattern.
 */
class PushService
{
    /**
     * Gửi Firebase Cloud Messaging.
     */
    public function sendFCM(int $userId, string $title, string $content, string $url = ''): bool
    {
        // Ghi log giả lập gửi thành công
        error_log("[PUSH SERVICE] FCM Sent to User #{$userId}: [{$title}] - {$content} (URL: {$url})");
        return true;
    }

    /**
     * Gửi OneSignal Push.
     */
    public function sendOneSignal(int $userId, string $title, string $content, string $url = ''): bool
    {
        error_log("[PUSH SERVICE] OneSignal Sent to User #{$userId}: [{$title}] - {$content} (URL: {$url})");
        return true;
    }

    /**
     * Gửi tin nhắn Telegram Bot.
     */
    public function sendTelegram(int $userId, string $title, string $content): bool
    {
        error_log("[PUSH SERVICE] Telegram Bot Sent to User #{$userId}: [{$title}] - {$content}");
        return true;
    }

    /**
     * Gửi Zalo OA (Zalo Official Account).
     */
    public function sendZaloOA(int $userId, string $title, string $content): bool
    {
        error_log("[PUSH SERVICE] Zalo OA Message Sent to User #{$userId}: [{$title}] - {$content}");
        return true;
    }
}
