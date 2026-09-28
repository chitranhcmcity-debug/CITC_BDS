<?php

/** Tổng hợp và chuẩn hóa dữ liệu Dashboard; cache theo người dùng trong 5 phút. */
class DashboardService
{
    private const CACHE_TTL = 300;
    private DashboardRepository $repository;
    private PostRepository $posts;
    private WalletRepository $wallets;
    private NotificationRepository $notificationRepository;
    private string $cacheDir;

    public function __construct(?DashboardRepository $repository = null)
    {
        $this->repository = $repository ?? new DashboardRepository();
        $this->posts = new PostRepository($this->repository);
        $this->wallets = new WalletRepository($this->repository);
        $this->notificationRepository = new NotificationRepository($this->repository);
        $this->cacheDir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'timnhadat_dashboard';
    }

    public function dashboard(int $userId, int $period = 30): array
    {
        $period = DashboardValidation::period($period);
        $key = "dashboard_{$userId}_{$period}";
        $cached = $this->getCache($key);
        if ($cached !== null) {
            if (is_array($cached['user'] ?? null)) {
                $cached['user'] = (object)$cached['user'];
            }
            $cached['cache_hit'] = true;
            return $cached;
        }

        $user = $this->repository->user($userId);
        if (!$user) {
            throw new RuntimeException('Tài khoản không tồn tại.');
        }

        $data = [
            'user' => $user,
            'period' => $period,
            'statistics' => $this->statistics($userId, false),
            'wallet' => $this->wallet($userId, false),
            'chart' => $this->chart($userId, $period, false),
            'recentPosts' => $this->recent($userId, false),
            'notifications' => $this->notifications($userId, false),
            'transactions' => $this->transactions($userId, false),
            'upPackages' => (new PricingService())->upPackages(),
            'cache_hit' => false,
        ];
        $this->setCache($key, $data);
        return $data;
    }

    public function statistics(int $userId, bool $useCache = true): array
    {
        $key = "statistics_{$userId}";
        if ($useCache && ($cached = $this->getCache($key)) !== null) return $cached;
        $raw = $this->repository->statistics($userId);
        $views = max((int)($raw['legacy_views'] ?? 0), (int)($raw['analytics_views'] ?? 0));
        $data = [
            'total_posts' => (int)($raw['total_posts'] ?? 0),
            'visible_posts' => (int)($raw['visible_posts'] ?? 0),
            'vip_posts' => (int)($raw['vip_posts'] ?? 0),
            'expired_posts' => (int)($raw['expired_posts'] ?? 0),
            'pending_posts' => (int)($raw['pending_posts'] ?? 0),
            'rejected_posts' => (int)($raw['rejected_posts'] ?? 0),
            'views' => $views,
            'saves' => (int)($raw['total_saves'] ?? 0),
            'shares' => (int)($raw['total_shares'] ?? 0),
            'chats' => (int)($raw['total_chats'] ?? 0),
            'calls' => (int)($raw['total_calls'] ?? 0),
        ];
        if ($useCache) $this->setCache($key, $data);
        return $data;
    }

    public function wallet(int $userId, bool $useCache = true): array
    {
        $key = "wallet_{$userId}";
        if ($useCache && ($cached = $this->getCache($key)) !== null) return $cached;
        $raw = $this->wallets->summary($userId);
        $data = [
            'balance'=>(int)($raw['balance'] ?? 0), 'total_deposited'=>(int)($raw['total_deposited'] ?? 0),
            'total_spent'=>(int)($raw['total_spent'] ?? 0), 'up_turns'=>(int)($raw['up_turns'] ?? 0),
        ];
        if ($useCache) $this->setCache($key, $data);
        return $data;
    }

    public function recent(int $userId, bool $useCache = true): array
    {
        $key = "recent_{$userId}";
        if ($useCache && ($cached = $this->getCache($key)) !== null) return $cached;
        $now = time();
        $data = array_map(static function (object $post) use ($now): array {
            $vipExpiry = !empty($post->ngay_het_han_vip) ? strtotime($post->ngay_het_han_vip) : 0;
            return [
                'id'=>(int)$post->id, 'title'=>(string)$post->tieu_de, 'slug'=>(string)$post->duong_dan,
                'image'=>(string)($post->anh_thu_nho ?? ''), 'price'=>(string)($post->gia ?? ''),
                'created_at'=>(string)$post->ngay_tao, 'status'=>(string)$post->trang_thai,
                'status_label'=>self::statusLabel((string)$post->trang_thai),
                'vip_level'=>(int)$post->goi_vip, 'vip_active'=>$vipExpiry > $now,
                'vip_expires_at'=>(string)($post->ngay_het_han_vip ?? ''),
                'expires_at'=>(string)($post->ngay_het_han ?? ''),
                'rejection_reason'=>(string)($post->ly_do_tu_choi ?? ''),
            ];
        }, $this->posts->recentByUser($userId, 10));
        if ($useCache) $this->setCache($key, $data);
        return $data;
    }

