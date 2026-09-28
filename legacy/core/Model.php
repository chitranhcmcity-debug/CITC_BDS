<?php
/**
 * Lớp Model gốc (Base Model) - Cha của tất cả các Model trong hệ thống.
 * Cung cấp kết nối database và các phương thức CRUD cơ bản tái sử dụng được.
 *
 * Cach hoat dong MVC:
 *   Controller --> goi Model --> Model tuong tac Database --> tra ket qua ve Controller
 */
class Model
{
    /** @var Database Doi tuong ket noi co so du lieu */
    protected Database $db;

    /** @var string Ten bang trong CSDL (cac lop con phai khai bao) */
    protected string $table = '';

    // ==========================================
    // KHỞI TẠO
    // ==========================================

    /**
     * Khoi tao doi tuong Database khi tao Model.
     * Cac lop con ke thua va goi parent::__construct() de co ket noi.
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    // ==========================================
    // CÁC PHƯƠNG THỨC CRUD CHUNG
    // ==========================================

    /**
     * Lay tat ca ban ghi trong bang, sap xep giam dan theo ID.
     *
     * @return array Danh sach tat ca ban ghi
     */
    public function findAll(): array
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $this->db->resultSet();
    }

    /**
     * Tim ban ghi theo ID.
     *
     * @param  int          $id ID can tim kiem
     * @return object|false Doi tuong ban ghi hoac false neu khong tim thay
     */
    public function findById(int $id): object|false
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Xoa ban ghi theo ID.
     *
     * @param  int  $id ID can xoa
     * @return bool True neu xoa thanh cong
     */
    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Dem tong so ban ghi trong bang.
     *
     * @return int Tong so ban ghi
     */
    public function countAll(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $row = $this->db->single();
        return (int)($row->total ?? 0);
    }

    // ==========================================
    // DATABASE TRANSACTIONS DELEGATE PROXIES
    // ==========================================

    /**
     * Bắt đầu một giao dịch (transaction).
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Xác nhận lưu (commit) giao dịch.
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Hoàn tác (rollback) giao dịch.
     */
    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->db->inTransaction();
    }
}
