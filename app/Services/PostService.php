<?php

namespace App\Services;

use App\Repositories\CategoryRepository;
use App\Repositories\GalleryRepository;
use App\Repositories\PostRepository;
use App\Repositories\WalletRepository;

/** Nghiệp vụ hoàn chỉnh của Module Quản lý tin đăng. */
class PostService
{
    public function __construct(private ?PostRepository $posts = null, private ?CategoryRepository $categories = null, private ?GalleryRepository $gallery = null, private ?WalletRepository $wallet = null, private ?UploadService $upload = null, private ?ImageService $images = null, private ?NotificationService $notifications = null, private ?SEOService $seo = null)
    {
        $this->posts ??= new PostRepository;
        $this->categories ??= new CategoryRepository;
        $this->gallery ??= new GalleryRepository;
        $this->wallet ??= new WalletRepository;
        $this->upload ??= new UploadService;
        $this->images ??= new ImageService($this->gallery, $this->upload);
        $this->notifications ??= new NotificationService;
        $this->seo ??= new SEOService;
    }

    public function formData(int $userId, ?int $id = null): array
    {
        return ['categories' => $this->categories->all(), 'project' => $id ? $this->posts->findOwned($id, $userId) : null, 'images' => $id ? $this->gallery->byPost($id) : [], 'vipPrices' => (new PricingService)->vipDailyPrices(), 'upPackages' => (new PricingService)->upPackages(), 'user' => $this->wallet->user($userId)];
    }

