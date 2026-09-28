<?php

namespace App\Services;

use App\Repositories\ConversationRepository;
use App\Repositories\UserRepository;

/**
 * ConversationService – Xử lý logic nghiệp vụ liên quan đến quản lý cuộc hội thoại.
 * Tuân thủ SOLID, Service Pattern.
 */
class ConversationService
{
    private ConversationRepository $repo;

    public function __construct()
    {
        $this->repo = new ConversationRepository;
    }

    public function getRepo(): ConversationRepository
    {
        return $this->repo;
    }

    /**
     * Khởi tạo hoặc lấy lại hội thoại hiện có.
     */
    public function getOrCreate(string $type, ?int $customerId, ?string $guestToken, ?int $sellerId = null): int
    {
        $existing = $this->repo->findActive($type, $customerId, $guestToken, $sellerId);
        if ($existing) {
            return (int) $existing->id;
        }

        // Tạo tiêu đề
        $title = 'Hội thoại hỗ trợ';
        if ($type === 'customer_seller' && $sellerId > 0) {
            $userRepo = new UserRepository;
            $seller = $userRepo->findById($sellerId);
            $title = 'Chat với '.($seller ? $seller->ten : 'Người đăng tin');
        }

        $data = [
            'customer_id' => $customerId,
            'customer_guest_token' => $guestToken,
            'seller_id' => $sellerId,
            'staff_id' => null,
            'type' => $type,
            'status' => 'waiting',
            'title' => $title,
        ];

        return $this->repo->create($data);
    }

    /**
     * Đóng cuộc hội thoại.
     */
    public function close(int $id): bool
    {
        return $this->repo->updateStatus($id, 'closed');
    }

    /**
     * Cắt chuyển cuộc hội thoại cho nhân viên khác.
     */
    public function transfer(int $id, int $staffId): bool
    {
        return $this->repo->transfer($id, $staffId);
    }

    /**
     * Ghim cuộc hội thoại.
     */
    public function pin(int $id, bool $isPinned): bool
    {
        return $this->repo->pin($id, $isPinned);
    }
}
