<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DuAn;
use App\Models\KhachHang;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $projectModel = new DuAn;

        $chartCategories = $projectModel->thongKeTheoDanhMuc();
        $chartMonths = $projectModel->thongKeTheoThang();
        $pendingPosts = $projectModel->layTinChoDuyetMoiNhat(5);

        // 1. Top bài đăng xem nhiều nhất
        $topPosts = DB::select('
            SELECT p.id, p.tieu_de, p.anh_thu_nho, p.goi_vip, p.ngay_het_han_vip,
                   COALESCE(p.luot_xem, 0) as views,
                   COALESCE(p.luot_click_sdt, 0) as phones
            FROM du_an p
            ORDER BY p.luot_xem DESC, p.id DESC
            LIMIT 5
        ');

        // 2. Top người đăng tin nhiều nhất
        $topAuthors = DB::select('
            SELECT u.id, u.ten, u.email, COUNT(p.id) as total_posts
            FROM nguoi_dung u
            JOIN du_an p ON p.ma_nguoi_dung = u.id
            GROUP BY u.id, u.ten, u.email
            ORDER BY total_posts DESC
            LIMIT 5
        ');

        // 3. Top khu vực (tỉnh thành)
        $topRegions = DB::select("
            SELECT tinh_thanh, COUNT(id) as total_posts
            FROM du_an
            WHERE tinh_thanh IS NOT NULL AND tinh_thanh <> ''
            GROUP BY tinh_thanh
            ORDER BY total_posts DESC
            LIMIT 5
        ");

        // 4. Top loại bất động sản
        $topTypes = DB::select("
            SELECT loai_bat_dong_san, COUNT(id) as total_posts
            FROM du_an
            WHERE loai_bat_dong_san IS NOT NULL AND loai_bat_dong_san <> ''
            GROUP BY loai_bat_dong_san
            ORDER BY total_posts DESC
            LIMIT 5
        ");

        // 5. Biểu đồ doanh thu từ nap_tien (thông qua PayOS/chuyển khoản đã duyệt)
        $revenueChart = DB::select("
            SELECT DATE_FORMAT(ngay_tao, '%m/%Y') as period, SUM(so_tien) as total_revenue
            FROM nap_tien
            WHERE trang_thai = 'da_duyet' AND ngay_tao >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
            GROUP BY DATE_FORMAT(ngay_tao, '%m/%Y'), DATE_FORMAT(ngay_tao, '%Y-%m')
            ORDER BY DATE_FORMAT(ngay_tao, '%Y-%m') ASC
        ");

        // 6. Biểu đồ View, Chat, Call toàn trang
        $interactionsChart = DB::select("
            SELECT DATE_FORMAT(created_at, '%m/%Y') as period,
                   SUM(CASE WHEN type = 'view' THEN 1 ELSE 0 END) as total_views,
                   SUM(CASE WHEN type = 'chat' THEN 1 ELSE 0 END) as total_chats,
                   SUM(CASE WHEN type = 'call' THEN 1 ELSE 0 END) as total_calls
            FROM post_analytics
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%m/%Y'), DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
        ");

        $data = [
            'title' => 'Admin Dashboard - TimNhaDat.site',
            'total_projects' => DuAn::count(),
            'active_projects' => $projectModel->demDangHoatDong(),
            'total_users' => NguoiDung::count(),
            'total_leads' => KhachHang::count(),
            'chart_categories' => $chartCategories,
            'chart_months' => $chartMonths,
            'pending_posts' => $pendingPosts,
            'top_posts' => $topPosts,
            'top_authors' => $topAuthors,
            'top_regions' => $topRegions,
            'top_types' => $topTypes,
            'revenue_chart' => $revenueChart,
            'interactions_chart' => $interactionsChart,
        ];

        return view('admin.dashboard', $data);
    }
}