    public function notifications(int $userId, bool $useCache = true): array
    {
        $key = "notifications_{$userId}";
        if ($useCache && ($cached = $this->getCache($key)) !== null) return $cached;
        $data = array_map(static fn(object $row): array => [
            'id'=>(int)$row->id, 'title'=>(string)($row->title ?? $row->tieu_de ?? ''),
            'content'=>(string)($row->content ?? $row->noi_dung ?? ''), 'read'=>(bool)($row->is_read ?? $row->da_doc ?? false),
            'created_at'=>(string)($row->created_at ?? $row->ngay_tao ?? ''),
        ], $this->notificationRepository->recentByUser($userId, 10));
        if ($useCache) $this->setCache($key, $data);
        return $data;
    }

    public function transactions(int $userId, bool $useCache = true): array
    {
        $key = "transactions_{$userId}";
        if ($useCache && ($cached = $this->getCache($key)) !== null) return $cached;
        $data = array_map(static fn(object $row): array => [
            'id'=>(int)$row->id, 'type'=>(string)$row->type,
            'type_label'=>self::transactionLabel((string)$row->type),
            'description'=>(string)($row->description ?? ''), 'amount'=>(int)$row->amount,
            'status'=>(string)$row->status, 'status_label'=>self::transactionStatus((string)$row->status),
            'direction'=>(string)$row->direction, 'created_at'=>(string)$row->ngay_tao,
        ], $this->wallets->recentTransactions($userId, 10));
        if ($useCache) $this->setCache($key, $data);
        return $data;
    }

    public function chart(int $userId, int $period, bool $useCache = true): array
    {
        $period = DashboardValidation::period($period);
        $key = "chart_{$userId}_{$period}";
        if ($useCache && ($cached = $this->getCache($key)) !== null) return $cached;
        $lookup = [];
        foreach ($this->repository->chart($userId, $period) as $row) $lookup[$row->day] = (array)$row;
        $data = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $row = $lookup[$day] ?? [];
            $views=(int)($row['views']??0); $calls=(int)($row['calls']??0); $chats=(int)($row['chats']??0);
            $data[]=['date'=>$day,'label'=>date('d/m',strtotime($day)),'views'=>$views,'calls'=>$calls,
                'chats'=>$chats,'saves'=>(int)($row['saves']??0),'shares'=>(int)($row['shares']??0),
                'ctr'=>$views>0?round(($calls+$chats)*100/$views,2):0.0];
        }
        if ($useCache) $this->setCache($key, $data);
        return $data;
    }

    public function hidePost(int $userId, int $postId): bool
    {
        $ok = $postId > 0 && $this->posts->hideOwned($userId, $postId);
        if ($ok) $this->invalidate($userId);
        return $ok;
    }

    public function deletePost(int $userId, int $postId): bool
    {
        $ok = $postId > 0 && $this->posts->deleteOwned($userId, $postId);
        if ($ok) $this->invalidate($userId);
        return $ok;
    }

    public function invalidate(int $userId): void
    {
        foreach (glob($this->cacheDir . DIRECTORY_SEPARATOR . '*_' . $userId . '*.json') ?: [] as $file) @unlink($file);
    }

    private function getCache(string $key): ?array
    {
        $file=$this->cacheFile($key);
        if (!is_file($file) || time()-filemtime($file)>=self::CACHE_TTL) return null;
        $data=json_decode((string)file_get_contents($file),true);
        return is_array($data)?$data:null;
    }

    private function setCache(string $key, array $data): void
    {
        if (!is_dir($this->cacheDir)) @mkdir($this->cacheDir,0750,true);
        @file_put_contents($this->cacheFile($key),json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),LOCK_EX);
    }

    private function cacheFile(string $key): string
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '.json';
    }
    private static function statusLabel(string $status): string { return ['nhap'=>'Bản nháp','cho_duyet'=>'Chờ duyệt','xuat_ban'=>'Đang hiển thị','da_ban'=>'Đã bán','tu_choi'=>'Bị từ chối','an'=>'Đã ẩn'][$status]??'Không xác định'; }
    private static function transactionLabel(string $type): string { return ['nap_tien'=>'Nạp tiền','mua_vip'=>'Mua VIP','mua_up'=>'Mua lượt UP','gia_han_vip'=>'Gia hạn VIP','thuong_chia_se'=>'Thưởng chia sẻ'][$type]??str_replace('_',' ',ucfirst($type)); }
    private static function transactionStatus(string $status): string { return ['cho_duyet'=>'Chờ duyệt','da_duyet'=>'Thành công','tu_choi'=>'Bị từ chối','thanh_cong'=>'Thành công'][$status]??$status; }
}
