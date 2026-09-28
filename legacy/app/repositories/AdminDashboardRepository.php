<?php

/** Read-only queries for the administration dashboard. */
class AdminDashboardRepository
{
    private Database $db;

    public function __construct() { $this->db = new Database(); }

    public function statistics(): array
    {
        return [
            'users' => $this->one("SELECT COUNT(*) total, SUM(DATE(ngay_tao)=CURDATE()) today, SUM(last_login_at>=DATE_SUB(NOW(),INTERVAL 15 MINUTE)) online FROM nguoi_dung"),
            'posts' => $this->one("SELECT COUNT(*) total, SUM(trang_thai='cho_duyet') pending, SUM(goi_vip>0 AND ngay_het_han_vip>=NOW()) vip, SUM(ngay_het_han<NOW()) expired FROM du_an WHERE deleted_at IS NULL"),
            'money' => $this->one("SELECT COALESCE(SUM(IF(trang_thai='da_duyet' AND DATE(ngay_tao)=CURDATE(),tong_cong,0)),0) today, COALESCE(SUM(IF(trang_thai='da_duyet' AND YEAR(ngay_tao)=YEAR(CURDATE()) AND MONTH(ngay_tao)=MONTH(CURDATE()),tong_cong,0)),0) month, COUNT(*) transactions FROM nap_tien"),
            'chat' => $this->one("SELECT COUNT(*) chats FROM chat_conversations"),
        ];
    }

    public function chart(int $days): array
    {
        $days = max(7, min(365, $days));
        return $this->all("SELECT d.day,
            (SELECT COALESCE(SUM(tong_cong),0) FROM nap_tien WHERE trang_thai='da_duyet' AND DATE(ngay_tao)=d.day) revenue,
            (SELECT COUNT(*) FROM nguoi_dung WHERE DATE(ngay_tao)=d.day) users,
            (SELECT COUNT(*) FROM du_an WHERE DATE(ngay_tao)=d.day) posts,
            (SELECT COUNT(*) FROM post_analytics WHERE DATE(created_at)=d.day AND type='view') views,
            (SELECT COUNT(*) FROM chat_conversations WHERE DATE(created_at)=d.day) chats
          FROM (SELECT DATE(ngay_tao) day FROM nguoi_dung WHERE ngay_tao>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY)
                UNION SELECT DATE(ngay_tao) FROM du_an WHERE ngay_tao>=DATE_SUB(CURDATE(),INTERVAL {$days} DAY)
                UNION SELECT CURDATE()) d ORDER BY d.day");
    }

    public function top(): array
    {
        return [
            'posts' => $this->all('SELECT id,tieu_de,anh_thu_nho,goi_vip,ngay_het_han_vip,luot_xem views,luot_click_sdt phones FROM du_an WHERE deleted_at IS NULL ORDER BY luot_xem DESC LIMIT 5'),
            'authors' => $this->all('SELECT u.id,u.ten,u.email,COUNT(p.id) total_posts FROM nguoi_dung u JOIN du_an p ON p.ma_nguoi_dung=u.id GROUP BY u.id,u.ten,u.email ORDER BY total_posts DESC LIMIT 5'),
            'regions' => $this->all("SELECT tinh_thanh,COUNT(*) total_posts FROM du_an WHERE tinh_thanh IS NOT NULL AND tinh_thanh<>'' GROUP BY tinh_thanh ORDER BY total_posts DESC LIMIT 5"),
            'types' => $this->all("SELECT loai_bat_dong_san,COUNT(*) total_posts FROM du_an WHERE loai_bat_dong_san IS NOT NULL AND loai_bat_dong_san<>'' GROUP BY loai_bat_dong_san ORDER BY total_posts DESC LIMIT 5"),
        ];
    }

    public function pending(): array { return $this->all("SELECT p.id,p.tieu_de,p.duong_dan,p.ngay_tao,c.ten ten_danh_muc,u.ten ten_nguoi_dung,u.anh_dai_dien FROM du_an p LEFT JOIN danh_muc c ON c.id=p.ma_danh_muc LEFT JOIN nguoi_dung u ON u.id=p.ma_nguoi_dung WHERE p.trang_thai='cho_duyet' AND p.deleted_at IS NULL ORDER BY p.ngay_tao DESC LIMIT 5"); }
    public function recentErrors(): array { return $this->all("SELECT id,module,level,message,created_at FROM error_logs WHERE resolved=0 ORDER BY created_at DESC LIMIT 8"); }
    public function recentActivity(): array { return $this->all('SELECT id,user_id,action,model_type,description,created_at FROM system_logs ORDER BY created_at DESC LIMIT 10'); }

    private function one(string $sql): array { $this->db->query($sql); return (array)($this->db->single() ?: []); }
    private function all(string $sql): array { $this->db->query($sql); return $this->db->resultSet() ?: []; }
}
