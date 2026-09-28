<?php
/**
 * Controller DuAn – Hiển thị danh sách và chi tiết tin đăng BĐS (Frontend).
 *
 * Routes:
 *   GET  /du-an                    → index()
 *   GET  /du-an/search             → search()
 *   GET  /du-an/detail/{slug}      → detail()
 *   GET  /du-an/author/{userId}    → author()
 *   POST /du-an/report/{id}        → report()       [CSRF]
 *   POST /du-an/share/{id}         → share()        [CSRF]
 *   GET  /du-an/api-related/{id}   → apiRelated()   [JSON]
 */
class DuAnController extends Controller
{
    /** @var DuAn Model tin dang BDS */
    private DuAn $projectModel;

    /** @var DanhMuc Model danh muc */
    private DanhMuc $categoryModel;

    /** @var PropertyDetailService Service xu ly trang chi tiet */
    private PropertyDetailService $detailService;

    public function __construct()
    {
        $this->projectModel  = $this->model('DuAn');
        $this->categoryModel = $this->model('DanhMuc');
        $this->detailService = new PropertyDetailService();
    }

    // ==========================================
    // DANH SÁCH TIN ĐĂNG
    // ==========================================

    /**
     * Hien thi trang danh sach tin dang, co the loc theo loai (ban/cho thue).
     * Hien thi so luong tin dang theo tung thanh pho lon.
     *
     * URL: GET /project?type=sale  (mua ban)
     *      GET /project?type=rent  (cho thue)
     */
    public function index(): void
    {
        $loai      = $_GET['type']  ?? 'sale';
        $trangHien = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = 10; // Số tin mỗi trang

        $filters = [
            'loai'  => $loai,
            'limit' => $perPage,
            'trang' => $trangHien,
        ];

        $tinDang    = $this->projectModel->locNangCao($filters);
        $tongSoTin  = $this->projectModel->demTongLocNangCao($filters);
        $tongTrang  = (int)ceil($tongSoTin / $perPage);
        $danhMuc    = $this->categoryModel->layTheoLoai('du_an');

        // Danh sach cac tinh/thanh pho lon de hien thi so luong tin
        $danhSachTp = [
            'Hà Nội', 'Hồ Chí Minh', 'Bình Dương', 'Đồng Nai',
            'Bà Rịa - Vũng Tàu', 'Đà Nẵng', 'Long An', 'Lâm Đồng',
            'Quảng Nam', 'Khánh Hòa', 'Bình Phước', 'Lào Cai',
            'Bình Định', 'Bình Thuận', 'Quảng Ninh', 'Đắk Lắk',
            'Cần Thơ', 'Bắc Ninh', 'Sơn La', 'Hải Phòng',
            'Thanh Hóa', 'Ninh Thuận', 'Kiên Giang', 'Quảng Bình',
        ];
        $soLuongTheoTp = $this->projectModel->demTheoThanhPho($danhSachTp, $loai);

        $data = [
            'title'       => SITE_NAME . ' - Danh sách bất động sản',
            'projects'    => $tinDang,
            'categories'  => $danhMuc,
            'cityCounts'  => $soLuongTheoTp,
            'tongSoTin'   => $tongSoTin,
            'tongTrang'   => $tongTrang,
            'trangHien'   => $trangHien,
            'perPage'     => $perPage,
            'loai'        => $loai,
        ];

        $this->view('du-an/index', $data);
    }

    /**
     * Tim kiem nang cao tu form trang chu va trang danh sach du an.
     * URL: GET /du-an/search
     */
    public function search(): void
    {
        $trangHien = max(1, (int)($_GET['page'] ?? 1));
        $perPage   = 10;
        $filters   = $this->buildSearchFilters($_GET, $trangHien, $perPage);

        $tinDang   = $this->projectModel->locNangCao($filters);
        $tongSoTin = $this->projectModel->demTongLocNangCao($filters);
        $tongTrang = (int)ceil($tongSoTin / $perPage);
        $loai      = $filters['loai'] ?? 'sale';
        $danhMuc   = $this->categoryModel->layTheoLoai('du_an');

        $danhSachTp = [
            'Hà Nội', 'Hồ Chí Minh', 'Bình Dương', 'Đồng Nai',
            'Bà Rịa - Vũng Tàu', 'Đà Nẵng', 'Long An', 'Lâm Đồng',
            'Quảng Nam', 'Khánh Hòa', 'Bình Phước', 'Lào Cai',
            'Bình Định', 'Bình Thuận', 'Quảng Ninh', 'Đắk Lắk',
            'Cần Thơ', 'Bắc Ninh', 'Sơn La', 'Hải Phòng',
            'Thanh Hóa', 'Ninh Thuận', 'Kiên Giang', 'Quảng Bình',
        ];

        $data = [
            'title'       => SITE_NAME . ' - Tìm kiếm bất động sản',
            'projects'    => $tinDang,
            'categories'  => $danhMuc,
            'cityCounts'  => $this->projectModel->demTheoThanhPho($danhSachTp, $loai),
            'tongSoTin'   => $tongSoTin,
            'tongTrang'   => $tongTrang,
            'trangHien'   => $trangHien,
            'perPage'     => $perPage,
            'loai'        => $loai,
            'isSearch'    => true,
        ];

        $this->view('du-an/index', $data);
    }

