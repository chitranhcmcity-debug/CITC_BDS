<?php
/**
 * Controller AdminNews - Quản lý bài viết / tin tức (khu vực Admin).
 * URL: /admin/tin-tuc/{method}/{params}
 *
 * Quyen truy cap: Admin (role_id = 1) hoac Editor (role_id = 2)
 */
class AdminTinTucController extends Controller
{
    /** @var TinTuc Model quan ly bai viet */
    protected TinTuc $newsModel;

    /** @var DanhMuc Model quan ly danh muc */
    protected DanhMuc $categoryModel;

    // ==========================================
    // KHỞI TẠO
    // ==========================================

    /**
     * Kiem tra quyen truy cap va khoi tao cac Model can thiet.
     * Chi Admin (1) va Editor (2) moi co quyen.
     */
    public function __construct()
    {
        // Kiem tra dang nhap va phan quyen (su dung Session helper, nhat quan voi toan bo codebase)
        $userId = Session::get('user_id');
        $roleId = (int)Session::get('user_role_id');
        if (!$userId || $roleId !== 1) {
            $this->redirect('nguoi-dung/dang-nhap');
        }

        $this->newsModel     = $this->model('TinTuc');
        $this->categoryModel = $this->model('DanhMuc');
    }

    // ==========================================
    // DANH SÁCH BÀI VIẾT
    // ==========================================

    /**
     * Hien thi danh sach tat ca bai viet.
     * URL: GET /admin/tin-tuc
     */
    public function index(): void
    {
        $categories = $this->categoryModel->layTheoLoai('bai_viet');
        $validCategoryIds = array_map(static fn($category) => (string)$category->id, $categories);
        $requestedCategory = (string)($_GET['category_id'] ?? '');

        $filters = [
            'keyword'     => trim($_GET['keyword'] ?? ''),
            'category_id' => in_array($requestedCategory, $validCategoryIds, true) ? $requestedCategory : '',
            'status'      => in_array(($_GET['status'] ?? ''), ['nhap', 'xuat_ban'], true) ? $_GET['status'] : '',
            'featured'    => in_array(($_GET['featured'] ?? ''), ['0', '1'], true) ? $_GET['featured'] : '',
        ];

        $this->view('admin/tin-tuc/index', [
            'newsList'   => $this->newsModel->locAdmin($filters),
            'categories' => $categories,
            'filters'    => $filters,
        ]);
    }

    public function comments(): void
    {
        $status = (string)($_GET['status'] ?? '');
        if (!in_array($status, ['', 'hien', 'an'], true)) $status = '';
        $repo = new NewsCommentRepository();
        $this->view('admin/tin-tuc/comments', ['comments' => $repo->findForAdmin($status), 'status' => $status]);
    }

    public function hideComment(int $id): void { $this->changeCommentVisibility($id, false); }
    public function unhideComment(int $id): void { $this->changeCommentVisibility($id, true); }

