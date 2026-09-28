<?php
/**
 * Entry Point - Điểm vào duy nhất của toàn bộ ứng dụng.
 *
 * Thu tu khoi dong:
 *   1. Cau hinh session an toan (HttpOnly, SameSite, cookie_secure)
 *   2. Khoi dong session
 *   3. Nap config va autoload
 *   4. Khoi tao Router (App)
 */

// ==========================================
// 1. BẢO MẬT SESSION (HTTPONLY + SAMESITE)
// ==========================================
// Cai dat truoc khi goi session_start()

ini_set('session.use_strict_mode',    '1');  // Khong chap nhan Session ID do client tu tao
ini_set('session.use_only_cookies',   '1');  // Chi dung Cookie, khong dung URL (?PHPSESSID=...)
ini_set('session.cookie_httponly',    '1');  // HttpOnly: chan JavaScript doc Cookie (chong XSS)
ini_set('session.cookie_samesite',    'Lax'); // SameSite=Lax: chan CSRF tu cross-site
ini_set('session.cookie_lifetime',    '0');  // Session cookie: tu dong het han khi dong trinh duyet
ini_set('session.gc_maxlifetime',  '7200');  // Session data tren server: het han sau 2 gio khong hoat dong

// Chi bat Secure cookie tren HTTPS (production). Tren localhost XAMPP dung HTTP nen de false.
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.cookie_secure', $isHttps ? '1' : '0');

// ==========================================
// 2. ẨN THÔNG TIN MÔI TRƯỜNG (OWASP A05)
// ==========================================
ini_set('display_errors',         '0'); // Khong hien loi ra browser (production)
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);                 // Van ghi log day du tren server
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/logs/php_error.log');

// ==========================================
// 3. BAO GOM CONFIG VÀ AUTOLOAD
// ==========================================
require_once '../config/config.php';

spl_autoload_register(function (string $className): void {
    // Tên class được giữ ổn định để tương thích code cũ; tên file service dùng
    // tiếng Việt không dấu theo yêu cầu tổ chức mã nguồn của dự án.
    $serviceFiles = [
        'AdminDashboardService'=>'DichVuTongQuanQuanTri','AdminReportService'=>'DichVuBaoCaoQuanTri',
        'AnalyticsService'=>'DichVuPhanTich','ApprovalService'=>'DichVuPheDuyet',
        'AuthorizationService'=>'DichVuPhanQuyen','AuthorService'=>'DichVuTacGia',
        'AuthService'=>'DichVuXacThuc','AutoRenewService'=>'DichVuTuDongGiaHan',
        'CategoryService'=>'DichVuDanhMuc','ChartService'=>'DichVuBieuDo',
        'ChatService'=>'DichVuTroChuyen','CompareService'=>'DichVuSoSanh',
        'ContactService'=>'DichVuLienHe','ConversationService'=>'DichVuHoiThoai',
        'CRMService'=>'DichVuCRM','DashboardService'=>'DichVuTongQuan',
        'ExportService'=>'DichVuXuatDuLieu','FavoriteService'=>'DichVuYeuThich',
        'GeminiChatService'=>'DichVuTroChuyenGemini','ImageService'=>'DichVuHinhAnh',
        'ImportService'=>'DichVuNhapDuLieu','LayoutDataService'=>'DichVuDuLieuBoCuc',
        'LocationService'=>'DichVuViTri','LogMaintenanceService'=>'DichVuBaoTriNhatKy',
        'LogService'=>'DichVuNhatKy','MailService'=>'DichVuThuDienTu',
        'NewsService'=>'DichVuTinTuc','NotificationService'=>'DichVuThongBao',
        'OTPService'=>'DichVuOTP','PostService'=>'DichVuBaiDang',
        'PricingService'=>'DichVuBangGia','ProfileService'=>'DichVuHoSo',
        'ProjectService'=>'DichVuDuAn','PropertyDetailService'=>'DichVuChiTietBatDongSan',
        'PropertyService'=>'DichVuBatDongSan','PushService'=>'DichVuDayThongBao',
        'RateLimiter'=>'GioiHanTanSuat','RealtimeService'=>'DichVuThoiGianThuc',
        'ReportExportService'=>'DichVuXuatBaoCao','ReportPdfService'=>'DichVuBaoCaoPdf',
        'RoleService'=>'DichVuVaiTro','SecurityService'=>'DichVuBaoMat',
        'SEOService'=>'DichVuSEO','SettingService'=>'DichVuCaiDat',
        'SettingUploadService'=>'DichVuTaiCauHinh','StatisticService'=>'DichVuThongKe',
        'SystemBackupService'=>'DichVuSaoLuuHeThong','SystemCacheService'=>'DichVuBoNhoDemHeThong',
        'SystemLogger'=>'GhiNhatKyHeThong','SystemLogPolicy'=>'ChinhSachNhatKyHeThong',
        'SystemLogService'=>'DichVuNhatKyHeThong','TokenService'=>'DichVuMaTruyCap',
        'UploadService'=>'DichVuTaiLen','UserService'=>'DichVuNguoiDung',
        'WalletService'=>'DichVuViDienTu',
    ];
    $paths = [
        APP_ROOT . '/core/'              . $className . '.php',
        APP_ROOT . '/app/controllers/'  . $className . '.php',
        APP_ROOT . '/app/models/'       . $className . '.php',
        APP_ROOT . '/app/services/'     . ($serviceFiles[$className] ?? $className) . '.php',
        APP_ROOT . '/app/repositories/' . $className . '.php',
        APP_ROOT . '/app/middleware/'    . $className . '.php',
        APP_ROOT . '/app/validation/'   . $className . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ==========================================
// 4. KHỞI ĐỘNG SESSION VÀ ROUTER
// ==========================================
session_start();

// Kiểm tra phiên bản xác thực ở đầu mọi request để lệnh "đăng xuất tất cả
// thiết bị" có hiệu lực cả với những controller cũ chỉ đọc session trực tiếp.
if (Session::get('user_id') && !Auth::isLoggedIn()) {
    $_SESSION = [];
}

ErrorLogHandler::register();

set_exception_handler(static function (Throwable $exception): void {
    ErrorLogHandler::exception($exception);
    error_log('[UNCAUGHT] ' . $exception::class . ': ' . $exception->getMessage()
        . ' in ' . $exception->getFile() . ':' . $exception->getLine());
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><html lang="vi"><meta charset="utf-8"><title>Lỗi hệ thống</title>'
       . '<body style="font-family:Arial;text-align:center;padding:80px;background:#f6f7f9">'
       . '<h1>Hệ thống đang gặp sự cố</h1><p>Vui lòng thử lại sau.</p></body></html>';
});

// Bao ve Session Fixation: tao lai Session ID sau khi dang nhap
// (Lop Auth goi Session::regenerate() khi dang nhap thanh cong)

$app = new App();
