<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class KhachHang extends Model
{
    protected $table = 'khach_hang';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_du_an',
        'nguoi_phu_trach',
        'ten',
        'email',
        'dien_thoai',
        'tin_nhan',
        'trang_thai',
        'da_giai_quyet',
        'bao_cao_giai_quyet',
        'ghi_chu',
    ];

    protected $casts = [
        'da_giai_quyet' => 'boolean',
    ];

    public function duAn(): BelongsTo
    {
        return $this->belongsTo(DuAn::class, 'ma_du_an');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'nguoi_phu_trach');
    }

    /* ── Original Model Compatibility Methods ── */

    public function taoLead(array $data): bool
    {
        return DB::table('khach_hang')->insert([
            'ten' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'dien_thoai' => $data['phone'] ?? null,
            'tin_nhan' => $data['message'] ?? null,
            'ma_du_an' => $data['project_id'] ?? null,
            'ngay_tao' => now(),
            'ngay_cap_nhat' => now(),
        ]);
    }

    public function create(array $data): bool
    {
        return $this->taoLead($data);
    }

    public function layTatCaLeads(): array
    {
        return DB::select('
            SELECT kh.*, da.tieu_de AS tieu_de_du_an, da.duong_dan AS duong_dan_du_an, nd.ten AS ten_nguoi_phu_trach
            FROM khach_hang kh
            LEFT JOIN du_an da ON kh.ma_du_an = da.id
            LEFT JOIN nguoi_dung nd ON kh.nguoi_phu_trach = nd.id
            ORDER BY kh.ngay_tao DESC
        ');
    }

    public function layTheoId(int $id): mixed
    {
        $res = DB::select('
            SELECT kh.*, da.tieu_de AS tieu_de_du_an, da.duong_dan AS duong_dan_du_an, nd.ten AS ten_nguoi_phu_trach
            FROM khach_hang kh
            LEFT JOIN du_an da ON kh.ma_du_an = da.id
            LEFT JOIN nguoi_dung nd ON kh.nguoi_phu_trach = nd.id
            WHERE kh.id = ? LIMIT 1
        ', [$id]);

        return $res ? $res[0] : null;
    }

    public function capNhatLead(array $data): bool
    {
        return DB::table('khach_hang')->where('id', $data['id'])->update([
            'trang_thai' => $data['trang_thai'],
            'nguoi_phu_trach' => ! empty($data['nguoi_phu_trach']) ? (int) $data['nguoi_phu_trach'] : null,
            'ghi_chu' => $data['ghi_chu'] ?? null,
            'da_giai_quyet' => isset($data['da_giai_quyet']) ? $data['da_giai_quyet'] : false,
            'bao_cao_giai_quyet' => $data['bao_cao_giai_quyet'] ?? null,
            'ngay_cap_nhat' => now(),
        ]) >= 0;
    }

    public function capNhatGiaiQuyet(int $id, int $daGiaiQuyet): bool
    {
        return DB::table('khach_hang')->where('id', $id)->update([
            'da_giai_quyet' => $daGiaiQuyet,
            'ngay_cap_nhat' => now(),
        ]) >= 0;
    }

    public function xoaLead(int $id): bool
    {
        return DB::table('khach_hang')->where('id', $id)->delete() > 0;
    }

    public function layThongKe(): array
    {
        $stats = DB::select("
            SELECT 
                COUNT(*) AS tong,
                SUM(CASE WHEN trang_thai = 'moi' THEN 1 ELSE 0 END) AS moi,
                SUM(CASE WHEN trang_thai IN ('da_lien_he', 'tiem_nang') THEN 1 ELSE 0 END) AS dang_cham_soc,
                SUM(CASE WHEN trang_thai = 'thanh_cong' THEN 1 ELSE 0 END) AS thanh_cong,
                SUM(CASE WHEN tin_nhan LIKE '%[ĐẶT LỊCH HẸN TƯ VẤN]%' THEN 1 ELSE 0 END) AS dat_lich,
                SUM(CASE WHEN tin_nhan NOT LIKE '%[ĐẶT LỊCH HẸN TƯ VẤN]%' OR tin_nhan IS NULL THEN 1 ELSE 0 END) AS tuong_tac
            FROM khach_hang
        ");

        $s = $stats[0] ?? null;

        return [
            'tong' => (int) ($s->tong ?? 0),
            'moi' => (int) ($s->moi ?? 0),
            'dang_cham_soc' => (int) ($s->dang_cham_soc ?? 0),
            'thanh_cong' => (int) ($s->thanh_cong ?? 0),
            'dat_lich' => (int) ($s->dat_lich ?? 0),
            'tuong_tac' => (int) ($s->tuong_tac ?? 0),
        ];
    }
}
