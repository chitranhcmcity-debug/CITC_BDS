<?php
/**
 * Controller AdminDanhMuc - Quản lý tất cả danh mục dùng chung (Module 19)
 * - Categories, TransactionTypes, Facilities, Directions, LegalTypes
 * - Provinces, Districts, Wards (Locations)
 * - Projects
 * - CSV Import / Export
 */
class AdminDanhMucController extends Controller
{
    public CategoryService $catSvc;
    public LocationService $locSvc;
    public ProjectService $projSvc;
    public ImportService $importSvc;
    public ExportService $exportSvc;
    public LogService $logSvc;

    public function __construct()
    {
        Auth::requireRole(1); // Chỉ Admin mới được truy cập
        
        $this->catSvc    = new CategoryService();
        $this->locSvc    = new LocationService();
        $this->projSvc   = new ProjectService();
        $this->importSvc = new ImportService();
        $this->exportSvc = new ExportService();
        $this->logSvc    = new LogService();
    }

    // ==========================================
    // DANH SÁCH DANH MỤC (TABBED DASHBOARD)
    // ==========================================

    public function index(): void
    {
        $tab = $_GET['tab'] ?? 'categories';
        $search = trim($_GET['search'] ?? '');

        $data = [
            'title'  => 'Quản Lý Danh Mục',
            'tab'    => $tab,
            'search' => $search,
        ];

        // Lấy dữ liệu tương ứng với từng Tab
        if ($tab === 'categories') {
            $data['list'] = $this->catSvc->getCategories($search, false);
        } elseif ($tab === 'transaction_types') {
            $data['list'] = $this->catSvc->getTransactionTypes($search);
        } elseif ($tab === 'facilities') {
            $data['list'] = $this->catSvc->getFacilities($search, false);
        } elseif ($tab === 'directions') {
            $data['list'] = $this->catSvc->getDirections($search);
        } elseif ($tab === 'legal_types') {
            $data['list'] = $this->catSvc->getLegalTypes($search);
        }

        $this->view('admin/category/index', $data);
    }

    // ==========================================
    // THÊM MỚI (COMMON CRUD)
    // ==========================================

    public function create(): void
    {
        $type = $_GET['type'] ?? 'categories';
        $this->view('admin/category/create', [
            'title' => 'Thêm Danh Mục Mới',
            'type'  => $type,
        ]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/danh-muc');
        }

        Csrf::verify();

        $type = $_POST['type'] ?? 'categories';
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            Session::flash('admin_msg', 'Tên không được để trống.', 'alert alert-danger');
            $this->redirect("admin/danh-muc/create?type={$type}");
        }

        $slug = $this->createSlug($name);
        $adminId = (int)Session::get('user_id');

        $db = new Database();