    public function deleteComment(int $id): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') $this->redirect('admin/tin-tuc/comments');
        Csrf::verify();
        $repo = new NewsCommentRepository();
        $comment = $repo->findById($id);
        if (!$comment) {
            Session::flash('admin_msg', 'Bình luận không tồn tại.');
        } elseif ($repo->delete($id)) {
            (new NewsRepository())->decrementComment((int)$comment->bai_viet_id);
            SystemLogger::admin('delete', 'tin_tuc', 'news_comments', $id, "Xóa bình luận #{$id}", (array)$comment, [], (int)Session::get('user_id'));
            Session::flash('admin_msg', 'Đã xóa bình luận.');
        } else Session::flash('admin_msg', 'Không thể xóa bình luận.');
        $this->redirect('admin/tin-tuc/comments');
    }

    private function changeCommentVisibility(int $id, bool $visible): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') $this->redirect('admin/tin-tuc/comments');
        Csrf::verify();
        $repo = new NewsCommentRepository();
        $comment = $repo->findById($id);
        $ok = $comment && ($visible ? $repo->unhide($id) : $repo->hide($id));
        Session::flash('admin_msg', $ok ? ($visible ? 'Đã hiện lại bình luận.' : 'Đã ẩn bình luận.') : 'Không tìm thấy hoặc không thể cập nhật bình luận.');
        $this->redirect('admin/tin-tuc/comments');
    }

    // ==========================================
    // TẠO BÀI VIẾT MỚI
    // ==========================================

    /**
     * Hien thi form tao bai viet (GET) va xu ly luu bai viet moi (POST).
     * URL: GET /admin/tin-tuc/create  => hien thi form
     *      POST /admin/tin-tuc/create => luu bai viet
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $data = $this->chuanBiDuLieuBaiViet();
            $data['ma_nguoi_dung'] = (int)Session::get('user_id');

            // Xu ly upload anh dai dien neu co
            $data['anh_thu_nho'] = $this->uploadAnh('anh_thu_nho', 'uploads/tin-tuc/');

            if ($this->newsModel->them($data)) {
                Session::flash('admin_msg', 'Đã thêm bài viết thành công!');
                $this->redirect('admin/tin-tuc');
            } else {
                Session::flash('admin_msg', 'Lỗi: Không thể lưu bài viết. Vui lòng thử lại!', 'alert alert-danger');
                $this->redirect('admin/tin-tuc/create');
            }
        } else {
            // Hien thi form tao moi
            $danhMuc = $this->categoryModel->layTheoLoai('bai_viet');
            $this->view('admin/tin-tuc/create', ['categories' => $danhMuc]);
        }
    }

    // ==========================================
    // SỬA BÀI VIẾT
    // ==========================================

    /**
     * Hien thi form sua bai viet (GET) va xu ly cap nhat (POST).
     * URL: GET /admin/tin-tuc/edit/{id}  => hien thi form
     *      POST /admin/tin-tuc/edit/{id} => cap nhat bai viet
     *
     * @param int $id ID bai viet can sua
     */
    public function edit(int $id): void
    {
        $baiViet = $this->newsModel->findById($id);
        if (!$baiViet) {
            $this->redirect('admin/tin-tuc');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            $data       = $this->chuanBiDuLieuBaiViet();
            $data['id'] = $id;

            // Giu anh cu neu khong upload anh moi
            $anhMoi = $this->uploadAnh('anh_thu_nho', 'uploads/tin-tuc/');
            $data['anh_thu_nho'] = $anhMoi ?: $baiViet->anh_thu_nho;

            if ($this->newsModel->capNhat($data)) {
                Session::flash('admin_msg', 'Cập nhật bài viết thành công!');
                $this->redirect('admin/tin-tuc');
            } else {
                Session::flash('admin_msg', 'Lỗi: Không thể cập nhật bài viết. Vui lòng thử lại!', 'alert alert-danger');
                $this->redirect('admin/tin-tuc/edit/' . $id);
            }
        } else {
            $danhMuc = $this->categoryModel->layTheoLoai('bai_viet');
            $this->view('admin/tin-tuc/edit', [
                'news'       => $baiViet,
                'categories' => $danhMuc
            ]);
        }
    }

    // ==========================================
    // XÓA BÀI VIẾT
    // ==========================================

    /**
     * Xoa bai viet theo ID (chi chap nhan POST de tranh xoa nham khi vao URL truc tiep).
     * URL: POST /admin/tin-tuc/delete/{id}
     *
     * @param int $id ID bai viet can xoa
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();

            // Lấy dữ liệu bài viết trước khi xóa (để ghi log và dọn file)
            $baiViet = $this->newsModel->findById($id);
            if (!$baiViet) {
                Session::flash('admin_msg', 'Bài viết không tồn tại.', 'alert alert-warning');
                $this->redirect('admin/tin-tuc');
                return;
            }

            // Xóa dữ liệu liên kết trước khi xóa bài viết chính
            $db = new Database();

            // Xóa bình luận liên kết
            $db->query("DELETE FROM news_comments WHERE bai_viet_id = :id");
            $db->bind(':id', $id);
            $db->execute();

            // Xóa lượt thích liên kết
            $db->query("DELETE FROM news_likes WHERE bai_viet_id = :id");
            $db->bind(':id', $id);
            $db->execute();

            // Xóa bài viết (bai_viet_the sẽ tự xóa nhờ ON DELETE CASCADE)
            if ($this->newsModel->delete($id)) {
                // Xóa file ảnh thu nhỏ trên đĩa nếu có
                if (!empty($baiViet->anh_thu_nho)) {
                    $imgPath = APP_ROOT . '/public/' . $baiViet->anh_thu_nho;
                    if (is_file($imgPath)) {
                        @unlink($imgPath);
                    }
                }

                // Ghi log admin
                SystemLogger::admin(
                    'delete',
                    'tin_tuc',
                    'bai_viet',
                    $id,
                    "Xóa bài viết: #{$id} - " . htmlspecialchars($baiViet->tieu_de ?? ''),
                    (array)$baiViet,
                    [],
                    (int)Session::get('user_id')
                );

                Session::flash('admin_msg', 'Đã xóa bài viết thành công!');
            } else {
                Session::flash('admin_msg', 'Lỗi: Không thể xóa bài viết. Vui lòng thử lại!', 'alert alert-danger');
            }
        }
        $this->redirect('admin/tin-tuc');
    }

    // ==========================================
    // HÀM HELPER DÙNG NỘI BỘ
    // ==========================================

    /**
     * Chuan bi du lieu bai viet tu $_POST sau khi da loc XSS.
     * Dung chung cho ca create() va edit().
     *
     * @return array Mang du lieu bai viet da duoc xu ly
     */
    private function chuanBiDuLieuBaiViet(): array
    {
        return [
            'ma_danh_muc' => (int)trim($_POST['ma_danh_muc']),
            'tieu_de'     => Sanitizer::chuoi((string)($_POST['tieu_de'] ?? ''), 255),
            'duong_dan'   => $this->createSlug((string)($_POST['tieu_de'] ?? '')),
            'tom_tat'     => Sanitizer::chuoi((string)($_POST['tom_tat'] ?? ''), 1000),
            'noi_dung'    => Sanitizer::noiDungHtml((string)($_POST['noi_dung'] ?? '')),
            'trang_thai'  => in_array(($_POST['trang_thai'] ?? ''), ['nhap', 'xuat_ban'], true) ? $_POST['trang_thai'] : 'nhap',
            'noi_bat'     => isset($_POST['noi_bat']) ? 1 : 0,
        ];
    }

    /**
     * Xu ly upload anh, tra ve duong dan tuong doi neu thanh cong.
     * Anh duoc luu vao /public/{$folder}.
     *
     * @param  string      $fieldName  Ten truong input file trong form
     * @param  string      $folder     Thu muc luu anh (tinh tu /public/)
     * @return string|null             Duong dan tuong doi hoac null neu khong upload
     */
    private function uploadAnh(string $fieldName, string $folder): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmpName = $_FILES[$fieldName]['tmp_name'];
        $size    = (int)($_FILES[$fieldName]['size'] ?? 0);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $maxSize = 5 * 1024 * 1024;
        $mime    = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName) ?: '';

        if (!is_uploaded_file($tmpName) || $size <= 0 || $size > $maxSize || !isset($allowed[$mime])) {
            Session::flash('admin_msg', 'Anh tai len khong hop le. Chi cho phep JPG, PNG, WEBP toi da 5MB.', 'alert alert-danger');
            return null;
        }

        $uploadDir = "../public/{$folder}";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName   = 'news_' . bin2hex(random_bytes(12)) . '_' . time() . '.' . $allowed[$mime];
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            return $folder . $fileName;
        }
        return null;
    }
}
