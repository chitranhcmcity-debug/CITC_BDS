<?php

namespace App\Http\Controllers;

use App\Models\DuAn;
use App\Models\NguoiDung;
use App\Models\TinTuc;

class HomeController extends Controller
{
    public function index()
    {
        $projectModel = new DuAn;

        $tinNoiBat = $projectModel->layNoiBat(6);
        $tinMoiNhat = $projectModel->layMoiNhat(8);
        $tinTopSeo = $projectModel->layTopSeo(4);

        $tinTucNoiBat = (new TinTuc)->layNoiBat(1);
        $tinTucMoi = (new TinTuc)->layDaXuatBan(5);

        $projectStats = $projectModel->thongKeTrangChu();
        $tongTinDang = (int) ($projectStats->tong_tin_dang ?? 0);
        $tinMuaBan = (int) ($projectStats->tin_mua_ban ?? 0);
        $tinChoThue = (int) ($projectStats->tin_cho_thue ?? 0);
        $tongThanhVien = NguoiDung::count();
        $tongLuotXem = (int) ($projectStats->tong_luot_xem ?? 0);
        $tinMoiTuan = (int) ($projectStats->tin_moi_tuan ?? 0);
        $bannerImages = $projectModel->layBannerImages(7);

        $cityRows = $projectModel->layThongKeTheoTinhThanh();
        $cityCounts = [];
        foreach ($cityRows as $row) {
            foreach (['Hà Nội', 'Hồ Chí Minh', 'Hòa Bình', 'Đà Nẵng', 'Hải Phòng',
                'Bình Dương', 'Khánh Hòa', 'Tuyên Quang', 'Điện Biên',
                'Vĩnh Phúc', 'Bắc Ninh', 'Đồng Nai'] as $city) {
                if (str_contains((string) ($row->tinh_thanh ?? ''), $city)) {
                    $cityCounts[$city] = ($cityCounts[$city] ?? 0) + (int) $row->cnt;
                }
            }
        }

        $data = [
            'title' => 'TimNhaDat.site - Trang chủ',
            'featuredProjects' => $tinNoiBat,
            'latestProjects' => $tinMoiNhat,
            'topSeoProjects' => $tinTopSeo,
            'featuredNews' => $tinTucNoiBat,
            'latestNews' => $tinTucMoi,
            'bannerImages' => $bannerImages,
            'thongKe' => [
                'tongTinDang' => $tongTinDang,
                'tinMuaBan' => $tinMuaBan,
                'tinChoThue' => $tinChoThue,
                'tongThanhVien' => $tongThanhVien,
                'tongLuotXem' => $tongLuotXem,
                'tinMoiTuan' => $tinMoiTuan,
                'cityCounts' => $cityCounts,
            ],
        ];

        return view('trang-chu.index', $data);
    }

    public function pricing()
    {
        $projectModel = new DuAn;
        $cityRows = $projectModel->layThongKeTheoTinhThanh();

        $cityCounts = [];
        foreach ($cityRows as $row) {
            $tinh = trim($row->tinh_thanh ?? '');
            if (! empty($tinh)) {
                $cityCounts[$tinh] = ($cityCounts[$tinh] ?? 0) + (int) $row->cnt;
            }
        }
        arsort($cityCounts);

        $data = [
            'title' => 'Bảng Giá Dịch Vụ - TimNhaDat.site',
            'cityCounts' => $cityCounts,
            'vipPrices' => [
                'vip5' => 5000,
                'vip4' => 10000,
                'vip3' => 20000,
                'vip2' => 30000,
                'vip1' => 50000,
            ],
            'upPackages' => [
                'up60' => 30000,
                'up150' => 50000,
                'up500' => 100000,
                'up750' => 150000,
                'up1500' => 200000,
            ],
            'bonusTiers' => [
                'tier1' => 20,
                'tier2' => 50,
                'tier3' => 100,
                'tier4' => 150,
                'tier5' => 200,
                'tier6' => 250,
            ],
        ];

        return view('trang-chu.pricing', $data);
    }
}