    public function create(int $uid, array $input, bool $draft = false): array
    {
        $d = $this->normalize($uid, $input, $draft, 'create');
        $errors = (new PostValidation)->validate($d, $draft);
        if (! $this->categories->exists($d['category_id'])) {
            $errors['category_id'] = 'Danh mục không hợp lệ.';
        }if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }
        $files = $this->upload->images();
        if (! $draft && ! $files) {
            return ['success' => false, 'errors' => ['images' => 'Vui lòng tải lên ít nhất một ảnh hợp lệ.']];
        }$d['cover_image'] = $files[0] ?? '';
        $d['video_file'] = $this->upload->video();
        $cost = $this->cost($d);
        try {
            $this->posts->begin();
            if ($cost > 0 && ! $this->wallet->debit($uid, $cost)) {
                throw new RuntimeException('Số dư không đủ.');
            }$id = $this->posts->create($d);
            if (! $id) {
                throw new RuntimeException('Không thể tạo tin.');
            }if ($files && ! $this->images->attach($id, $files, (int) ($input['cover_index'] ?? 0))) {
                throw new RuntimeException('Không thể lưu gallery.');
            }if ($cost > 0 && ! $this->wallet->record($uid, $id, 'mua_vip', 'Mua gói VIP khi đăng tin', $cost)) {
                throw new RuntimeException('Không thể ghi giao dịch.');
            }$this->posts->commit();
            $this->notifications->send($uid, $draft ? 'Đã lưu bản nháp' : 'Tin đăng đang chờ duyệt', $draft ? 'Bản nháp đã được lưu.' : 'Tin đăng đã gửi và đang chờ quản trị viên duyệt.');
            (new DashboardService)->invalidate($uid);

            return ['success' => true, 'id' => $id, 'message' => $draft ? 'Đã lưu bản nháp.' : 'Đăng tin thành công, đang chờ duyệt.'];
        } catch (Throwable $e) {
            $this->posts->rollBack();
            foreach ($files as $f) {
                $this->upload->delete($f);
            }error_log('[POST CREATE] '.$e->getMessage());

            return ['success' => false, 'errors' => ['general' => $e->getMessage()]];
        }
    }

    public function update(int $uid, int $id, array $input, bool $draft = false): array
    {
        $post = $this->posts->findOwned($id, $uid);
        if (! $post) {
            return ['success' => false, 'errors' => ['general' => 'Tin đăng không tồn tại hoặc không thuộc tài khoản.']];
        }$d = $this->normalize($uid, $input, $draft, 'update', $id);
        $errors = (new PostValidation)->validate($d, $draft);
        if (! $this->categories->exists($d['category_id'])) {
            $errors['category_id'] = 'Danh mục không hợp lệ.';
        }if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }$files = $this->upload->images();
        $d['video_file'] = $this->upload->video();
        try {
            $this->posts->begin();
            if (! $this->posts->updateOwned($id, $uid, $d)) {
                throw new RuntimeException('Không thể cập nhật tin.');
            }if ($files && (! $this->images->attach($id, $files, count($this->gallery->byPost($id))) || ! $this->posts->setCover($id, $uid, $files[0]))) {
                throw new RuntimeException('Không thể thêm ảnh.');
            }$this->posts->commit();
            $this->notifications->send($uid, $draft ? 'Đã cập nhật bản nháp' : 'Tin đã gửi duyệt lại', $draft ? 'Bản nháp đã cập nhật.' : 'Nội dung thay đổi đang chờ duyệt lại.');
            (new DashboardService)->invalidate($uid);

            return ['success' => true, 'message' => $draft ? 'Đã cập nhật bản nháp.' : 'Cập nhật thành công, tin đang chờ duyệt lại.'];
        } catch (Throwable $e) {
            $this->posts->rollBack();
            foreach ($files as $f) {
                $this->upload->delete($f);
            }

            return ['success' => false, 'errors' => ['general' => $e->getMessage()]];
        }
    }

    public function delete(int $uid, int $id): bool
    {
        $ok = $this->posts->softDelete($id, $uid);
        if ($ok) {
            $this->notifications->send($uid, 'Đã xóa tin đăng', 'Tin đăng đã được chuyển sang trạng thái đã xóa.');
            (new DashboardService)->invalidate($uid);
        }

        return $ok;
    }

    public function status(int $uid, int $id, string $status): bool
    {
        if (! in_array($status, ['an', 'cho_duyet'], true)) {
            return false;
        }$ok = $this->posts->setStatus($id, $uid, $status);
        if ($ok) {
            (new DashboardService)->invalidate($uid);
        }

        return $ok;
    }

    public function renew(int $uid, int $id, int $days): array
    {
        if (! in_array($days, [7, 15, 30, 90], true)) {
            return ['success' => false, 'message' => 'Thời hạn gia hạn không hợp lệ.'];
        }$post = $this->posts->findOwned($id, $uid);
        if (! $post || (int) $post->goi_vip < 1) {
            return ['success' => false, 'message' => 'Tin VIP không tồn tại.'];
        }$cost = (new PricingService)->vipPrice((int) $post->goi_vip, $days, 0);
        $base = ! empty($post->ngay_het_han_vip) && strtotime($post->ngay_het_han_vip) > time() ? $post->ngay_het_han_vip : 'now';
        $expiry = date('Y-m-d H:i:s', strtotime($base." +{$days} days"));
        try {
            $this->posts->begin();
            if ($cost && ! $this->wallet->debit($uid, $cost)) {
                throw new RuntimeException('Số dư không đủ.');
            }if (! $this->posts->renew($id, $uid, $expiry)) {
                throw new RuntimeException('Không thể gia hạn.');
            }if ($cost && ! $this->wallet->record($uid, $id, 'gia_han_vip', "Gia hạn VIP {$days} ngày", $cost)) {
                throw new RuntimeException('Không thể ghi giao dịch.');
            }$this->posts->commit();
            $this->notifications->send($uid, 'Gia hạn thành công', "Tin đăng đã được gia hạn {$days} ngày.");
            (new DashboardService)->invalidate($uid);

            return ['success' => true, 'message' => 'Gia hạn thành công.'];
        } catch (Throwable $e) {
            $this->posts->rollBack();

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function up(int $uid, int $id): array
    {
        $post = $this->posts->findOwned($id, $uid);
        if (! $post || $post->trang_thai !== 'xuat_ban') {
            return ['success' => false, 'message' => 'Chỉ có thể UP tin đang hiển thị.'];
        }try {
            $this->posts->begin();
            if (! $this->wallet->consumeUp($uid)) {
                throw new RuntimeException('Bạn không còn lượt UP.');
            }if (! $this->posts->up($id, $uid) || ! $this->posts->recordUp($id, $uid)) {
                throw new RuntimeException('Không thể UP tin.');
            }$this->posts->commit();
            $this->notifications->send($uid, 'UP tin thành công', "Tin '{$post->tieu_de}' đã được đưa lên đầu danh sách.");
            (new DashboardService)->invalidate($uid);

            return ['success' => true, 'message' => 'UP tin thành công.'];
        } catch (Throwable $e) {
            $this->posts->rollBack();

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function toggleAuto(int $uid, int $id, int $status): bool
    {
        return $this->posts->toggleAutoRenew($id, $uid, $status ? 1 : 0);
    }

    public function list(int $uid): array
    {
        return $this->posts->listOwned($uid);
    }

    private function normalize(int $uid, array $i, bool $draft, string $mode, ?int $exclude = null): array
    {
        $title = trim((string) ($i['title'] ?? ''));
        $description = trim((string) ($i['description'] ?? $i['mo_ta'] ?? ''));
        $transaction = ($i['transaction_type'] ?? '') ?: ((string) ($i['type'] ?? '') === 'Nhà đất cho thuê' ? 'cho_thue' : 'ban');
        $property = trim((string) ($i['property_type'] ?? $i['type'] ?? ''));
        $seo = $this->seo->generate($title, $description, [$property, (string) ($i['province'] ?? '')]);
        $slug = $this->seo->slug($title, fn ($s) => $this->posts->slugExists($s, $exclude));
        $vip = max(0, min(5, (int) ($i['vip_level'] ?? 0)));
        $days = in_array((int) ($i['days'] ?? 0), [7, 15, 30, 90], true) ? (int) $i['days'] : 0;
        $province = trim((string) ($i['province'] ?? ''));
        $district = trim((string) ($i['district'] ?? ''));
        $ward = trim((string) ($i['ward'] ?? ''));
        $address = trim((string) ($i['address'] ?? ''));

        return array_merge($seo, ['mode' => $mode, 'user_id' => $uid, 'title' => $title, 'slug' => $slug, 'description' => $description, 'category_id' => (int) ($i['category'] ?? $i['category_id'] ?? 0), 'property_type' => $property, 'transaction_type' => $transaction, 'price' => $this->price($i['gia'] ?? $i['price'] ?? 0, $i['don_vi_gia'] ?? ''), 'area' => (float) str_replace(',', '.', (string) ($i['dien_tich'] ?? $i['area'] ?? 0)), 'province' => $province, 'district' => $district, 'ward' => $ward, 'address' => $address, 'location' => trim("{$address} {$ward} {$district} {$province}"), 'contact_name' => trim((string) ($i['contact_name'] ?? '')), 'contact_phone' => trim((string) ($i['contact_phone'] ?? '')), 'legal' => $i['phap_ly'] ?? null, 'direction' => $i['huong_nha'] ?? null, 'frontage' => $i['mat_tien'] ?? null, 'bedrooms' => (int) ($i['so_phong_ngu'] ?? 0), 'bathrooms' => (int) ($i['so_phong_wc'] ?? 0), 'video_url' => trim((string) ($i['link_video'] ?? '')), 'tour360' => trim((string) ($i['link_360'] ?? '')), 'latitude' => is_numeric($i['latitude'] ?? null) ? (float) $i['latitude'] : null, 'longitude' => is_numeric($i['longitude'] ?? null) ? (float) $i['longitude'] : null, 'project_name' => trim((string) ($i['project_name'] ?? '')), 'interior' => trim((string) ($i['interior'] ?? '')), 'width' => (float) ($i['width'] ?? 0) ?: null, 'length' => (float) ($i['length'] ?? 0) ?: null, 'floors' => (int) ($i['floors'] ?? 0) ?: null, 'construction_year' => (int) ($i['construction_year'] ?? 0) ?: null, 'status' => $draft ? 'nhap' : 'cho_duyet', 'vip_level' => $vip, 'vip_expires_at' => $vip && $days ? date('Y-m-d H:i:s', strtotime("+{$days} days")) : null, 'expires_at' => date('Y-m-d H:i:s', strtotime('+90 days'))]);
    }

    private function price(mixed $v, string $unit): int
    {
        $n = (float) str_replace(',', '.', preg_replace('/[^0-9,.]/', '', (string) $v) ?? '0');
        $m = match ($unit) {
            'Triệu','Triệu/m2' => 1000000,'Tỷ' => 1000000000,default => 1
        };

        return (int) round($n * $m);
    }

    private function cost(array $d): int
    {
        return (new PricingService)->vipPrice($d['vip_level'], ($d['vip_expires_at'] ? max(1, (int) round((strtotime($d['vip_expires_at']) - time()) / 86400)) : 0), 0);
    }
}
