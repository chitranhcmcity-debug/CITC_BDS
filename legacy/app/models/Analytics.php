<?php
/**
 * Model Analytics – Đối tượng dữ liệu thống kê tổng hợp cho dashboard.
 * Sử dụng bởi AnalyticsService & AnalyticsRepository.
 * Tuân thủ SOLID, Model Layer.
 */
class Analytics
{
    public int $views = 0;
    public int $calls = 0;
    public int $chats = 0;
    public int $saves = 0;
    public int $shares = 0;
    public int $phone_views = 0;
    public int $zalo_clicks = 0;
    public float $ctr = 0.0;
    public float $conversion_rate = 0.0;

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->views      = (int)($d['views'] ?? $d['luot_xem'] ?? 0);
        $this->calls      = (int)($d['calls'] ?? $d['luot_goi'] ?? 0);
        $this->chats      = (int)($d['chats'] ?? $d['luot_chat'] ?? 0);
        $this->saves      = (int)($d['saves'] ?? $d['luot_luu'] ?? 0);
        $this->shares     = (int)($d['shares'] ?? $d['luot_chia_se'] ?? 0);
        $this->phone_views = (int)($d['phone_views'] ?? 0);
        $this->zalo_clicks = (int)($d['zalo_clicks'] ?? 0);
        $this->ctr             = (float)($d['ctr'] ?? 0);
        $this->conversion_rate = (float)($d['conversion_rate'] ?? 0);
    }
}
