<?php $layout = $data['layout'] ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Panel - TimNhaDat.site' ?></title>
    <!-- Favicon -->
    <link rel="icon" href="<?= URL_ROOT ?>/public/images/favicon.png?v=2" type="image/png">
    
    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/adminlte.min.css">
    <style>
        /* Custom tweaks to match AdminLTE and preserve look-and-feel */
        .brand-link {
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            background: rgba(0, 0, 0, 0.15);
            padding: 0.9rem 1rem !important;
        }
        .nav-link.active {
            background: linear-gradient(135deg, #0dbfaa, #077c6e) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(13, 191, 170, 0.35) !important;
            font-weight: 600;
        }
        .admin-dialog-card { border-radius: 20px; overflow: hidden; }
        .admin-dialog-width { max-width: 480px; }
        .admin-dialog-icon { width: 74px; height: 74px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.85rem; }
        .admin-dialog-icon.is-danger { color: #dc3545; background: #fff0f1; }
        .admin-dialog-icon.is-warning { color: #b77900; background: #fff7df; }
        .admin-dialog-icon.is-success { color: #198754; background: #eaf8f1; }
        .admin-dialog-icon.is-info { color: #0d6efd; background: #edf5ff; }
        .admin-dialog-card .btn { min-width: 118px; border-radius: 10px; padding-top: .65rem; padding-bottom: .65rem; }
        .admin-dialog-input { border-radius: 10px; resize: vertical; }

        /* Sidebar Polish - Compact spacing */
        .sidebar-menu .nav-link {
            padding: 0.45rem 0.85rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border-radius: 6px;
            margin: 0.1rem 0.6rem;
            width: auto;
            position: relative;
        }
        .sidebar-menu .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 4px;
            background-color: #ffffff;
            border-radius: 0 4px 4px 0;
        }
        .sidebar-menu .nav-link:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.06);
            color: #0dbfaa !important;
            transform: translateX(6px);
        }
        .sidebar-menu .nav-link:hover .nav-icon {
            transform: scale(1.15) rotate(6deg);
        }
        .sidebar-menu .nav-icon {
            font-size: 1.1rem;
            width: 28px;
            text-align: center;
            transition: transform 0.3s ease, color 0.3s ease;
        }
        .sidebar-menu .nav-header {
            font-size: 0.72rem !important;
            color: #8da2bb !important;
            margin-top: 1rem !important;
            margin-bottom: 0.2rem !important;
            letter-spacing: 0.12em;
            padding-left: 1.25rem !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-menu .nav-header::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.06);
            margin-right: 1.25rem;
        }

        /* Custom sidebar scrollbar */
        .sidebar-wrapper::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
        }
        .sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        /* Caterpillar Wavy Text - Xem Website */
        .caterpillar-btn {
            background: linear-gradient(135deg, #10b981, #22c55e, #86efac);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent !important;
            font-weight: 900;
            padding: 4px 0;
            margin-top: 5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none !important;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            animation: caterpillar-crawl 3s infinite ease-in-out, caterpillar-gradient 4s infinite linear;
            font-size: 1.15rem;
            border: none !important;
            box-shadow: none !important;
            filter: drop-shadow(0px 2px 4px rgba(34, 197, 94, 0.35));
        }
        .caterpillar-btn .fa-solid {
            color: #10b981 !important;
            -webkit-text-fill-color: #10b981 !important;
            display: inline-block;
            transition: transform 0.3s ease;
            animation: spin-slow 10s infinite linear;
        }
        .caterpillar-btn:hover {
            transform: scale(1.12) translateY(-3px) !important;
            filter: brightness(1.25) drop-shadow(0px 4px 12px rgba(16, 185, 129, 0.55));
        }
        .caterpillar-btn:hover .fa-solid {
            animation: spin-continuous 1.2s infinite linear;
        }
        @keyframes spin-slow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes spin-continuous {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes caterpillar-crawl {
            0%, 100% {
                transform: translateY(0) scaleX(1) scaleY(1);
            }
            25% {
                transform: translateY(-4px) scaleX(1.04) scaleY(0.96);
            }
            50% {
                transform: translateY(2px) scaleX(0.96) scaleY(1.04);
            }
            75% {
                transform: translateY(-2px) scaleX(1.02) scaleY(0.98);
            }
        }
        @keyframes caterpillar-gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <!-- Top Navbar -->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm">
            <div class="container-fluid">
                <!-- Left Navbar Links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-dark" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list fs-4"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block ms-3 d-flex align-items-center">
                        <a href="<?= URL_ROOT ?>/" class="caterpillar-btn" title="Truy cập trang chủ Website">
                            <i class="fa-solid fa-earth-asia" style="transition: transform 0.6s ease;"></i> Xem Website
                        </a>
                    </li>
                </ul>
                <!-- Right Navbar Links -->
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item text-dark me-3 d-flex align-items-center">
                        <img src="<?= URL_ROOT ?>/public/images/favicon.png?v=2" alt="Avatar" class="rounded-circle me-2" style="width: 26px; height: 26px; object-fit: contain;">
                        <span>Xin chào, <strong><?= htmlspecialchars((string)Session::get('user_name')) ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <form action="<?= URL_ROOT ?>/nguoi-dung/dang-xuat" method="POST" class="m-0">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Sidebar -->
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <!-- Sidebar Brand -->
            <div class="sidebar-brand">
                <a href="<?= URL_ROOT ?>/admin/dashboard" class="brand-link">
                    <img src="<?= URL_ROOT ?>/public/images/favicon.png?v=2" alt="Logo" class="brand-image" style="max-height: 33px; object-fit: contain; filter: brightness(0) invert(1);">
                    <span class="brand-text fw-semibold fs-5 text-white ms-2">TimNhaDat Admin</span>
                </a>
            </div>
            
            <!-- Sidebar Wrapper -->
            <div class="sidebar-wrapper">
                <nav class="mt-3">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="false">
                        <?php
                        $pendingProjects = (int)($layout['pendingProjects'] ?? 0);
                        $pendingLiveChats = (int)($layout['pendingLiveChats'] ?? 0);
                        $current_uri = $_SERVER['REQUEST_URI'] ?? '';
                        $is_dashboard = (strpos($current_uri, 'admin/dashboard') !== false || substr($current_uri, -6) == '/admin' || substr($current_uri, -7) == '/admin/');
                        ?>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/dashboard" class="nav-link <?= $is_dashboard ? 'active' : '' ?>">
                                <i class="nav-icon fa-solid fa-gauge"></i>
                                <p>Tổng Quan</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/du-an" class="nav-link <?= (strpos($current_uri, 'admin/du-an') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fa-solid fa-building"></i>
                                <p>
                                    Dự án
                                    <?php if($pendingProjects > 0): ?>
                                        <span class="badge bg-danger rounded-pill float-end"><?= $pendingProjects ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/danh-muc" class="nav-link <?= (strpos($current_uri, 'admin/danh-muc') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fa-solid fa-tags"></i>
                                <p>Danh Mục</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/tin-tuc" class="nav-link <?= (strpos($current_uri, 'admin/tin-tuc') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fa-regular fa-newspaper"></i>
                                <p>Tin Tức</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/contact" class="nav-link <?= (strpos($current_uri, 'admin/contact') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fa-solid fa-headset"></i>
                                <p>Yêu cầu tư vấn (CRM)</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/wallet" class="nav-link <?= (strpos($current_uri, 'admin/wallet') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fa-solid fa-wallet"></i>
                                <p>Ví & Giao dịch CRM</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="<?= URL_ROOT ?>/admin/live-chat" class="nav-link <?= (strpos($current_uri, 'admin/live-chat') !== false) ? 'active' : '' ?>">
                                <i class="nav-icon fa-regular fa-comments"></i>
                                <p>
                                    Live Chat
                                    <span class="badge bg-danger rounded-pill float-end <?= $pendingLiveChats > 0 ? '' : 'd-none' ?>" data-live-chat-waiting-badge><?= $pendingLiveChats ?></span>
                                </p>
                            </a>
                        </li>

                        <?php if(Session::get('user_role_id') == 1): ?>
                            <li class="nav-header text-uppercase text-xs font-weight-bold text-muted mt-3 mb-1">Hệ Thống</li>
                            
                            <li class="nav-item">
                                <a href="<?= URL_ROOT ?>/admin/nguoi-dung" class="nav-link <?= (strpos($current_uri, 'admin/nguoi-dung') !== false) ? 'active' : '' ?>">
                                    <i class="nav-icon fa-solid fa-user-shield"></i>
                                    <p>Người Dùng</p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="<?= URL_ROOT ?>/admin/bang-gia" class="nav-link <?= (strpos($current_uri, 'admin/bang-gia') !== false) ? 'active' : '' ?>">
                                    <i class="nav-icon fa-solid fa-money-bill-wave"></i>
                                    <p>Bảng Giá</p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="<?= URL_ROOT ?>/admin/bao-cao" class="nav-link <?= (strpos($current_uri, 'admin/bao-cao') !== false) ? 'active' : '' ?>">
                                    <i class="nav-icon fa-solid fa-chart-pie"></i>
                                    <p>Báo Cáo</p>
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a href="<?= URL_ROOT ?>/admin/cai-dat" class="nav-link <?= (strpos($current_uri, 'admin/cai-dat') !== false) ? 'active' : '' ?>">
                                    <i class="nav-icon fa-solid fa-gear"></i>
                                    <p>Cài Đặt</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= URL_ROOT ?>/admin/roles" class="nav-link <?= (strpos($current_uri, 'admin/roles') !== false) ? 'active' : '' ?>">
                                    <i class="nav-icon fa-solid fa-user-shield"></i><p>Phân quyền & Vai trò</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= URL_ROOT ?>/admin/system-log" class="nav-link <?= (strpos($current_uri, 'admin/system-log') !== false) ? 'active' : '' ?>">
                                    <i class="nav-icon fa-solid fa-clock-rotate-left"></i>
                                    <p>Nhật Ký Hệ Thống</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>
        
        <!-- Main Content Wrapper -->
        <main class="app-main">
            <!-- App Content Header -->
            <div class="app-content-header py-3 bg-white border-bottom mb-3 shadow-xs">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-semibold text-dark fs-4"><?= $title ?? 'Tổng Quan' ?></h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end mb-0">
                                <li class="breadcrumb-item"><a href="<?= URL_ROOT ?>/admin/dashboard" class="text-decoration-none">Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= $title ?? 'Dashboard' ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- App Content -->
            <div class="app-content">
                <div class="container-fluid">
