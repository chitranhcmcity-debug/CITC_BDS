<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleController extends Controller
{
    private const TITLES = [
        'tin-tuc' => 'Tin tức',
        'contact' => 'Yêu cầu tư vấn (CRM)',
        'wallet' => 'Ví & Giao dịch CRM',
        'live-chat' => 'Live Chat',
        'nguoi-dung' => 'Người dùng',
        'bang-gia' => 'Bảng giá',
        'bao-cao' => 'Báo cáo',
        'cai-dat' => 'Cài đặt',
        'roles' => 'Phân quyền & Vai trò',
        'system-log' => 'Nhật ký hệ thống',
    ];

    private const MODULES = [
        'tin-tuc' => ['bai_viet', ['id', 'tieu_de', 'trang_thai', 'luot_xem', 'noi_bat', 'ngay_tao']],
        'contact' => ['khach_hang', ['id', 'ten', 'email', 'dien_thoai', 'trang_thai', 'nguoi_phu_trach', 'ngay_tao']],
        'wallet' => ['nap_tien', ['id', 'ma_nguoi_dung', 'so_tien', 'tong_cong', 'phuong_thuc', 'ma_giao_dich', 'trang_thai', 'ngay_tao']],
        'live-chat' => ['hoi_thoai_truc_tuyen', ['id', 'customer_name', 'customer_email', 'subject', 'status', 'priority', 'assigned_staff_id', 'last_message_at']],
        'nguoi-dung' => ['nguoi_dung', ['id', 'ten', 'email', 'dien_thoai', 'ma_vai_tro', 'loai_tai_khoan', 'so_du', 'trang_thai', 'ngay_tao']],
        'bang-gia' => ['goi_up_tin', ['id', 'name', 'price', 'up_count', 'bonus_up', 'valid_days', 'is_active', 'created_at']],
        'cai-dat' => ['cai_dat', ['id', 'khoa_cai_dat', 'gia_tri_cai_dat']],
        'roles' => ['vai_tro', ['id', 'ten', 'slug', 'mo_ta', 'is_system']],
        'system-log' => ['nhat_ky_hoat_dong', ['id', 'user_id', 'action', 'module', 'description', 'ip_address', 'created_at']],
    ];

    public function show(Request $request, string $module)
    {
        abort_unless(isset(self::TITLES[$module]), 404);

        if ($module === 'bao-cao') {
            return $this->report();
        }

        [$table, $wantedColumns] = self::MODULES[$module];
        abort_unless(Schema::hasTable($table), 503, "Thiếu bảng dữ liệu {$table}");

        $columns = array_values(array_intersect($wantedColumns, Schema::getColumnListing($table)));
        $search = trim((string) $request->query('search', ''));
        $query = DB::table($table);

        if ($search !== '') {
            $query->where(function ($builder) use ($columns, $search) {
                foreach ($columns as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $orderColumn = in_array('id', $columns, true) ? 'id' : $columns[0];

        return view('admin.module-index', [
            'title' => self::TITLES[$module],
            'moduleTitle' => self::TITLES[$module],
            'module' => $module,
            'columns' => $columns,
            'rows' => $query->orderByDesc($orderColumn)->paginate(20)->withQueryString(),
            'total' => DB::table($table)->count(),
            'search' => $search,
        ]);
    }

    private function report()
    {
        $stats = [
            'Người dùng' => DB::table('nguoi_dung')->count(),
            'Tin đăng' => DB::table('du_an')->count(),
            'Tin tức' => DB::table('bai_viet')->count(),
            'Yêu cầu tư vấn' => DB::table('khach_hang')->count(),
            'Cuộc trò chuyện' => DB::table('hoi_thoai_truc_tuyen')->count(),
            'Tổng tiền nạp' => (float) DB::table('nap_tien')->where('trang_thai', 'da_duyet')->sum('so_tien'),
        ];

        return view('admin.report-summary', ['title' => self::TITLES['bao-cao'], 'stats' => $stats]);
    }
}
