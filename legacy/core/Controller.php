<?php
/**
 * Lớp Controller gốc (Base Controller) - Cha của tất cả Controller trong hệ thống.
 * Cung cấp 3 phương thức cốt lõi của MVC: nạp Model, render View, chuyển hướng.
 *
 * Luong xu ly MVC:
 *   Request --> App (Router) --> Controller --> Model/View --> Response
 */
class Controller
{
    // ==========================================
    // NẠP MODEL
    // ==========================================

    /**
     * Nap (require) file Model va tra ve doi tuong Model tuong ung.
     * Su dung trong Controller de tuong tac voi co so du lieu.
     *
     * Vi du: $userModel = $this->model('NguoiDung');
     *
     * @param  string $model Ten lop Model (vi du: 'NguoiDung', 'DuAn')
     * @return object         Doi tuong Model da duoc khoi tao
     */
    protected function model(string $model): object
    {
        require_once "../app/models/{$model}.php";
        return new $model();
    }

    // ==========================================
    // RENDER VIEW
    // ==========================================

    /**
     * Tai va render file View, truyen du lieu tu Controller xuong.
     * Bien du lieu duoc extract() ra thanh bien PHP trong View.
     *
     * Vi du: $this->view('trang-chu/index', ['title' => 'Trang chu']);
     *
     * @param string $view Duong dan View (tinh tu /app/views/, khong can .php)
     * @param array  $data Du lieu can truyen xuong View
     */
    protected function view(string $view, array $data = []): void
    {
        $viewFile = "../app/views/{$view}.php";

        if (file_exists($viewFile)) {
            $data['layout'] = array_replace_recursive(
                LayoutDataService::forView($view),
                $data['layout'] ?? []
            );
            // Chuyen mang $data thanh cac bien rieng le de dung trong View
            extract($data);
            require_once $viewFile;
        } else {
            die("Lỗi: Không tìm thấy View '{$view}'");
        }
    }

    // ==========================================
    // CHUYỂN HƯỚNG
    // ==========================================

    /**
     * Chuyen huong (redirect) nguoi dung den URL khac.
     * Tu dong them URL_ROOT phia truoc.
     *
     * Vi du: $this->redirect('admin/dashboard');
     *        => http://localhost/CITC_BDS/admin/dashboard
     *
     * @param string $url Duong dan tuong doi (khong can URL_ROOT va dau /)
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . URL_ROOT . '/' . ltrim($url, '/'));
        exit;
    }

    // ==========================================
    // TIỆN ÍCH CHUNG
    // ==========================================

    /**
     * Chuyển đổi chuỗi tiếng Việt có dấu thành slug URL thân thiện SEO.
     * Dùng chung cho mọi Controller cần tạo slug (danh mục, tin tức...).
     *
     * Ví dụ: "Tin Tức Bất Động Sản" => "tin-tuc-bat-dong-san"
     *
     * @param  string $str Chuỗi tiếng Việt cần chuyển đổi
     * @return string      Slug đã được chuẩn hoá (chữ thường, không dấu, dấu gạch ngang)
     */
    protected function createSlug(string $str): string
    {
        $map = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];

        foreach ($map as $latin => $unicode) {
            $str = preg_replace("/($unicode)/i", $latin, $str);
        }

        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', '-', $str);
        return trim($str, '-');
    }
}