        if ($type === 'categories') {
            $code = trim($_POST['code'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $color = trim($_POST['color'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            // Kiểm tra trùng slug
            $db->query("SELECT id FROM nhom_danh_muc WHERE slug = :slug");
            $db->bind(':slug', $slug);
            if ($db->single()) {
                Session::flash('admin_msg', 'Tên danh mục hoặc slug đã tồn tại.', 'alert alert-danger');
                $this->redirect("admin/danh-muc/create?type={$type}");
            }

            $this->catSvc->createCategory([
                'name'        => $name,
                'slug'        => $slug,
                'code'        => $code ?: null,
                'icon'        => $icon ?: null,
                'color'       => $color ?: null,
                'description' => $desc ?: null,
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ], $adminId);

        } elseif ($type === 'transaction_types') {
            $code = trim($_POST['code'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            $db->query("SELECT id FROM loai_giao_dich WHERE slug = :slug");
            $db->bind(':slug', $slug);
            if ($db->single()) {
                Session::flash('admin_msg', 'Tên loại giao dịch đã tồn tại.', 'alert alert-danger');
                $this->redirect("admin/danh-muc/create?type={$type}");
            }

            $this->catSvc->clearCache('categories_all_');
            $id = $this->catSvc->catRepo->create([
                'name'        => $name,
                'slug'        => $slug,
                'code'        => $code ?: null,
                'description' => $desc ?: null,
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ]);
            $this->catSvc->ttRepo->updateSortOrder($id, $sortOrder); // Cập nhật sort
            LogService::write($adminId, 'create', 'transaction_types', $id, "Tạo loại giao dịch mới: {$name}");

        } elseif ($type === 'facilities') {
            $icon = trim($_POST['icon'] ?? '');

            $db->query("SELECT id FROM tien_ich WHERE slug = :slug");
            $db->bind(':slug', $slug);
            if ($db->single()) {
                Session::flash('admin_msg', 'Tên tiện ích đã tồn tại.', 'alert alert-danger');
                $this->redirect("admin/danh-muc/create?type={$type}");
            }

            $this->catSvc->clearCache('facilities_all_');
            $id = $this->catSvc->facRepo->create([
                'name'       => $name,
                'slug'       => $slug,
                'icon'       => $icon ?: null,
                'sort_order' => $sortOrder,
                'status'     => $status,
            ]);
            LogService::write($adminId, 'create', 'facilities', $id, "Tạo tiện ích mới: {$name}");

        } elseif ($type === 'directions') {
            $code = trim($_POST['code'] ?? $slug);

            $db->query("SELECT id FROM huong_nha WHERE code = :code");
            $db->bind(':code', $code);
            if ($db->single()) {
                Session::flash('admin_msg', 'Mã hướng nhà đã tồn tại.', 'alert alert-danger');
                $this->redirect("admin/danh-muc/create?type={$type}");
            }

            $id = $this->catSvc->dirRepo->create([
                'name'       => $name,
                'code'       => $code,
                'sort_order' => $sortOrder,
                'status'     => $status,
            ]);
            LogService::write($adminId, 'create', 'directions', $id, "Tạo hướng nhà mới: {$name}");

        } elseif ($type === 'legal_types') {
            $code = trim($_POST['code'] ?? $slug);

            $db->query("SELECT id FROM loai_phap_ly WHERE code = :code");
            $db->bind(':code', $code);
            if ($db->single()) {
                Session::flash('admin_msg', 'Mã pháp lý đã tồn tại.', 'alert alert-danger');
                $this->redirect("admin/danh-muc/create?type={$type}");
            }

            $id = $this->catSvc->legalRepo->create([
                'name'       => $name,
                'code'       => $code,
                'sort_order' => $sortOrder,
                'status'     => $status,
            ]);
            LogService::write($adminId, 'create', 'legal_types', $id, "Tạo pháp lý mới: {$name}");
        }

        Session::flash('admin_msg', 'Đã thêm mới danh mục thành công!');
        $this->redirect("admin/danh-muc?tab={$type}");
    }

    // ==========================================
    // CẬP NHẬT (COMMON CRUD)
    // ==========================================

    public function edit(int $id): void
    {
        $type = $_GET['type'] ?? 'categories';
        $item = null;

        if ($type === 'categories') {
            $item = $this->catSvc->catRepo->findById($id);
        } elseif ($type === 'transaction_types') {
            $item = $this->catSvc->ttRepo->findById($id);
        } elseif ($type === 'facilities') {
            $item = $this->catSvc->facRepo->findById($id);
        } elseif ($type === 'directions') {
            $item = $this->catSvc->dirRepo->findById($id);
        } elseif ($type === 'legal_types') {
            $item = $this->catSvc->legalRepo->findById($id);
        }

        if (!$item) {
            Session::flash('admin_msg', 'Dữ liệu không tồn tại.', 'alert alert-danger');
            $this->redirect("admin/danh-muc?tab={$type}");
        }

        $this->view('admin/category/edit', [
            'title' => 'Cập Nhật Danh Mục',
            'type'  => $type,
            'item'  => $item,
        ]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/danh-muc');
        }

        Csrf::verify();

        $type = $_POST['type'] ?? 'categories';
        $name = trim($_POST['name'] ?? '');
        $status = $_POST['status'] ?? 'active';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($name)) {
            Session::flash('admin_msg', 'Tên không được để trống.', 'alert alert-danger');
            $this->redirect("admin/danh-muc/edit/{$id}?type={$type}");
        }

        $slug = $this->createSlug($name);
        $adminId = (int)Session::get('user_id');

        if ($type === 'categories') {
            $code = trim($_POST['code'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $color = trim($_POST['color'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            $this->catSvc->updateCategory($id, [
                'name'        => $name,
                'slug'        => $slug,
                'code'        => $code ?: null,
                'icon'        => $icon ?: null,
                'color'       => $color ?: null,
                'description' => $desc ?: null,
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ], $adminId);

        } elseif ($type === 'transaction_types') {
            $code = trim($_POST['code'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            $this->catSvc->clearCache('categories_all_');
            $this->catSvc->ttRepo->update($id, [
                'name'        => $name,
                'slug'        => $slug,
                'code'        => $code ?: null,
                'description' => $desc ?: null,
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ]);
            LogService::write($adminId, 'update', 'transaction_types', $id, "Cập nhật loại giao dịch: {$name}");

        } elseif ($type === 'facilities') {
            $icon = trim($_POST['icon'] ?? '');

            $this->catSvc->clearCache('facilities_all_');
            $this->catSvc->facRepo->update($id, [
                'name'       => $name,
                'slug'       => $slug,
                'icon'       => $icon ?: null,
                'sort_order' => $sortOrder,
                'status'     => $status,
            ]);
            LogService::write($adminId, 'update', 'facilities', $id, "Cập nhật tiện ích: {$name}");

        } elseif ($type === 'directions') {
            $code = trim($_POST['code'] ?? $slug);

            $this->catSvc->dirRepo->update($id, [
                'name'       => $name,
                'code'       => $code,
                'sort_order' => $sortOrder,
                'status'     => $status,
            ]);
            LogService::write($adminId, 'update', 'directions', $id, "Cập nhật hướng nhà: {$name}");

        } elseif ($type === 'legal_types') {
            $code = trim($_POST['code'] ?? $slug);

            $this->catSvc->legalRepo->update($id, [
                'name'       => $name,
                'code'       => $code,
                'sort_order' => $sortOrder,
                'status'     => $status,
            ]);
            LogService::write($adminId, 'update', 'legal_types', $id, "Cập nhật pháp lý: {$name}");
        }

        Session::flash('admin_msg', 'Đã cập nhật danh mục thành công!');
        $this->redirect("admin/danh-muc?tab={$type}");
    }

    // ==========================================
    // XÓA (COMMON CRUD)
    // ==========================================

    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/danh-muc');
            return;
        }

        Csrf::verify();

        $type = $_GET['type'] ?? 'categories';
        $adminId = (int)Session::get('user_id');
        $success = false;
        $db = new Database();

        if ($type === 'categories') {
            $success = $this->catSvc->deleteCategory($id, $adminId);
        } elseif ($type === 'transaction_types') {
            // Kiểm tra liên kết: loại giao dịch đang được sử dụng trong tin đăng
            $db->query("SELECT COUNT(*) as total FROM du_an WHERE loai_bat_dong_san IN (SELECT name FROM loai_giao_dich WHERE id = :id)");
            $db->bind(':id', $id);
            if ((int)$db->single()->total > 0) {
                Session::flash('admin_msg', 'Không thể xóa: Loại giao dịch đang được sử dụng trong tin đăng.', 'alert alert-danger');
                $this->redirect("admin/danh-muc?tab={$type}");
                return;
            }
            $this->catSvc->clearCache('categories_all_');
            $success = $this->catSvc->ttRepo->delete($id);
            if ($success) LogService::write($adminId, 'delete', 'transaction_types', $id, "Xóa loại giao dịch ID #{$id}");
        } elseif ($type === 'facilities') {
            $this->catSvc->clearCache('facilities_all_');
            $success = $this->catSvc->facRepo->delete($id);
            if ($success) LogService::write($adminId, 'delete', 'facilities', $id, "Xóa tiện ích ID #{$id}");
        } elseif ($type === 'directions') {
            // Kiểm tra liên kết: hướng nhà đang được sử dụng trong tin đăng
            $db->query("SELECT COUNT(*) as total FROM du_an WHERE huong_nha IN (SELECT name FROM huong_nha WHERE id = :id)");
            $db->bind(':id', $id);
            if ((int)$db->single()->total > 0) {
                Session::flash('admin_msg', 'Không thể xóa: Hướng nhà đang được sử dụng trong tin đăng.', 'alert alert-danger');
                $this->redirect("admin/danh-muc?tab={$type}");
                return;
            }
            $success = $this->catSvc->dirRepo->delete($id);
            if ($success) LogService::write($adminId, 'delete', 'directions', $id, "Xóa hướng nhà ID #{$id}");
        } elseif ($type === 'legal_types') {
            // Kiểm tra liên kết: pháp lý đang được sử dụng trong tin đăng
            $db->query("SELECT COUNT(*) as total FROM du_an WHERE phap_ly IN (SELECT code FROM loai_phap_ly WHERE id = :id)");
            $db->bind(':id', $id);
            if ((int)$db->single()->total > 0) {
                Session::flash('admin_msg', 'Không thể xóa: Loại pháp lý đang được sử dụng trong tin đăng.', 'alert alert-danger');
                $this->redirect("admin/danh-muc?tab={$type}");
                return;
            }
            $success = $this->catSvc->legalRepo->delete($id);
            if ($success) LogService::write($adminId, 'delete', 'legal_types', $id, "Xóa pháp lý ID #{$id}");
        }

        if ($success) {
            Session::flash('admin_msg', 'Đã xóa danh mục thành công!');
        } else {
            Session::flash('admin_msg', 'Lỗi khi xóa: Danh mục có thể đang chứa dữ liệu liên kết.', 'alert alert-danger');
        }
        $this->redirect("admin/danh-muc?tab={$type}");
    }

    // ==========================================
    // ĐỔI TRẠNG THÁI AJAX / POST
    // ==========================================

    public function status(int $id): void
    {
        Csrf::verify();
        $type = $_GET['type'] ?? 'categories';
        $status = $_POST['status'] ?? 'active';
        $adminId = (int)Session::get('user_id');
        $success = false;

        if ($type === 'categories') {
            $success = $this->catSvc->changeCategoryStatus($id, $status, $adminId);
        } elseif ($type === 'transaction_types') {
            $success = $this->catSvc->ttRepo->changeStatus($id, $status);
            if ($success) LogService::write($adminId, 'status', 'transaction_types', $id, "Đổi trạng thái loại giao dịch #{$id} thành {$status}");
        } elseif ($type === 'facilities') {
            $success = $this->catSvc->facRepo->changeStatus($id, $status);
            if ($success) LogService::write($adminId, 'status', 'facilities', $id, "Đổi trạng thái tiện ích #{$id} thành {$status}");
        } elseif ($type === 'directions') {
            $success = $this->catSvc->dirRepo->changeStatus($id, $status);
            if ($success) LogService::write($adminId, 'status', 'directions', $id, "Đổi trạng thái hướng nhà #{$id} thành {$status}");
        } elseif ($type === 'legal_types') {
            $success = $this->catSvc->legalRepo->changeStatus($id, $status);
            if ($success) LogService::write($adminId, 'status', 'legal_types', $id, "Đổi trạng thái pháp lý #{$id} thành {$status}");
        }

        $this->redirect("admin/danh-muc?tab={$type}");
    }

    // ==========================================
    // SẮP XẾP THỨ TỰ AJAX / POST
    // ==========================================

    public function sort(): void
    {
        Csrf::verify();
        $type = $_GET['type'] ?? 'categories';
        $orders = $_POST['orders'] ?? []; // Array [id => sort_order]
        $adminId = (int)Session::get('user_id');

        foreach ($orders as $id => $order) {
            $id = (int)$id;
            $order = (int)$order;
            if ($type === 'categories') {
                $this->catSvc->catRepo->updateSortOrder($id, $order);
            } elseif ($type === 'transaction_types') {
                $this->catSvc->ttRepo->updateSortOrder($id, $order);
            } elseif ($type === 'facilities') {
                $this->catSvc->facRepo->updateSortOrder($id, $order);
            } elseif ($type === 'directions') {
                $this->catSvc->dirRepo->updateSortOrder($id, $order);
            } elseif ($type === 'legal_types') {
                $this->catSvc->legalRepo->updateSortOrder($id, $order);
            }
        }

        $this->catSvc->clearCache('categories_all_');
        $this->catSvc->clearCache('facilities_all_');
        LogService::write($adminId, 'sort', $type, null, "Cập nhật sắp xếp thứ tự hiển thị danh mục loại: {$type}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }

    // ==========================================
    // QUẢN LÝ ĐỊA GIỚI HÀNH CHÍNH
    // ==========================================

    public function location(): void
    {
        $search = trim($_GET['search'] ?? '');
        $data = [
            'title'     => 'Quản Lý Địa Giới Hành Chính',
            'provinces' => $this->locSvc->getProvinces($search),
            'search'    => $search,
        ];
        $this->view('admin/category/location', $data);
    }

    // ==========================================
    // IMPORT EXCEL / CSV
    // ==========================================

    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/danh-muc/location');
        }

        Csrf::verify();

        $type = $_POST['type'] ?? ''; // provinces, districts, wards
        if (empty($_FILES['file']['tmp_name'])) {
            Session::flash('admin_msg', 'Vui lòng chọn tệp tin CSV cần tải lên.', 'alert alert-danger');
            $this->redirect('admin/danh-muc/location');
        }

        $adminId = (int)Session::get('user_id');
        $result = $this->importSvc->importLocations($type, $_FILES['file']['tmp_name'], $adminId);

        if ($result['success']) {
            $msg = "Đã nhập thành công {$result['imported']} dòng.";
            if (!empty($result['errors'])) {
                $msg .= " Một số dòng gặp lỗi: <br> - " . implode('<br> - ', array_slice($result['errors'], 0, 5));
            }
            Session::flash('admin_msg', $msg, 'alert alert-info');
        } else {
            Session::flash('admin_msg', 'Không thể nhập dữ liệu. Lỗi: ' . implode('<br>', $result['errors']), 'alert alert-danger');
        }

        $this->redirect('admin/danh-muc/location');
    }

    // ==========================================
    // EXPORT EXCEL / CSV
    // ==========================================

    public function export(): void
    {
        $tab = $_GET['tab'] ?? 'categories';
        $adminId = (int)Session::get('user_id');

        LogService::write($adminId, 'export', $tab, null, "Xuất báo cáo danh mục loại: {$tab}");

        if ($tab === 'categories') {
            $headers = ['ID', 'Tên Danh Mục', 'Slug Đường Dẫn', 'Mã Code', 'Biểu Tượng', 'Mô Tả', 'Sắp Xếp', 'Màu Sắc', 'Trạng Thái'];
            $rows = array_map(static fn($c) => [
                $c['id'], $c['name'], $c['slug'], $c['code'], $c['icon'], $c['description'], $c['sort_order'], $c['color'], $c['status']
            ], $this->catSvc->getCategories());
            $this->exportSvc->exportCSV('danh-muc-bds.csv', $headers, $rows);

        } elseif ($tab === 'provinces') {
            $headers = ['ID', 'Mã Tỉnh', 'Tên Tỉnh/Thành Phố', 'Phân Loại', 'Sắp Xếp', 'Trạng Thái'];
            $rows = array_map(static fn($p) => [
                $p['id'], $p['code'], $p['name'], $p['type'], $p['sort_order'], $p['status']
            ], $this->locSvc->getProvinces());
            $this->exportSvc->exportCSV('tinh-thanh-pho.csv', $headers, $rows);
        }
    }

    // ==========================================
    // QUẢN LÝ DỰ ÁN DÙNG CHUNG
    // ==========================================

    public function project(): void
    {
        $action = $_GET['action'] ?? 'list';
        $adminId = (int)Session::get('user_id');

        if ($action === 'list') {
            $search = trim($_GET['search'] ?? '');
            $data = [
                'title'  => 'Quản Lý Dự Án Bất Động Sản',
                'list'   => $this->projSvc->getProjects($search),
                'search' => $search
            ];
            $this->view('admin/category/project', $data);

        } elseif ($action === 'create') {
            $this->view('admin/category/project_create', [
                'title' => 'Thêm Dự Án Mới'
            ]);

        } elseif ($action === 'store') {
            Csrf::verify();
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                Session::flash('admin_msg', 'Tên dự án là bắt buộc.', 'alert alert-danger');
                $this->redirect('admin/danh-muc/project?action=create');
            }

            // Xử lý tiện ích (Facilities) chuyển sang JSON array
            $facArr = $_POST['facilities'] ?? [];

            $this->projSvc->createProject([
                'name'        => $name,
                'investor'    => trim($_POST['investor'] ?? ''),
                'address'     => trim($_POST['address'] ?? ''),
                'google_map'  => trim($_POST['google_map'] ?? ''),
                'logo'        => trim($_POST['logo'] ?? ''),
                'image'       => trim($_POST['image'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'facilities'  => json_encode($facArr),
                'sort_order'  => (int)($_POST['sort_order'] ?? 0),
                'status'      => $_POST['status'] ?? 'active'
            ], $adminId);

            Session::flash('admin_msg', 'Đã thêm dự án thành công!');
            $this->redirect('admin/danh-muc/project');

        } elseif ($action === 'edit') {
            $id = (int)($_GET['id'] ?? 0);
            $item = $this->projSvc->getProjects('', false);
            $project = null;
            foreach ($item as $p) {
                if ((int)$p['id'] === $id) {
                    $project = (object)$p;
                    break;
                }
            }

            if (!$project) {
                Session::flash('admin_msg', 'Dự án không tồn tại.', 'alert alert-danger');
                $this->redirect('admin/danh-muc/project');
            }

            $this->view('admin/category/project_edit', [
                'title'   => 'Sửa Dự Án Bất Động Sản',
                'project' => $project
            ]);

        } elseif ($action === 'update') {
            Csrf::verify();
            $id = (int)($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');

            if (empty($name)) {
                Session::flash('admin_msg', 'Tên dự án là bắt buộc.', 'alert alert-danger');
                $this->redirect("admin/danh-muc/project?action=edit&id={$id}");
            }

            $facArr = $_POST['facilities'] ?? [];

            $this->projSvc->updateProject($id, [
                'name'        => $name,
                'investor'    => trim($_POST['investor'] ?? ''),
                'address'     => trim($_POST['address'] ?? ''),
                'google_map'  => trim($_POST['google_map'] ?? ''),
                'logo'        => trim($_POST['logo'] ?? ''),
                'image'       => trim($_POST['image'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'facilities'  => json_encode($facArr),
                'sort_order'  => (int)($_POST['sort_order'] ?? 0),
                'status'      => $_POST['status'] ?? 'active'
            ], $adminId);

            Session::flash('admin_msg', 'Đã cập nhật dự án thành công!');
            $this->redirect('admin/danh-muc/project');

        } elseif ($action === 'delete') {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('admin/danh-muc/project');
                return;
            }
            Csrf::verify();
            $id = (int)($_GET['id'] ?? 0);
            if ($this->projSvc->deleteProject($id, $adminId)) {
                Session::flash('admin_msg', 'Đã xóa dự án thành công!');
            } else {
                Session::flash('admin_msg', 'Không thể xóa: Dự án đang được sử dụng trong bài đăng.', 'alert alert-danger');
            }
            $this->redirect('admin/danh-muc/project');
        }
    }
}
