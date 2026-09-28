<?php

namespace App\Services;

use App\Repositories\AnalyticsRepository;

/**
 * ChartService – Định dạng và cấu trúc dữ liệu chuỗi thời gian, tỷ lệ nguồn gốc của tin đăng cho Chart.js.
 * Tuân thủ SOLID, Service Pattern.
 */
class ChartService
{
    private AnalyticsRepository $repo;

    public function __construct(AnalyticsRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Lấy dữ liệu thống kê hàng ngày.
     */
    public function getDailyData(int $ownerId, string $start, string $end, ?int $postId = null, string $postFilter = ''): array
    {
        $raw = $this->repo->dailySeries($ownerId, $start, $end, $postId, $postFilter);

        $labels = [];
        $views = [];
        $calls = [];
        $chats = [];
        $saves = [];
        $shares = [];

        // Trích xuất mảng để đưa vào các Dataset của Chart.js
        foreach ($raw as $row) {
            $labels[] = date('d/m', strtotime($row->period));
            $views[] = (int) ($row->views ?? 0);
            $calls[] = (int) ($row->calls ?? 0);
            $chats[] = (int) ($row->chats ?? 0);
            $saves[] = (int) ($row->saves ?? 0);
            $shares[] = (int) ($row->shares ?? 0);
        }

        return [
            'labels' => $labels,
            'views' => $views,
            'calls' => $calls,
            'chats' => $chats,
            'saves' => $saves,
            'shares' => $shares,
        ];
    }

    /**
     * Lấy dữ liệu 12 tháng qua.
     */
    public function getMonthlyData(int $ownerId, ?int $postId = null): array
    {
        $raw = $this->repo->monthlyViews($ownerId, $postId);

        $labels = [];
        $views = [];

        foreach ($raw as $row) {
            $labels[] = date('m/Y', strtotime($row->period.'-01'));
            $views[] = (int) ($row->views ?? 0);
        }

        return [
            'labels' => $labels,
            'views' => $views,
        ];
    }

    /**
     * Lấy nguồn truy cập.
     */
    public function getSourcesData(int $ownerId, string $start, string $end, ?int $postId = null): array
    {
        $raw = $this->repo->sources($ownerId, $start, $end, $postId);

        $sourceLabels = [
            'google' => 'Google',
            'facebook' => 'Facebook',
            'zalo' => 'Zalo',
            'telegram' => 'Telegram',
            'direct' => 'Trực tiếp',
            'other' => 'Khác',
        ];

        $labels = [];
        $data = [];

        // Khởi tạo tất cả nguồn
        $sourceValues = [
            'google' => 0,
            'facebook' => 0,
            'zalo' => 0,
            'telegram' => 0,
            'direct' => 0,
            'other' => 0,
        ];

        foreach ($raw as $row) {
            if (isset($sourceValues[$row->source])) {
                $sourceValues[$row->source] = (int) $row->total;
            } else {
                $sourceValues['other'] += (int) $row->total;
            }
        }

        foreach ($sourceValues as $key => $val) {
            $labels[] = $sourceLabels[$key];
            $data[] = $val;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