    private function buildSearchFilters(array $input, int $page, int $perPage): array
    {
        $filters = [
            'loai'      => in_array($input['category'] ?? '', ['sale', 'rent'], true)
                ? $input['category']
                : ($_GET['type'] ?? 'sale'),
            'tu_khoa'  => trim((string)($input['q'] ?? '')),
            'tinh'     => $this->normalizeProvinceName((string)($input['province'] ?? '')),
            'quan'     => trim((string)($input['district'] ?? '')),
            'phuong'   => trim((string)($input['ward'] ?? '')),
            'huong'    => trim((string)($input['direction'] ?? '')),
            'limit'    => $perPage,
            'trang'    => $page,
        ];

        $rooms = (string)($input['rooms'] ?? '');
        if ($rooms === '5+') {
            $filters['phong_ngu_min'] = 5;
        } elseif ($rooms !== '') {
            $filters['phong_ngu'] = (int)$rooms;
        }

        foreach ($this->parseRange((string)($input['area'] ?? ''), 'area') as $key => $value) {
            $filters[$key] = $value;
        }
        foreach ($this->parseRange((string)($input['price'] ?? ''), 'price') as $key => $value) {
            $filters[$key] = $value;
        }

        return array_filter($filters, static fn($value) => $value !== '' && $value !== null);
    }

    private function parseRange(string $value, string $type): array
    {
        if ($value === '' || $value === 'Thỏa thuận') {
            return [];
        }

        if ($type === 'area') {
            return match ($value) {
                'Dưới 30 m²' => ['dt_max' => 30],
                '30 - 50 m²' => ['dt_min' => 30, 'dt_max' => 50],
                '50 - 80 m²' => ['dt_min' => 50, 'dt_max' => 80],
                '80 - 100 m²' => ['dt_min' => 80, 'dt_max' => 100],
                '100 - 150 m²' => ['dt_min' => 100, 'dt_max' => 150],
                '150 - 200 m²' => ['dt_min' => 150, 'dt_max' => 200],
                '200 - 250 m²' => ['dt_min' => 200, 'dt_max' => 250],
                '250 - 300 m²' => ['dt_min' => 250, 'dt_max' => 300],
                '300 - 500 m²' => ['dt_min' => 300, 'dt_max' => 500],
                'Trên 500 m²' => ['dt_min' => 500],
                default => [],
            };
        }

        return match ($value) {
            'Dưới 500 triệu' => ['gia_max' => 500000000],
            '500 - 800 triệu' => ['gia_min' => 500000000, 'gia_max' => 800000000],
            '800 triệu - 1 tỷ' => ['gia_min' => 800000000, 'gia_max' => 1000000000],
            '1 - 2 tỷ' => ['gia_min' => 1000000000, 'gia_max' => 2000000000],
            '2 - 3 tỷ' => ['gia_min' => 2000000000, 'gia_max' => 3000000000],
            '3 - 5 tỷ' => ['gia_min' => 3000000000, 'gia_max' => 5000000000],
            '5 - 7 tỷ' => ['gia_min' => 5000000000, 'gia_max' => 7000000000],
            '7 - 10 tỷ' => ['gia_min' => 7000000000, 'gia_max' => 10000000000],
            '10 - 20 tỷ' => ['gia_min' => 10000000000, 'gia_max' => 20000000000],
            '20 - 30 tỷ' => ['gia_min' => 20000000000, 'gia_max' => 30000000000],
            'Trên 30 tỷ' => ['gia_min' => 30000000000],
            default => [],
        };
    }

