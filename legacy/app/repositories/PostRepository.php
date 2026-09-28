<?php

/** CRUD tin đăng; mọi thao tác thành viên đều ràng buộc user_id. */
class PostRepository
{
    private Database $db;

    public function __construct(private ?DashboardRepository $dashboard = null)
    {
        $this->db = new Database;
    }

    public function begin(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    public function findOwned(int $id, int $userId): ?object
    {
        $this->db->query('SELECT * FROM du_an WHERE id=:id AND ma_nguoi_dung=:uid AND deleted_at IS NULL LIMIT 1');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $userId, PDO::PARAM_INT);

        return $this->db->single() ?: null;
    }

    public function create(array $d): int|false
    {
        $this->db->query('INSERT INTO du_an(ma_danh_muc,ma_nguoi_dung,tieu_de,duong_dan,mo_ta,gia,gia_so,dien_tich,dien_tich_so,vi_tri,tinh_thanh,quan_huyen,phuong_xa,dia_chi,loai_bat_dong_san,loai_giao_dich,muc_dich_giao_dich,anh_thu_nho,phap_ly,huong_nha,mat_tien,so_phong_ngu,so_phong_wc,link_video,video_file,link_360,vi_do,kinh_do,nguoi_lien_he,so_dien_thoai_lien_he,ten_du_an,noi_that,chieu_rong,chieu_dai,so_tang,nam_xay_dung,trang_thai,goi_vip,ngay_het_han_vip,ngay_het_han,meta_title,meta_description,meta_keywords)
          VALUES(:category,:user,:title,:slug,:description,:price,:price_numeric,:area,:area_numeric,:location,:province,:district,:ward,:address,:property_type,:transaction_type,:purpose,:cover,:legal,:direction,:frontage,:bedrooms,:bathrooms,:video_url,:video_file,:tour360,:lat,:lng,:contact,:phone,:project,:interior,:width,:length,:floors,:year,:status,:vip,:vip_expiry,:expiry,:meta_title,:meta_description,:keywords)');
        $this->bindPost($d);
        if (! $this->db->execute()) {
            return false;
        }

return (int) $this->db->lastInsertId();
    }

    public function updateOwned(int $id, int $userId, array $d): bool
    {
        $this->db->query('UPDATE du_an SET ma_danh_muc=:category,tieu_de=:title,duong_dan=:slug,mo_ta=:description,gia=:price,gia_so=:price_numeric,dien_tich=:area,dien_tich_so=:area_numeric,vi_tri=:location,tinh_thanh=:province,quan_huyen=:district,phuong_xa=:ward,dia_chi=:address,loai_bat_dong_san=:property_type,loai_giao_dich=:transaction_type,muc_dich_giao_dich=:purpose,phap_ly=:legal,huong_nha=:direction,mat_tien=:frontage,so_phong_ngu=:bedrooms,so_phong_wc=:bathrooms,link_video=:video_url,video_file=COALESCE(:video_file,video_file),link_360=:tour360,vi_do=:lat,kinh_do=:lng,nguoi_lien_he=:contact,so_dien_thoai_lien_he=:phone,ten_du_an=:project,noi_that=:interior,chieu_rong=:width,chieu_dai=:length,so_tang=:floors,nam_xay_dung=:year,trang_thai=:status,meta_title=:meta_title,meta_description=:meta_description,meta_keywords=:keywords,ly_do_tu_choi=NULL WHERE id=:id AND ma_nguoi_dung=:user AND deleted_at IS NULL');
        $this->bindPost($d);
        $this->db->bind(':id', $id, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    private function bindPost(array $d): void
    {
        $map = [':category' => 'category_id', ':user' => 'user_id', ':title' => 'title', ':slug' => 'slug', ':description' => 'description', ':price' => 'price', ':price_numeric' => 'price', ':area' => 'area', ':area_numeric' => 'area', ':location' => 'location', ':province' => 'province', ':district' => 'district', ':ward' => 'ward', ':address' => 'address', ':property_type' => 'property_type', ':transaction_type' => 'transaction_type', ':purpose' => 'purpose', ':cover' => 'cover_image', ':legal' => 'legal', ':direction' => 'direction', ':frontage' => 'frontage', ':bedrooms' => 'bedrooms', ':bathrooms' => 'bathrooms', ':video_url' => 'video_url', ':video_file' => 'video_file', ':tour360' => 'tour360', ':lat' => 'latitude', ':lng' => 'longitude', ':contact' => 'contact_name', ':phone' => 'contact_phone', ':project' => 'project_name', ':interior' => 'interior', ':width' => 'width', ':length' => 'length', ':floors' => 'floors', ':year' => 'construction_year', ':status' => 'status', ':vip' => 'vip_level', ':vip_expiry' => 'vip_expires_at', ':expiry' => 'expires_at', ':meta_title' => 'meta_title', ':meta_description' => 'meta_description', ':keywords' => 'meta_keywords'];
        if (($d['mode'] ?? 'create') === 'update') {
            foreach ([':cover', ':vip', ':vip_expiry', ':expiry'] as $key) {
                unset($map[$key]);
            }
        }
        foreach ($map as $param => $key) {
            $this->db->bind($param, $d[$key] ?? null, is_int($d[$key] ?? null) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    public function slugExists(string $slug, ?int $exclude = null): bool
    {
        $sql = 'SELECT 1 FROM du_an WHERE duong_dan=:slug'.($exclude ? ' AND id<>:id' : '').' LIMIT 1';
        $this->db->query($sql);
        $this->db->bind(':slug', $slug);
        if ($exclude) {
            $this->db->bind(':id', $exclude, PDO::PARAM_INT);
        }

return (bool) $this->db->single();
    }

    public function setStatus(int $id, int $uid, string $status): bool
    {
        $this->db->query('UPDATE du_an SET trang_thai=:status WHERE id=:id AND ma_nguoi_dung=:uid AND deleted_at IS NULL');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function softDelete(int $id, int $uid): bool
    {
        $this->db->query("UPDATE du_an SET deleted_at=NOW(),trang_thai='an' WHERE id=:id AND ma_nguoi_dung=:uid AND deleted_at IS NULL");
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function renew(int $id, int $uid, string $expiry): bool
    {
        $this->db->query('UPDATE du_an SET ngay_het_han_vip=:expiry WHERE id=:id AND ma_nguoi_dung=:uid');
        $this->db->bind(':expiry', $expiry);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function toggleAutoRenew(int $id, int $uid, int $status): bool
    {
        $this->db->query('UPDATE du_an SET tu_dong_gia_han_vip=:status WHERE id=:id AND ma_nguoi_dung=:uid AND goi_vip>0');
        $this->db->bind(':status', $status, PDO::PARAM_INT);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function up(int $id, int $uid): bool
    {
        $this->db->query('UPDATE du_an SET ngay_lam_moi=NOW() WHERE id=:id AND ma_nguoi_dung=:uid AND trang_thai=\'xuat_ban\'');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute() && $this->db->rowCount() > 0;
    }

    public function recordUp(int $id, int $uid): bool
    {
        $this->db->query('INSERT INTO post_up_history(post_id,user_id) VALUES(:id,:uid)');
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function setCover(int $id, int $uid, string $file): bool
    {
        $this->db->query('UPDATE du_an SET anh_thu_nho=:file WHERE id=:id AND ma_nguoi_dung=:uid');
        $this->db->bind(':file', $file);
        $this->db->bind(':id', $id, PDO::PARAM_INT);
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return $this->db->execute();
    }

    public function listOwned(int $uid, int $limit = 100): array
    {
        $this->db->query('SELECT * FROM du_an WHERE ma_nguoi_dung=:uid AND deleted_at IS NULL ORDER BY COALESCE(ngay_lam_moi,ngay_tao) DESC LIMIT :lim');
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);
        $this->db->bind(':lim', min(200, max(1, $limit)), PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function listOwnedPage(int $uid, int $limit, int $offset): array
    {
        $this->db->query('SELECT * FROM du_an WHERE ma_nguoi_dung=:uid AND deleted_at IS NULL ORDER BY COALESCE(ngay_lam_moi,ngay_tao) DESC LIMIT :lim OFFSET :off');
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);
        $this->db->bind(':lim', min(100, max(1, $limit)), PDO::PARAM_INT);
        $this->db->bind(':off', max(0, $offset), PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    public function countOwned(int $uid): int
    {
        $this->db->query('SELECT COUNT(*) FROM du_an WHERE ma_nguoi_dung=:uid AND deleted_at IS NULL');
        $this->db->bind(':uid', $uid, PDO::PARAM_INT);

        return (int) $this->db->singleColumn();
    }

    public function recentByUser(int $uid, int $limit = 10): array
    {
        return ($this->dashboard ?? new DashboardRepository)->recentPosts($uid,$limit);
    }

    public function hideOwned(int $uid,int $id): bool
    {
        return $this->setStatus($id,$uid,'an');
    }

    public function deleteOwned(int $uid,int $id): bool
    {
        return $this->softDelete($id,$uid);
    }
}
