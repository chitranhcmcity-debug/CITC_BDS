<?php

namespace App\Services;

use App\Models\CaiDat;
use App\Models\DuAn;
use App\Models\LiveChat;
use App\Models\NguoiDung;
use App\Models\YeuThich;

/** Chuan bi du lieu dung chung cho layout; View khong tu truy van DB. */
class LayoutDataService
{
    public static function forView(string $view): array
    {
        $data = [
            'settings' => CaiDat::getAll(),
            'currentUser' => null,
            'unreadNotifications' => 0,
            'notifications' => [],
            'savedProjectsCount' => 0,
            'pendingProjects' => 0,
            'pendingLiveChats' => 0,
        ];

        try {
            $userId = (int) (Session::get('user_id') ?: 0);
            if ($userId > 0) {
                $userModel = new NguoiDung;
                $data['currentUser'] = $userModel->layTheoId($userId) ?: null;
                $data['unreadNotifications'] = $userModel->demThongBaoChuaDoc($userId);
                $data['notifications'] = array_slice($userModel->layThongBao($userId), 0, 5);
                $data['savedProjectsCount'] = (new YeuThich)->demTheoNguoiDung($userId);
            }

            if (str_starts_with($view, 'admin/') && $view !== 'admin/login' && (int) Session::get('user_role_id') === 1) {
                $data['pendingProjects'] = (new DuAn)->demChoDuyet();
                $stats = (new LiveChat)->stats();
                $data['pendingLiveChats'] = (int) ($stats['waiting_for_admin'] ?? 0);
            }
        } catch (Throwable $exception) {
            error_log('[LAYOUT DATA] '.$exception->getMessage());
        }

        return $data;
    }
}
