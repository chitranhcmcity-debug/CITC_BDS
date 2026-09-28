<?php

namespace App\Services;

use App\Repositories\GalleryRepository;
use App\Repositories\PropertyRepository;

/**
 * PropertyService – Nghiệp vụ quản lý tin đăng bất động sản dành cho Admin.
 * Tuân thủ SOLID, Service Pattern.
 */
class PropertyService
{
    private PropertyRepository $repo;

    public function __construct()
    {
        $this->repo = new PropertyRepository;
    }

    public function getRepo(): PropertyRepository
    {
        return $this->repo;
    }

    public function adminList(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->repo->adminList($filters, $perPage, $offset);
    }

    public function adminCount(array $filters = []): int
    {
        return $this->repo->adminCount($filters);
    }

    public function adminFind(int $id): ?stdClass
    {
        return $this->repo->adminFind($id);
    }

    public function adminUpdate(int $id, array $data): bool
    {
        return $this->repo->adminUpdate($id, $data);
    }

    /** Xoa vinh vien tin dang va tep anh vat ly cua tin. */
    public function hardDelete(int $id): bool
    {
        $post = $this->repo->adminFind($id);
        if (! $post) {
            return false;
        }
        $gallery = new GalleryRepository;
        $files = array_map(static fn ($image) => (string) $image->duong_dan_anh, $gallery->byPost($id));
        if (! empty($post->anh_thu_nho)) {
            $files[] = (string) $post->anh_thu_nho;
        }
        if (! $this->repo->hardDelete($id)) {
            return false;
        }
        $upload = new UploadService;
        foreach (array_unique(array_filter($files)) as $file) {
            $upload->delete($file);
        }

        return true;
    }

    /**
     * Thay đổi trạng thái hiển thị hoặc khóa tin đăng.
     */
    public function toggleStatus(int $id, string $action): bool
    {
        $status = match ($action) {
            'hide' => 'an',
            'show' => 'xuat_ban',
            'lock' => 'khoa',
            'unlock' => 'xuat_ban',
            'delete' => 'xoa',
            default => null
        };

        if ($status === null) {
            return false;
        }

        return $this->repo->updateStatus($id, $status);
    }

    /**
     * Thay đổi gói VIP của tin đăng.
     */
    public function changeVip(int $id, int $vipLevel, int $days): bool
    {
        $expiry = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        return $this->repo->updateVip($id, $vipLevel, $expiry);
    }

    /**
     * Gia hạn thời hạn hiển thị tin đăng.
     */
    public function renew(int $id, int $days): bool
    {
        $expiry = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        return $this->repo->renew($id, $expiry);
    }
}
