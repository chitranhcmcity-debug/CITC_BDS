<?php
/**
 * Model Favorite – Đối tượng dữ liệu tin yêu thích.
 * Sử dụng bởi FavoriteService & FavoriteRepository.
 * Tuân thủ SOLID, Model Layer.
 */
class Favorite
{
    public int $id = 0;
    public int $user_id = 0;
    public int $post_id = 0;
    public string $created_at = '';

    public function __construct(array|object $data = [])
    {
        if (empty($data)) return;
        $d = is_object($data) ? get_object_vars($data) : $data;

        $this->id         = (int)($d['id'] ?? 0);
        $this->user_id    = (int)($d['user_id'] ?? $d['ma_nguoi_dung'] ?? 0);
        $this->post_id    = (int)($d['post_id'] ?? $d['ma_du_an'] ?? 0);
        $this->created_at = (string)($d['created_at'] ?? $d['ngay_tao'] ?? '');
    }
}
