<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ViDienTu extends Model
{
    protected $table = 'nap_tien';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    public function taoYeuCauNap(array $data): bool
    {
        return DB::table('nap_tien')->insert([
            'ma_nguoi_dung' => $data['ma_nguoi_dung'],
            'so_tien' => $data['so_tien'],
            'so_tien_khuyen_mai' => $data['so_tien_khuyen_mai'] ?? 0,
            'tong_cong' => $data['tong_cong'],
            'phuong_thuc' => $data['phuong_thuc'],
            'ma_giao_dich' => $data['ma_giao_dich'],
            'trang_thai' => $data['trang_thai'] ?? 'cho_duyet',
            'ghi_chu' => $data['ghi_chu'] ?? null,
            'ngay_tao' => now(),
        ]);
    }

    public function taoYeuCauNapTraVeId(array $data): int|false
    {
        $id = DB::table('nap_tien')->insertGetId([
            'ma_nguoi_dung' => $data['ma_nguoi_dung'],
            'so_tien' => $data['so_tien'],
            'so_tien_khuyen_mai' => $data['so_tien_khuyen_mai'] ?? 0,
            'tong_cong' => $data['tong_cong'],
            'phuong_thuc' => $data['phuong_thuc'],
            'ma_giao_dich' => $data['ma_giao_dich'],
            'trang_thai' => $data['trang_thai'] ?? 'cho_duyet',
            'ghi_chu' => $data['ghi_chu'] ?? null,
            'ngay_tao' => now(),
        ]);

        return $id ? (int) $id : false;
    }

    public function layChoNap(): array
    {
        return DB::select("
            SELECT n.*, u.ten AS ten_nguoi_dung, u.email
            FROM nap_tien n
            JOIN nguoi_dung u ON n.ma_nguoi_dung = u.id
            WHERE n.trang_thai = 'cho_duyet'
              AND n.phuong_thuc <> 'payos'
            ORDER BY n.ngay_tao DESC
        ");
    }

    public function layYeuCauTheoId(int $id): object|false
    {
        $res = DB::select('SELECT * FROM nap_tien WHERE id = ? LIMIT 1', [$id]);

        return $res ? $res[0] : false;
    }

    public function capNhatTrangThaiNap(int $id, string $trangThai): bool
    {
        return DB::update('UPDATE nap_tien SET trang_thai = ? WHERE id = ?', [$trangThai, $id]) >= 0;
    }

    public function danhDauDaDuyetNeuDangCho(int $id): bool
    {
        $affected = DB::update("
            UPDATE nap_tien
            SET trang_thai = 'da_duyet'
            WHERE id = ? AND trang_thai = 'cho_duyet'
        ", [$id]);

        return $affected === 1;
    }

    public function congSoDu(int $userId, int $soTien): bool
    {
        return DB::update('UPDATE nguoi_dung SET so_du = so_du + ? WHERE id = ?', [$soTien, $userId]) > 0;
    }

    public function truSoDu(int $userId, int $soTien): bool
    {
        $affected = DB::update('
            UPDATE nguoi_dung 
            SET so_du = so_du - ? 
            WHERE id = ? AND so_du >= ?
        ', [$soTien, $userId, $soTien]);

        return $affected > 0;
    }

    public function ghiChiTieu(array $data): bool
    {
        return DB::table('chi_tieu')->insert([
            'ma_nguoi_dung' => $data['ma_nguoi_dung'],
            'ma_du_an' => $data['ma_du_an'] ?? null,
            'loai' => $data['loai'],
            'mo_ta' => $data['mo_ta'],
            'so_tien' => $data['so_tien'],
            'ngay_tao' => now(),
        ]);
    }

    public function layLichSu(int $userId): array
    {
        return DB::select("
            SELECT id, so_tien AS amount, 'nap_tien' AS type, phuong_thuc AS method, trang_thai AS status, ngay_tao
            FROM nap_tien WHERE ma_nguoi_dung = :deposit_uid
            UNION ALL
            SELECT id, so_tien AS amount, loai AS type, mo_ta AS method, 'thanh_cong' AS status, ngay_tao
            FROM chi_tieu WHERE ma_nguoi_dung = :expense_uid
            ORDER BY ngay_tao DESC
        ", [
            'deposit_uid' => $userId,
            'expense_uid' => $userId,
        ]);
    }

    public function daNhanThuongHomNay(int $userId): bool
    {
        $res = DB::selectOne("
            SELECT id FROM chi_tieu 
            WHERE ma_nguoi_dung = ? 
              AND loai = 'thuong_chia_se' 
              AND DATE(ngay_tao) = CURDATE()
            LIMIT 1
        ");

        return (bool) $res;
    }

    /* ── Alias mapping for backwards compatibility ── */
    public function createDeposit(array $data): bool
    {
        return $this->taoYeuCauNap($data);
    }

    public function getPendingDeposits(): array
    {
        return $this->layChoNap();
    }

    public function getDepositById(int $id): mixed
    {
        return $this->layYeuCauTheoId($id);
    }

    public function updateDepositStatus(int $id, string $status): bool
    {
        return $this->capNhatTrangThaiNap($id, $status);
    }

    public function addBalance(int $uid, int $amount): bool
    {
        return $this->congSoDu($uid, $amount);
    }

    public function subtractBalance(int $uid, int $amount): bool
    {
        return $this->truSoDu($uid, $amount);
    }

    public function createTransaction(array $data): bool
    {
        return $this->ghiChiTieu($data);
    }

    public function getUserHistory(int $uid): array
    {
        return $this->layLichSu($uid);
    }

    public function checkShareRewardToday(int $uid): bool
    {
        return $this->daNhanThuongHomNay($uid);
    }
}
