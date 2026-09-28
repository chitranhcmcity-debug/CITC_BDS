<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class YeuThich extends Model
{
    protected $table = 'yeu_thich';

    public function kiemTraDaLuu(int $maNguoiDung, int $maDuAn): bool
    {
        return DB::selectOne('SELECT 1 FROM yeu_thich WHERE ma_nguoi_dung = :ma_nguoi_dung AND ma_du_an = :ma_du_an', [
            'ma_nguoi_dung' => $maNguoiDung,
            'ma_du_an' => $maDuAn,
        ]) !== null;
    }

    public function luuTin(int $maNguoiDung, int $maDuAn): bool
    {
        return DB::insert('INSERT INTO yeu_thich (ma_nguoi_dung, ma_du_an) VALUES (:ma_nguoi_dung, :ma_du_an)', [
            'ma_nguoi_dung' => $maNguoiDung,
            'ma_du_an' => $maDuAn,
        ]);
    }

    public function boLuuTin(int $maNguoiDung, int $maDuAn): bool
    {
        return DB::delete('DELETE FROM yeu_thich WHERE ma_nguoi_dung = :ma_nguoi_dung AND ma_du_an = :ma_du_an', [
            'ma_nguoi_dung' => $maNguoiDung,
            'ma_du_an' => $maDuAn,
        ]) > 0;
    }

    public function layDanhSachDaLuu(int $maNguoiDung): array
    {
        return DB::select('SELECT p.*, c.ten AS ten_danh_muc, u.ten AS ten_nguoi_dung, y.ngay_tao as ngay_luu
                           FROM yeu_thich y
                           JOIN du_an p ON y.ma_du_an = p.id
                           JOIN danh_muc c ON p.ma_danh_muc = c.id
                           LEFT JOIN nguoi_dung u ON p.ma_nguoi_dung = u.id
                           WHERE y.ma_nguoi_dung = :ma_nguoi_dung
                           ORDER BY y.ngay_tao DESC', [
            'ma_nguoi_dung' => $maNguoiDung,
        ]);
    }

    public function demTheoNguoiDung(int $maNguoiDung): int
    {
        $res = DB::selectOne('SELECT COUNT(*) AS total FROM yeu_thich WHERE ma_nguoi_dung = :uid', [
            'uid' => $maNguoiDung,
        ]);

        return (int) ($res->total ?? 0);
    }
}
