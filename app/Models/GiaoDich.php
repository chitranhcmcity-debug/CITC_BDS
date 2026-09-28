<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GiaoDich extends Model
{
    protected $table = 'nap_tien';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = null;

    public function getMonthlyRevenue(int $months = 6): array
    {
        return DB::select("
            SELECT
            DATE_FORMAT(ngay_tao, '%m/%Y') as thang,
            DATE_FORMAT(ngay_tao, '%Y-%m') as thang_sort,
            COUNT(*) as so_giao_dich,
            SUM(so_tien) as tong_nap,
            SUM(tong_cong - so_tien) as tong_khuyen_mai,
            SUM(tong_cong) as tong_vao_tai_khoan
            FROM nap_tien
            WHERE trang_thai = 'da_duyet'
            AND ngay_tao >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%Y-%m'), DATE_FORMAT(ngay_tao, '%m/%Y')
            ORDER BY thang_sort DESC
        ", ['months' => $months]);
    }

    public function getMonthlySpending(int $months = 6): array
    {
        return DB::select("
            SELECT
            DATE_FORMAT(ngay_tao, '%m/%Y') as thang,
            DATE_FORMAT(ngay_tao, '%Y-%m') as thang_sort,
            SUM(CASE WHEN loai = 'mua_vip' THEN so_tien ELSE 0 END) as chi_vip,
            SUM(CASE WHEN loai = 'mua_up'  THEN so_tien ELSE 0 END) as chi_up,
            SUM(so_tien) as tong_chi
            FROM chi_tieu
            WHERE ngay_tao >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%Y-%m'), DATE_FORMAT(ngay_tao, '%m/%Y')
            ORDER BY thang_sort DESC
        ", ['months' => $months]);
    }

    public function getTotalRevenue(): float|int
    {
        $row = DB::selectOne("
            SELECT SUM(so_tien - (tong_cong - so_tien) - (so_tien * 0.1)) as total
            FROM nap_tien WHERE trang_thai = 'da_duyet'
        ");

        return $row ? ($row->total ?? 0) : 0;
    }

    public function getRevenueThisMonth(): float|int
    {
        $row = DB::selectOne("
            SELECT SUM(so_tien - (tong_cong - so_tien) - (so_tien * 0.1)) as total
            FROM nap_tien
            WHERE trang_thai = 'da_duyet'
            AND MONTH(ngay_tao) = MONTH(NOW())
            AND YEAR(ngay_tao)  = YEAR(NOW())
        ");

        return $row ? ($row->total ?? 0) : 0;
    }

    public function countPending(): int
    {
        $row = DB::selectOne("SELECT COUNT(*) as total FROM nap_tien WHERE trang_thai = 'cho_duyet'");

        return $row ? (int) ($row->total ?? 0) : 0;
    }

    public function getTotalSpending(): float|int
    {
        $row = DB::selectOne('SELECT SUM(so_tien) as total FROM chi_tieu');

        return $row ? ($row->total ?? 0) : 0;
    }

    public function getRecentTransactions(int $limit = 10): array
    {
        return DB::select('
            SELECT n.*, u.ten as ten_nguoi_dung, u.email
            FROM nap_tien n
            JOIN nguoi_dung u ON n.ma_nguoi_dung = u.id
            ORDER BY n.ngay_tao DESC
            LIMIT :limit
        ', ['limit' => $limit]);
    }
}