    private function normalizeProvinceName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/^(Thành phố|Tỉnh)\s+/u', '', $name) ?? $name;
        return trim($name);
    }

    // ==========================================
    // CHI TIẾT TIN ĐĂNG
    // ==========================================

    /**
     * Hiển thị trang chi tiết tin đăng theo slug URL.
     * Dùng PropertyDetailService để lấy & cache dữ liệu.
     * Ghi nhận lượt xem (tối đa 1 view / 30 phút / visitor).
     *
     * URL: GET /du-an/detail/{slug}
     *
     * @param  string|null $slug URL slug của tin đăng
     */
    public function detail(?string $slug = null): void
    {
        if (!$slug) {
            $this->redirect('du-an');
        }

        // Lấy dữ liệu qua Service (có cache 5 phút)
        $detailData = $this->detailService->getDetail($slug);
        if (!$detailData) {
            $this->redirect('du-an');
        }

        $post = $detailData['post'];

        // Ghi nhận lượt xem (giới hạn 1 lần / 30 phút / IP+session)
        $analyticsService = new AnalyticsService();
        if ($analyticsService->record((int)$post->id, 'view')) {
            $post->luot_xem = ($post->luot_xem ?? 0) + 1;
        }

        // Kiểm tra đã lưu tin chưa
        $isSaved = false;
        if (Session::get('user_id')) {
            $yeuThichModel = $this->model('YeuThich');
            $isSaved = $yeuThichModel->kiemTraDaLuu(
                (int)Session::get('user_id'),
                (int)$post->id
            );
        }

        // Lấy tin liên quan (cache riêng)
        $related = $this->detailService->getRelated($post);

        // Lấy tin cùng người đăng
        $propertyRepo  = new PropertyRepository();
        $sameAuthorPosts = $propertyRepo->findByUser(
            (int)$post->ma_nguoi_dung,
            (int)$post->id,
            6
        );
        $currentUserId=(int)(Session::get('user_id')?:0);
        $ratingSummary=$propertyRepo->ratingSummary((int)$post->id);
        $userRating=$currentUserId?$propertyRepo->userRating((int)$post->id,$currentUserId):0;

        // URL chia sẻ
        $shareUrl = URL_ROOT . '/du-an/detail/' . htmlspecialchars($post->duong_dan, ENT_QUOTES);

        $data = [
            // SEO
            'title'           => SITE_NAME . ' - ' . $post->tieu_de,
            'metaDescription' => mb_substr(strip_tags($post->mo_ta ?? $post->noi_dung ?? ''), 0, 160),
            'canonicalUrl'    => $shareUrl,
            // Dữ liệu chính
            'project'         => $post,
            'images'          => $detailData['images'],
            'amenities'       => $detailData['amenities'],
            'videoEmbed'      => $detailData['videoEmbed'],
            'mapData'         => $detailData['mapData'],
            'breadcrumb'      => $detailData['breadcrumb'],
            // Sidebar
            'isSaved'         => $isSaved,
            'related'         => $related,
            'sameAuthorPosts' => $sameAuthorPosts,
            'shareUrl'        => $shareUrl,
            'ratingSummary'   => $ratingSummary,
            'userRating'      => $userRating,
            // Lý do báo cáo
            'reportReasons'   => [
                'thong_tin_sai' => 'Thông tin không chính xác',
                'hinh_anh_sai'  => 'Hình ảnh sai / không liên quan',
                'gia_sai'       => 'Giá hiển thị sai',
                'lua_dao'       => 'Nghi ngờ lừa đảo',
                'tin_trung_lap' => 'Tin đăng trùng lặp',
                'khac'          => 'Lý do khác',
            ],
        ];

        $this->view('du-an/detail', $data);
    }

    public function rate(?int $id = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify(false)) { http_response_code(403); echo json_encode(['success'=>false,'message'=>'Yêu cầu bảo mật không hợp lệ.']); return; }
        $userId=(int)(Session::get('user_id')?:0);$stars=(int)($_POST['stars']??0);
        if(!$userId){http_response_code(401);echo json_encode(['success'=>false,'message'=>'Bạn cần đăng nhập để đánh giá.']);return;}
        if(!$id||$stars<1||$stars>5){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Số sao không hợp lệ.']);return;}
        $repo=new PropertyRepository();$post=$repo->adminFind($id);
        if(!$post||$post->trang_thai!=='xuat_ban'){http_response_code(404);echo json_encode(['success'=>false,'message'=>'Tin đăng không tồn tại.']);return;}
        if((int)$post->ma_nguoi_dung===$userId){http_response_code(422);echo json_encode(['success'=>false,'message'=>'Bạn không thể tự đánh giá tin của mình.']);return;}
        if(!$repo->saveRating($id,$userId,$stars)){http_response_code(500);echo json_encode(['success'=>false,'message'=>'Không thể lưu đánh giá.']);return;}
        echo json_encode(['success'=>true,'message'=>'Cảm ơn bạn đã đánh giá.','rating'=>$repo->ratingSummary($id)],JSON_UNESCAPED_UNICODE);
    }

    // ==========================================
    // BÁO CÁO VI PHẠM
    // ==========================================

    /**
     * Tiếp nhận báo cáo vi phạm qua AJAX POST.
     * Trả về JSON.
     *
     * URL: POST /du-an/report/{id}
     *
     * @param  int|null $id ID tin đăng
     */
    public function report(?int $id = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
            exit;
        }

        // Kiểm tra CSRF
        if (!Csrf::verify($_POST['_csrf_token'] ?? $_POST['_token'] ?? '')) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Token không hợp lệ. Vui lòng tải lại trang.']);
            exit;
        }

        if (!$id || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Tin đăng không hợp lệ.']);
            exit;
        }

        $ip     = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $result = $this->detailService->submitReport($id, $_POST, $ip);

        echo json_encode($result);
        exit;
    }

    // ==========================================
    // CHIA SẺ
    // ==========================================

    /**
     * Ghi nhận lượt chia sẻ qua AJAX POST.
     * Trả về JSON.
     *
     * URL: POST /du-an/share/{id}
     *
     * @param  int|null $id ID tin đăng
     */
    public function share(?int $id = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false]);
            exit;
        }

        if (!Csrf::verify($_POST['_token'] ?? '')) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'CSRF mismatch.']);
            exit;
        }

        if (!$id || $id <= 0) {
            echo json_encode(['success' => false]);
            exit;
        }

        $platform = preg_replace('/[^a-z]/', '', strtolower($_POST['platform'] ?? 'other'));
        $ok       = $this->detailService->recordShare($id, $platform);

        echo json_encode(['success' => $ok]);
        exit;
    }

    // ==========================================
    // API – TIN LIÊN QUAN
    // ==========================================

    /**
     * Trả về JSON danh sách tin liên quan.
     * Dùng cho lazy-load phía client.
     *
     * URL: GET /du-an/api-related/{id}
     *
     * @param  int|null $id ID tin đăng
     */
    public function apiRelated(?int $id = null): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$id || $id <= 0) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        // Lấy thông tin tin đăng
        $repo = new PropertyRepository();
        if (!$repo->exists($id)) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        // Dùng model để lấy post object (findById từ base Model)
        $post = $this->projectModel->findById($id) ?: null;
        if (!$post) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        $related = $repo->findRelated(
            (int)$post->id,
            (string)($post->loai_bat_dong_san ?? ''),
            (string)($post->tinh_thanh ?? ''),
            12
        );

        // Chỉ trả các field cần thiết (không lộ email, SĐT)
        $data = array_map(static fn($p) => [
            'id'                => $p->id,
            'tieu_de'           => $p->tieu_de,
            'duong_dan'         => $p->duong_dan,
            'gia'               => $p->gia,
            'dien_tich'         => $p->dien_tich,
            'vi_tri'            => $p->vi_tri,
            'anh_thu_nho'       => img_url($p->anh_thu_nho ?? ''),
            'loai_bat_dong_san' => $p->loai_bat_dong_san,
            'ten_danh_muc'      => $p->ten_danh_muc,
            'ngay_tao'          => $p->ngay_tao,
        ], $related);

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    /**
     * Redirect sang AuthorController (backward compat cho URL cũ /du-an/author/{id}).
     * Route mới: /author/{userId}
     *
     * @param int|null $userId
     */
    public function author(?int $userId = null): void
    {
        if (!$userId) {
            $this->redirect('du-an');
        }
        // 301 Redirect sang URL chuẩn mới
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . URL_ROOT . '/author/' . $userId);
        exit;
    }
}
