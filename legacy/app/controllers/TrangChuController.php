<?php

/**
 * Controller Home - Trang chủ của website bất động sản.
 * URL: / (root) hoac /home
 */
class TrangChuController extends Controller
{
    /** @var DuAn Model du an bat dong san */
    private DuAn $projectModel;

    /**
     * Khoi tao DuAn Model dung chung cho tat ca action.
     */
    public function __construct()
    {
        $this->projectModel = $this->model('DuAn');
    }

    // ==========================================
    // TRANG CHỦ
    // ==========================================

    /**
     * Hien thi trang chu voi cac khu vuc:
     *   - Tin noi bat (VIP cao nhat)
     *   - Tin moi nhat
     *   - Tin SEO (nhieu luot xem, noi bat)
     *   - Tin tuc / Bai viet moi nhat
     *
     * URL: GET /
     */
    public function index(): void
    {
        // Lay du lieu du an
        $tinNoiBat = $this->projectModel->layNoiBat(6);
        $tinMoiNhat = $this->projectModel->layMoiNhat(8);
        $tinTopSeo = $this->projectModel->layTopSeo(4);

        // Lay du lieu tin tuc
        $newsModel = $this->model('TinTuc');
        $tinTucNoiBat = $newsModel->layNoiBat(1);
        $tinTucMoi = $newsModel->layDaXuatBan(5);

        // Lay thong ke thuc te tu models
        $userModel = $this->model('NguoiDung');

        // Thong ke thuc te tu model
        $projectStats = $this->projectModel->thongKeTrangChu();
        $tongTinDang = (int) ($projectStats->tong_tin_dang ?? 0);
        $tinMuaBan = (int) ($projectStats->tin_mua_ban ?? 0);
        $tinChoThue = (int) ($projectStats->tin_cho_thue ?? 0);
        $tongThanhVien = $userModel->countAll();
        $tongLuotXem = (int) ($projectStats->tong_luot_xem ?? 0);
        $tinMoiTuan = (int) ($projectStats->tin_moi_tuan ?? 0);
        $bannerImages = $this->projectModel->layBannerImages(7);

        // Dem so tin theo tinh thanh
        $cityRows = $this->projectModel->layThongKeTheoTinhThanh();
        // Chuyen thanh mang [ten_tinh => so_luong] de View lookup bang $cityCounts[$city]
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
            'title' => SITE_NAME.' - Trang chủ',
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
                'cityCounts' => $cityCounts,   // Dem theo tinh thanh cho Link Directory
            ],
        ];

        $this->view('trang-chu/index', $data);
    }

    // ==========================================
    // TRANG BÁO GIÁ
    // ==========================================

    /**
     * Hien thi trang bao gia dich vu dang tin.
     * URL: GET /trang-chu/pricing
     */
    public function pricing(): void
    {
        // Dem so tin theo tinh thanh – dung cho sidebar
        $cityRows = $this->projectModel->layThongKeTheoTinhThanh();

        // Chuyen sang mang [ten_tinh => so_luong] va loc nhung tinh co > 0 tin
        $cityCounts = [];
        foreach ($cityRows as $row) {
            $tinh = trim($row->tinh_thanh ?? '');
            if (! empty($tinh)) {
                $cityCounts[$tinh] = ($cityCounts[$tinh] ?? 0) + (int) $row->cnt;
            }
        }
        // Sap xep giam dan theo so luong tin
        arsort($cityCounts);

        $data = [
            'title' => 'Bảng Giá Dịch Vụ - '.SITE_NAME,
            'cityCounts' => $cityCounts,
            'vipPrices' => (new PricingService)->vipDailyPrices(),
            'upPackages' => (new PricingService)->upPackages(),
            'bonusTiers' => (new PricingService)->bonusTiers(),
        ];
        $this->view('trang-chu/pricing', $data);
    }
}
