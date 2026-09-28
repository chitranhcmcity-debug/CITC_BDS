<?php
/**
 * Model DanhMuc - Quản lý danh mục (du_an / bai_viet).
 * Bang CSDL: danh_muc
 */
class DanhMuc extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'danh_muc';
    }

    /**
     * Lay danh sach danh muc dang hoat dong theo loai.
     *
     * @param  string $loai Loai danh muc: 'du_an' hoac 'bai_viet'
     * @return array        Danh sach danh muc
     */
    public function layTheoLoai(string $loai = 'du_an'): array
    {
        $this->db->query("SELECT * FROM danh_muc WHERE loai = :loai AND trang_thai = 'hoat_dong' ORDER BY ten ASC");
        $this->db->bind(':loai', $loai);
        return $this->db->resultSet();
    }

    /**
     * Alias tuong thich nguoc cho layTheoLoai (cac Controller cu van dung duoc).
     *
     * @deprecated Su dung layTheoLoai() thay the
     */
    public function getActiveByType(string $type = 'du_an'): array
    {
        return $this->layTheoLoai($type);
    }

    /**
     * Lay tat ca danh muc trong database (dung cho Admin).
     *
     * @return array Danh sach tat ca danh muc
     */
    public function getAll(): array
    {
        return $this->findAll();
    }

    /**
     * Them moi danh muc.
     *
     * @param  array $data Du lieu them moi
     * @return bool
     */
    public function create(array $data): bool
    {
        $this->db->query("INSERT INTO danh_muc (ten, duong_dan, loai, trang_thai) VALUES (:ten, :duong_dan, :loai, :trang_thai)");
        $this->db->bind(':ten', $data['ten']);
        $this->db->bind(':duong_dan', $data['duong_dan']);
        $this->db->bind(':loai', $data['loai']);
        $this->db->bind(':trang_thai', $data['trang_thai'] ?? 'hoat_dong');
        return $this->db->execute();
    }

    /**
     * Cap nhat danh muc hien co.
     *
     * @param  int   $id   ID danh muc
     * @param  array $data Du lieu cap nhat
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $this->db->query("UPDATE danh_muc SET ten = :ten, duong_dan = :duong_dan, loai = :loai, trang_thai = :trang_thai WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':ten', $data['ten']);
        $this->db->bind(':duong_dan', $data['duong_dan']);
        $this->db->bind(':loai', $data['loai']);
        $this->db->bind(':trang_thai', $data['trang_thai'] ?? 'hoat_dong');
        return $this->db->execute();
    }
}
