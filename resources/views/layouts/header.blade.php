<?php
$layout = $data['layout'] ?? [];
$siteSettings = $layout['settings'] ?? [];
$siteName = (string)($siteSettings['site_name'] ?? SITE_NAME);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : SITE_NAME ?></title>
    <!-- Dynamic SEO Meta Tags -->
    <?php if (isset($metaDescription)): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php elseif (isset($meta_description)): ?>
        <meta name="description" content="<?= htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8') ?>">
    <?php elseif (isset($meta_desc)): ?>
        <meta name="description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($meta_desc, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (isset($canonicalUrl)): ?>
        <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php elseif (isset($canonical_url)): ?>
        <link rel="canonical" href="<?= htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8') ?>">
    <?php elseif (isset($canonical)): ?>
        <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (isset($ogImage)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php elseif (isset($og_image)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8') ?>">
        <meta name="twitter:image" content="<?= htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= htmlspecialchars(isset($title) ? $title : SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars(isset($title) ? $title : SITE_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <?php if (isset($schemaJson)): ?>
        <script type="application/ld+json"><?= $schemaJson ?></script>
    <?php endif; ?>
    <!-- Favicon -->
    <link rel="icon" href="<?= URL_ROOT ?>/public/images/favicon.png?v=2" type="image/png">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/style.css?v=<?= filemtime(APP_ROOT . '/public/css/style.css') ?>">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/notion-theme.css?v=<?= filemtime(APP_ROOT . '/public/css/notion-theme.css') ?>">
    <link rel="stylesheet" href="<?= URL_ROOT ?>/public/css/live-chat.css?v=<?= filemtime(APP_ROOT . '/public/css/live-chat.css') ?>">
    <style>
        /* Premium Page Loader Transition Overlay */
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.5s cubic-bezier(0.25, 1, 0.5, 1), visibility 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .page-loader.fade-out {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            animation: loaderContentEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .loader-logo-wrapper {
            position: relative;
            width: 110px;
            height: 110px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-ring {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            border: 3.5px solid rgba(86, 69, 212, 0.08);
            border-top-color: #5645d4;
            border-right-color: #f2cb6c;
            animation: loaderSpin 1.1s cubic-bezier(0.5, 0.12, 0.35, 0.9) infinite;
        }

        .loader-logo {
            width: 64px !important;
            height: 64px !important;
            object-fit: contain;
            z-index: 2;
            animation: loaderLogoPulse 2s ease-in-out infinite;
        }

        .loader-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            font-family: 'Inter', sans-serif;
        }

        .loader-progress-container {
            width: 200px;
            height: 4px;
            background: rgba(86, 69, 212, 0.08);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 16px;
            position: relative;
        }

        .loader-progress-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #5645d4, #f2cb6c);
            border-radius: 10px;
            transition: width 0.3s ease-out;
        }

        .loader-status {
            font-size: 0.85rem;
            font-weight: 500;
            color: #787671;
            letter-spacing: 0.2px;
            opacity: 0.85;
        }

        /* Animations */
        @keyframes loaderSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes loaderLogoPulse {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 4px 6px rgba(86, 69, 212, 0.15));
            }
            50% {
                transform: scale(1.06);
                filter: drop-shadow(0 8px 16px rgba(86, 69, 212, 0.35));
            }
        }

        @keyframes loaderContentEntry {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .vip-badge {
            background: linear-gradient(45deg, #ffd700, #ff8c00);
            color: #fff !important;
            border: 1px solid #ffeb3b;
            box-shadow: 0 4px 6px rgba(255, 140, 0, 0.4);
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .vip-badge i {
            color: #fff;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
        }

        .main-navbar-menu > .nav-item > .nav-link {
            position: relative;
            border-radius: 8px;
            padding: 10px 12px;
            transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
        }

        .main-navbar-menu > .nav-item > .nav-link::after {
            content: "";
            position: absolute;
            left: 12px;
            right: 12px;
            bottom: 5px;
            height: 2px;
            border-radius: 999px;
            background: #198754;
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.2s ease;
        }

        .main-navbar-menu > .nav-item > .nav-link:hover,
        .main-navbar-menu > .nav-item > .nav-link:focus {
            color: #198754 !important;
            background: rgba(25, 135, 84, 0.08);
            transform: translateY(-1px);
        }

        .main-navbar-menu > .nav-item > .nav-link:hover::after,
        .main-navbar-menu > .nav-item > .nav-link:focus::after,
        .main-navbar-menu > .nav-item > .nav-link.text-primary::after {
            transform: scaleX(1);
        }

        .navbar .user-menu-toggle {
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .navbar .user-menu-toggle:hover,
        .navbar .user-menu-toggle:focus {
            color: #111827 !important;
            background-color: #ffc107 !important;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(251, 191, 36, 0.28) !important;
        }

        /* Floating Action Buttons Panel */
        .floating-contact-panel {
            position: fixed;
            bottom: 94px;
            right: 24px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
        }
        
        .floating-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white !important;
            text-decoration: none;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.18);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            cursor: pointer;
            border: none;
        }
        
        .floating-btn:hover {
            transform: scale(1.08);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.28);
        }
        
        .floating-btn-booking {
            background-color: #f2cb6c;
            animation: floating-booking-live 2.8s ease-in-out infinite;
        }
        
        .floating-btn-booking:hover {
            animation-play-state: paused;
            transform: translateY(-4px) scale(1.1) !important;
        }

        .floating-btn-booking:hover {
            box-shadow: 0 16px 30px rgba(242, 203, 108, 0.48) !important;
        }

        
        /* Pulse animations for attention grabbing */
        @keyframes pulse-gold {
            0% { box-shadow: 0 0 0 0 rgba(242, 203, 108, 0.6); }
            70% { box-shadow: 0 0 0 15px rgba(242, 203, 108, 0); }
            100% { box-shadow: 0 0 0 0 rgba(242, 203, 108, 0); }
        }
        
        @keyframes floating-booking-live {
            0%, 100% {
                transform: translateY(0) scale(1);
                box-shadow: 0 6px 14px rgba(242, 203, 108, 0.32), 0 0 0 0 rgba(242, 203, 108, 0.46);
            }
            50% {
                transform: translateY(-5px) scale(1.04);
                box-shadow: 0 14px 26px rgba(242, 203, 108, 0.42), 0 0 0 13px rgba(242, 203, 108, 0);
            }
        }

        /* Premium Hover Tooltips */
        .floating-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            right: 65px;
            top: 50%;
            transform: translateY(-50%) translateX(10px);
            background: rgba(15, 23, 42, 0.95);
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10001;
        }
        
        .floating-btn:hover::after {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) translateX(0);
        }
    </style>
</head>

<body>

    <!-- Premium Page Loader / Intro Transition Overlay -->
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="loader-logo-wrapper">
                <div class="loader-ring"></div>
                <img src="<?= URL_ROOT ?>/public/images/favicon.png?v=2" alt="Logo" class="loader-logo">
            </div>
            <h3 class="loader-title"><?= htmlspecialchars($siteName) ?></h3>
            <div class="loader-progress-container">
                <div class="loader-progress-bar"></div>
            </div>
            <span class="loader-status">Đang kết nối không gian sống mới...</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm"
        style="background: #ffffff; z-index: 9999;">
        <?php
        $current_uri = $_SERVER['REQUEST_URI'] ?? '';
        $is_sell = strpos($current_uri, '/du-an') !== false && strpos($current_uri, 'type=rent') === false;
        $is_rent = strpos($current_uri, '/du-an?type=rent') !== false;
        $is_news = strpos($current_uri, '/blog') !== false || strpos($current_uri, 'tin-tuc') !== false;
        $is_pricing = strpos($current_uri, '/bang-gia') !== false;
        ?>
        <div class="container">
            <a class="navbar-brand text-dark fw-bold fs-4 d-flex align-items-center" href="<?= URL_ROOT ?>/">
                <img src="<?= URL_ROOT ?>/public/images/favicon.png?v=2" alt="Logo" width="55" height="55" class="d-inline-block align-text-top me-2" style="object-fit: contain;">
                <?= htmlspecialchars($siteName) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav main-navbar-menu me-auto mb-2 mb-lg-0 fw-semibold fs-6 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link <?= $is_sell ? 'text-primary' : 'text-dark' ?>" href="<?= URL_ROOT ?>/du-an">
                            Nhà đất bán
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_rent ? 'text-primary' : 'text-dark' ?>" href="<?= URL_ROOT ?>/du-an?type=rent">
                            Nhà đất cho thuê
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link <?= $is_news ? 'text-primary' : 'text-dark' ?>" href="<?= URL_ROOT ?>/tin-tuc">
                            Tin tức
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $is_pricing ? 'text-primary' : 'text-dark' ?>" href="<?= URL_ROOT ?>/bang-gia">
                            Báo giá
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center fw-semibold fs-6">
                        <?php if (Session::get('user_id')): ?>
                        <?php
                            $unreadCount = (int)($layout['unreadNotifications'] ?? 0);
                            $notifications = $layout['notifications'] ?? [];
                        ?>
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link text-dark position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-bell fs-5"></i>
                                <?php if ($unreadCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                        <?= $unreadCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                                <li><h6 class="dropdown-header fw-bold">Thông báo hệ thống</h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php
                                    if (count($notifications) > 0):
                                        foreach ($notifications as $notif):
                                ?>
                                    <li>
                                        <a class="dropdown-item py-2 <?= $notif->da_doc == 0 ? 'bg-light' : '' ?>" href="<?= URL_ROOT ?>/vi-dien-tu">
                                            <p class="mb-1 fw-bold text-wrap <?= $notif->da_doc == 0 ? 'text-primary' : '' ?>" style="font-size: 0.85rem;"><?= htmlspecialchars($notif->tieu_de) ?></p>
                                            <p class="mb-1 text-wrap text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($notif->noi_dung) ?></p>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?= date('H:i d/m/Y', strtotime($notif->ngay_tao)) ?></small>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php 
                                        endforeach;
                                    else: 
                                ?>
                                    <li><span class="dropdown-item text-muted text-center py-3">Không có thông báo mới</span></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link user-menu-toggle dropdown-toggle text-dark fw-bold bg-warning rounded-pill px-3 py-1" href="#"
                                data-bs-toggle="dropdown"
                                style="display: inline-flex; align-items: center; text-decoration: none;">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode(Session::get('user_name') ?: 'chi Tran') ?>&background=random"
                                    class="rounded-circle me-2" width="24" height="24" alt="Avatar">
                                    <?= Session::get('user_name') ?: 'chi Tran' ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                                <li>
                                    <h6 class="dropdown-header">Chào bạn: <?= Session::get('user_name') ?: 'chi Tran' ?>
                                    </h6>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <?php if (Session::get('user_role_id') == 1): ?>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/admin/dashboard">Dashboard</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <?php else: ?>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/nguoi-dung/dashboard">Nạp tiền vào tài
                                        khoản</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/nguoi-dung/dashboard">Danh sách tin đăng</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/nguoi-dung/profile">Thông tin cá nhân</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/nguoi-dung/change_password">Đổi mật khẩu</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?= URL_ROOT ?>/nguoi-dung/daLuu">Tin BDS đã lưu</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <?php endif; ?>
                                <li>
                                    <form action="<?= URL_ROOT ?>/nguoi-dung/logout" method="POST">
                                        <?= Csrf::field() ?>
                                        <button type="submit" class="dropdown-item">Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                            <?php if (Session::get('user_role_id') == 1): ?>
                            <a href="<?= URL_ROOT ?>/admin/dashboard" class="btn btn-danger text-white rounded-1 shadow-sm px-3">
                                <i class="fa-solid fa-list-check"></i> HỆ THỐNG QUẢN TRỊ
                            </a>
                            <?php else: ?>
                            <a href="<?= URL_ROOT ?>/nguoi-dung/post" class="btn btn-danger text-white rounded-1 shadow-sm px-3">
                                <i class="fa-solid fa-pen-to-square"></i> ĐĂNG TIN
                            </a>
                            <?php endif; ?>
                        </li>
                        <?php else: ?>
                        <style>
                            .dropdown-login .dropdown-toggle::after {
                                display: none !important;
                            }
                            .guest-auth-link {
                                display: inline-flex !important;
                                align-items: center;
                                justify-content: center;
                                gap: 6px;
                                padding: 8px 22px !important;
                                border-radius: 30px !important;
                                font-weight: 700 !important;
                                font-size: 0.85rem !important;
                                text-transform: uppercase;
                                letter-spacing: 0.5px;
                                transition: all 0.25s ease-in-out !important;
                                text-decoration: none !important;
                                line-height: 1.5 !important;
                                min-height: unset !important;
                            }
                            
                            .guest-register-link {
                                border: 1px solid #cfa86e !important;
                                background: linear-gradient(180deg, #ebd1a7 0%, #cfa86e 100%) !important;
                                color: #2A1E17 !important;
                                box-shadow: 0 4px 10px rgba(207, 168, 110, 0.25) !important;
                                margin: 0 6px !important;
                            }
                            .guest-register-link:hover {
                                background: linear-gradient(180deg, #f5ddb9, #dfb476) !important;
                                border-color: #dfb476 !important;
                                color: #1a0f0a !important;
                                transform: translateY(-1px);
                                box-shadow: 0 6px 16px rgba(207, 168, 110, 0.4) !important;
                            }
                            
                            .guest-login-link {
                                border: 1.5px solid #bd9254 !important;
                                background: #ffffff !important;
                                color: #8f6735 !important;
                                box-shadow: 0 2px 6px rgba(143, 103, 53, 0.08) !important;
                                margin-left: 6px !important;
                            }
                            .guest-login-link:hover,
                            .guest-login-link[aria-expanded="true"] {
                                background: #fdfaf5 !important;
                                border-color: #8f6735 !important;
                                color: #6d4a20 !important;
                                transform: translateY(-1px);
                                box-shadow: 0 4px 10px rgba(143, 103, 53, 0.15) !important;
                            }

                            .auth-login-item {
                                margin-left: 0.5rem;
                            }
                            @media (max-width: 991.98px) {
                                .auth-login-item {
                                    margin-top: 0.5rem;
                                    margin-left: 0;
                                }
                                .guest-register-link,
                                .guest-login-link {
                                    width: 100%;
                                    margin: 6px 0 !important;
                                }
                            }
                            .header-login-dropdown {
                                border-radius: 14px !important;
                                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
                            }
                            .header-login-dropdown .input-group {
                                border: 1.5px solid #eef2fa !important;
                                border-radius: 12px !important;
                                overflow: hidden;
                                background-color: #eef2fa !important;
                                transition: all 0.2s;
                            }
                            .header-login-dropdown .input-group:focus-within {
                                border-color: #2563eb !important;
                                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
                                background-color: #eef2fa !important;
                            }
                            .header-login-dropdown .input-group-text {
                                border: none !important;
                                background-color: transparent !important;
                                padding-left: 16px !important;
                                padding-right: 8px !important;
                            }
                            .header-login-dropdown .form-control {
                                border: none !important;
                                background-color: transparent !important;
                                padding-top: 10px !important;
                                padding-bottom: 10px !important;
                                box-shadow: none !important;
                                color: #111827 !important;
                            }
                            .header-login-dropdown .btn-login-submit {
                                background-color: #2563eb !important;
                                border: none !important;
                                border-radius: 12px !important;
                                padding: 11px !important;
                                font-weight: 700 !important;
                                transition: all 0.2s;
                            }
                            .header-login-dropdown .btn-login-submit:hover {
                                background-color: #1d4ed8 !important;
                                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
                            }
                            .header-login-dropdown .auth-social-grid {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 12px;
                                margin-bottom: 16px;
                            }
                            .header-login-dropdown .auth-social-btn {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                padding: 10px;
                                border: 1.5px solid #e5e7eb;
                                border-radius: 12px;
                                background: #fff;
                                font-size: 0.9rem;
                                font-weight: 600;
                                cursor: pointer;
                                transition: all 0.2s;
                                text-decoration: none;
                                color: #374151;
                            }
                            .header-login-dropdown .auth-social-btn:hover {
                                border-color: #cbd5e1;
                                background: #f8fafc;
                                transform: translateY(-1px);
                            }
                            .header-login-dropdown .auth-social-btn svg {
                                width: 18px;
                                height: 18px;
                            }
                            .header-login-dropdown .auth-divider {
                                display: flex;
                                align-items: center;
                                gap: 12px;
                                margin: 16px 0;
                            }
                            .header-login-dropdown .auth-divider span {
                                font-size: 0.8rem;
                                color: #9ca3af;
                                white-space: nowrap;
                            }
                            .header-login-dropdown .auth-divider::before,
                            .header-login-dropdown .auth-divider::after {
                                content: '';
                                flex: 1;
                                height: 1px;
                                background: #e5e7eb;
                            }
                        </style>
                        <li class="nav-item me-lg-3 mt-2 mt-lg-0">
                            <a href="<?= URL_ROOT ?>/dang-nhap?redirect=/nguoi-dung/post"
                                class="btn btn-danger text-white rounded-1 shadow-sm px-3 trigger-login-btn">
                                <i class="fa-solid fa-pen-to-square"></i> ĐĂNG TIN
                            </a>
                        </li>
                        <li class="nav-item auth-register-item">
                            <a class="nav-link guest-auth-link guest-register-link" href="<?= URL_ROOT ?>/dang-ky">Đăng ký</a>
                        </li>
                        <li class="nav-item auth-login-item">
                            <a class="nav-link guest-auth-link guest-login-link" href="<?= URL_ROOT ?>/dang-nhap">
                                <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i><span>Đăng nhập</span>
                            </a>
                        </li>
                        <?php if (false): // Legacy dropdown auth; standalone pages are now used. ?>
                        <li class="nav-item dropdown dropdown-login">
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 p-4 mt-2 header-login-dropdown" style="width: 340px; z-index: 9999;">
                                
                                <!-- ================= PANEL LOGIN ================= -->
                                <div id="header-login-panel">
                                    <form id="header-login-form" action="<?= URL_ROOT ?>/dang-nhap" method="POST">
                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                        
                                        <h5 class="fw-bold text-dark mb-1 text-start">Đăng nhập</h5>
                                        <p class="text-secondary small mb-3 text-start">Nhập thông tin tài khoản để tiếp tục</p>
                                        
                                        <?php if (false): // Tạm ẩn đăng nhập mạng xã hội; bật lại khi hoàn thiện OAuth. ?>
                                        <div class="auth-social-grid">
                                            <a href="#" onclick="return openSocialPopup('<?= URL_ROOT ?>/nguoi-dung/social-redirect?provider=google')" class="auth-social-btn google">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                                </svg>
                                                Google
                                            </a>
                                            <a href="#" onclick="return openSocialPopup('<?= URL_ROOT ?>/nguoi-dung/social-redirect?provider=facebook')" class="auth-social-btn facebook">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2">
                                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                                </svg>
                                                Facebook
                                            </a>
                                        </div>
                                        
                                        <div class="auth-divider"><span>hoặc đăng nhập bằng tài khoản</span></div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Email hoặc Số điện thoại</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                                                <input type="text" name="identifier" class="form-control text-dark" placeholder="Email hoặc SĐT" required style="font-size: 0.9rem;">
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Mật khẩu</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                                <input type="password" name="mat_khau" class="form-control text-dark" placeholder="Mật khẩu" required style="font-size: 0.9rem;">
                                                <button type="button" class="toggle-password-btn" style="border: none; background: transparent; padding-right: 12px; padding-left: 8px; color: #9ca3af;"><i class="fa-solid fa-eye"></i></button>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 d-flex align-items-center justify-content-between" style="font-size: 0.85rem;">
                                            <div class="form-check m-0 d-flex align-items-center">
                                                <input type="checkbox" class="form-check-input me-1" id="headerRememberMe" name="remember_me" value="1">
                                                <label class="form-check-label text-secondary" for="headerRememberMe">Ghi nhớ</label>
                                            </div>
                                            <a href="#" id="link-to-forgot" class="small text-decoration-none" style="font-size: 0.8rem; color: #2563eb; font-weight: 500;">Quên?</a>
                                        </div>
                                        
                                        <!-- CAPTCHA -->
                                        <div class="mb-3 d-none text-start" id="header-captcha-container">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Mã xác thực bảo mật</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <div id="header-captcha-display" class="border rounded px-3 py-2 fw-bold text-center bg-light text-dark" style="letter-spacing: 4px; font-size: 1.1rem; min-width: 100px; user-select: none;"></div>
                                                <input type="text" name="captcha" id="header-captcha-input" class="form-control text-dark" placeholder="Nhập mã" maxlength="5" autocomplete="off" style="font-size: 0.9rem; padding: 10px;">
                                                <button type="button" class="btn btn-outline-secondary" id="header-captcha-refresh" title="Tải lại">
                                                    <i class="fa-solid fa-arrows-rotate"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Unverified notice -->
                                        <div class="alert alert-warning d-none py-2 px-3 small mb-3 text-start" id="header-unverified-container" style="border-radius: 8px;">
                                            Tài khoản chưa xác thực email. <a href="#" id="header-resend-verify-btn" class="fw-bold text-warning-emphasis" style="text-decoration: underline;">Gửi lại email xác thực</a>
                                        </div>
                                        
                                        <div class="alert alert-danger d-none py-2 px-3 small mb-3 text-start" id="header-login-error" style="border-radius: 8px;"></div>
                                        
                                        <button type="submit" class="btn btn-login-submit text-white w-100 py-2 small fw-bold">
                                            <span class="btn-text"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i>Đăng nhập</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                        
                                        <div class="mt-3 text-center" style="font-size: 0.85rem;">
                                            Chưa có tài khoản? <a href="#" id="link-to-register" style="color: #2563eb; font-weight: 600; text-decoration: none;">Đăng ký miễn phí</a>
                                        </div>
                                    </form>
                                </div>

                                <!-- ================= PANEL REGISTER ================= -->
                                <div id="header-register-panel" class="d-none">
                                    <form id="header-register-form" action="<?= URL_ROOT ?>/dang-ky" method="POST">
                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                        
                                        <h5 class="fw-bold text-dark mb-1 text-start">Đăng ký</h5>
                                        <p class="text-secondary small mb-3 text-start">Tạo tài khoản mới hoàn toàn miễn phí</p>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Họ và tên</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-user text-muted"></i></span>
                                                <input type="text" name="ten" class="form-control text-dark" placeholder="Nguyễn Văn A" required style="font-size: 0.9rem;">
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reg-error-ten" style="font-size: 0.8rem;"></div>
                                        </div>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Địa chỉ Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                                                <input type="email" name="email" class="form-control text-dark" placeholder="email@example.com" required style="font-size: 0.9rem;">
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reg-error-email" style="font-size: 0.8rem;"></div>
                                        </div>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Số điện thoại</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-phone text-muted"></i></span>
                                                <input type="tel" name="dien_thoai" class="form-control text-dark" placeholder="0901234567" required style="font-size: 0.9rem;">
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reg-error-dien_thoai" style="font-size: 0.8rem;"></div>
                                        </div>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Mật khẩu</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                                <input type="password" name="mat_khau" class="form-control text-dark" placeholder="Ít nhất 6 ký tự" required style="font-size: 0.9rem;">
                                                <button type="button" class="toggle-password-btn" style="border: none; background: transparent; padding-right: 12px; padding-left: 8px; color: #9ca3af;"><i class="fa-solid fa-eye"></i></button>
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reg-error-mat_khau" style="font-size: 0.8rem;"></div>
                                        </div>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Nhập lại mật khẩu</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                                <input type="password" name="mat_khau_xac_nhan" class="form-control text-dark" placeholder="Nhập lại mật khẩu" required style="font-size: 0.9rem;">
                                                <button type="button" class="toggle-password-btn" style="border: none; background: transparent; padding-right: 12px; padding-left: 8px; color: #9ca3af;"><i class="fa-solid fa-eye"></i></button>
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reg-error-mat_khau_xac_nhan" style="font-size: 0.8rem;"></div>
                                        </div>
                                        
                                        <div class="mb-3 form-check text-start">
                                            <input type="checkbox" name="dong_y_dieu_khoan" value="1" class="form-check-input" id="headerRegAgree" required>
                                            <label class="form-check-label text-secondary small" for="headerRegAgree" style="font-size: 0.8rem; user-select: none;">Đồng ý với các điều khoản dịch vụ</label>
                                        </div>
                                        
                                        <div class="alert alert-danger d-none py-2 px-3 small mb-3 text-start" id="header-register-error" style="border-radius: 8px;"></div>
                                        
                                        <button type="submit" class="btn btn-login-submit text-white w-100 py-2 small fw-bold">
                                            <span class="btn-text">Đăng ký tài khoản</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                        
                                        <div class="mt-3 text-center" style="font-size: 0.85rem;">
                                            Đã có tài khoản? <a href="#" id="link-to-login" style="color: #2563eb; font-weight: 600; text-decoration: none;">Đăng nhập ngay</a>
                                        </div>
                                    </form>
                                </div>

                                <!-- ================= PANEL FORGOT PASSWORD ================= -->
                                <div id="header-forgot-panel" class="d-none">
                                    <form id="header-forgot-form" action="<?= URL_ROOT ?>/nguoi-dung/forgotPassword" method="POST">
                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                        
                                        <h5 class="fw-bold text-dark mb-1 text-start">Quên mật khẩu</h5>
                                        <p class="text-secondary small mb-3 text-start">Chúng tôi sẽ gửi mã xác thực khôi phục</p>
                                        
                                        <div class="mb-4 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Email hoặc Số điện thoại</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-envelope text-muted"></i></span>
                                                <input type="text" name="identifier" class="form-control text-dark" placeholder="Nhập Email hoặc Số điện thoại" required style="font-size: 0.9rem;">
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-danger d-none py-2 px-3 small mb-3 text-start" id="header-forgot-error" style="border-radius: 8px;"></div>
                                        <div class="alert alert-success d-none py-2 px-3 small mb-3 text-start" id="header-forgot-success" style="border-radius: 8px;"></div>
                                        
                                        <button type="submit" class="btn btn-login-submit text-white w-100 py-2 small fw-bold">
                                            <span class="btn-text">Gửi mã xác thực</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                        
                                        <div class="mt-3 text-center" style="font-size: 0.85rem;">
                                            Quay lại <a href="#" id="link-forgot-to-login" style="color: #2563eb; font-weight: 600; text-decoration: none;">Đăng nhập</a>
                                        </div>
                                    </form>
                                </div>

                                <!-- ================= PANEL OTP ================= -->
                                <div id="header-otp-panel" class="d-none">
                                    <form id="header-otp-form" action="<?= URL_ROOT ?>/nguoi-dung/verify-otp" method="POST">
                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                        
                                        <h5 class="fw-bold text-dark mb-1 text-start">Xác thực OTP</h5>
                                        <p class="text-secondary small mb-3 text-start">Nhập mã xác thực 6 chữ số vừa nhận</p>
                                        
                                        <div class="mb-4 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Mã OTP</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-key text-muted"></i></span>
                                                <input type="text" name="otp" class="form-control text-dark text-center fw-bold fs-5" placeholder="------" required maxlength="6" style="letter-spacing: 6px; font-size: 1.2rem;">
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-danger d-none py-2 px-3 small mb-3 text-start" id="header-otp-error" style="border-radius: 8px;"></div>
                                        
                                        <button type="submit" class="btn btn-login-submit text-white w-100 py-2 small fw-bold">
                                            <span class="btn-text">Xác minh mã OTP</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                        
                                        <div class="mt-3 text-center" style="font-size: 0.85rem;">
                                            Không nhận được mã? <a href="#" id="header-resend-otp-btn" style="color: #2563eb; font-weight: 600; text-decoration: none;">Gửi lại mã</a>
                                        </div>
                                    </form>
                                </div>

                                <!-- ================= PANEL RESET PASSWORD ================= -->
                                <div id="header-reset-panel" class="d-none">
                                    <form id="header-reset-form" action="<?= URL_ROOT ?>/nguoi-dung/reset-password-ajax" method="POST">
                                        <input type="hidden" name="_csrf_token" value="<?= Csrf::token() ?>">
                                        
                                        <h5 class="fw-bold text-dark mb-1 text-start">Mật khẩu mới</h5>
                                        <p class="text-secondary small mb-3 text-start">Tạo mật khẩu mới cho tài khoản của bạn</p>
                                        
                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Mật khẩu mới</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                                <input type="password" name="mat_khau" class="form-control text-dark" placeholder="Mật khẩu mới" required style="font-size: 0.9rem;">
                                                <button type="button" class="toggle-password-btn" style="border: none; background: transparent; padding-right: 12px; padding-left: 8px; color: #9ca3af;"><i class="fa-solid fa-eye"></i></button>
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reset-error-mat_khau" style="font-size: 0.8rem;"></div>
                                        </div>

                                        <div class="mb-3 text-start">
                                            <label class="form-label small fw-semibold text-secondary mb-1">Nhập lại mật khẩu</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                                                <input type="password" name="mat_khau_xac_nhan" class="form-control text-dark" placeholder="Nhập lại mật khẩu" required style="font-size: 0.9rem;">
                                                <button type="button" class="toggle-password-btn" style="border: none; background: transparent; padding-right: 12px; padding-left: 8px; color: #9ca3af;"><i class="fa-solid fa-eye"></i></button>
                                            </div>
                                            <div class="text-danger small mt-1 d-none" id="header-reset-error-mat_khau_xac_nhan" style="font-size: 0.8rem;"></div>
                                        </div>
                                        
                                        <div class="alert alert-danger d-none py-2 px-3 small mb-3 text-start" id="header-reset-error" style="border-radius: 8px;"></div>
                                        
                                        <button type="submit" class="btn btn-login-submit text-white w-100 py-2 small fw-bold">
                                            <span class="btn-text">Lưu mật khẩu mới</span>
                                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </li>
                        <script>
                        function openSocialPopup(url) {
                            const width = 500;
                            const height = 600;
                            const left = (screen.width - width) / 2;
                            const top = (screen.height - height) / 2;
                            window.open(url, 'socialLoginPopup', `width=${width},height=${height},left=${left},top=${top},status=no,resizable=yes`);
                            return false;
                        }

                        document.addEventListener("DOMContentLoaded", function() {
                            const loginForm = document.getElementById('header-login-form');
                            const registerForm = document.getElementById('header-register-form');
                            const forgotForm = document.getElementById('header-forgot-form');
                            const otpForm = document.getElementById('header-otp-form');
                            
                            const triggerBtn = document.querySelector('.trigger-login-btn');
                            const registerTrigger = document.querySelector('.trigger-register-btn');

                            // Show panel helper function
                            window.showPanel = function(panelId) {
                                const panels = [
                                    'header-login-panel',
                                    'header-register-panel',
                                    'header-forgot-panel',
                                    'header-otp-panel',
                                    'header-reset-panel'
                                ];
                                panels.forEach(p => {
                                    const el = document.getElementById(p);
                                    if (el) el.classList.add('d-none');
                                });
                                const activeEl = document.getElementById(panelId);
                                if (activeEl) activeEl.classList.remove('d-none');
                            };

                            // Toggle password visibility click listener
                            const togglePassBtns = document.querySelectorAll('.toggle-password-btn');
                            togglePassBtns.forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const group = btn.closest('.input-group');
                                    const input = group ? group.querySelector('input') : null;
                                    if (input) {
                                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                                        input.setAttribute('type', type);
                                        
                                        const icon = btn.querySelector('i');
                                        if (icon) {
                                            if (type === 'password') {
                                                icon.classList.remove('fa-eye-slash');
                                                icon.classList.add('fa-eye');
                                            } else {
                                                icon.classList.remove('fa-eye');
                                                icon.classList.add('fa-eye-slash');
                                            }
                                        }
                                    }
                                });
                            });

                            // Auto open dropdown if "?login=1", "?register=1", or "?forgot=1" is in URL
                            const urlParams = new URLSearchParams(window.location.search);
                            let targetPanel = '';
                            if (urlParams.has('login')) {
                                targetPanel = 'header-login-panel';
                            } else if (urlParams.has('register')) {
                                targetPanel = 'header-register-panel';
                            } else if (urlParams.has('forgot')) {
                                targetPanel = 'header-forgot-panel';
                            }

                            if (targetPanel) {
                                showPanel(targetPanel);
                                const loginToggle = document.querySelector('.dropdown-login .dropdown-toggle');
                                if (loginToggle) {
                                    setTimeout(() => {
                                        const dropdown = bootstrap.Dropdown.getOrCreateInstance(loginToggle);
                                        dropdown.show();
                                    }, 300);
                                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                                    window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
                                }
                            }

                            // Reset to login panel when the dropdown is closed
                            const dropdownEl = document.querySelector('.dropdown-login');
                            if (dropdownEl) {
                                dropdownEl.addEventListener('hidden.bs.dropdown', function() {
                                    showPanel('header-login-panel');
                                });
                            }

                            // Trigger buttons click handlers
                            if (triggerBtn && loginForm) {
                                triggerBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    showPanel('header-login-panel');
                                    const loginToggleEl = document.querySelector('.dropdown-login .dropdown-toggle');
                                    if (loginToggleEl) {
                                        const dropdown = bootstrap.Dropdown.getOrCreateInstance(loginToggleEl);
                                        dropdown.show();
                                    }
                                });
                            }

                            if (registerTrigger) {
                                registerTrigger.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    showPanel('header-register-panel');
                                    const loginToggleEl = document.querySelector('.dropdown-login .dropdown-toggle');
                                    if (loginToggleEl) {
                                        const dropdown = bootstrap.Dropdown.getOrCreateInstance(loginToggleEl);
                                        dropdown.show();
                                    }
                                });
                            }

                            // Panel switcher links
                            const toRegister = document.getElementById('link-to-register');
                            if (toRegister) {
                                toRegister.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    showPanel('header-register-panel');
                                });
                            }

                            const toForgot = document.getElementById('link-to-forgot');
                            if (toForgot) {
                                toForgot.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    showPanel('header-forgot-panel');
                                });
                            }

                            const toLoginFromReg = document.getElementById('link-to-login');
                            if (toLoginFromReg) {
                                toLoginFromReg.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    showPanel('header-login-panel');
                                });
                            }

                            const toLoginFromForgot = document.getElementById('link-forgot-to-login');
                            if (toLoginFromForgot) {
                                toLoginFromForgot.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    showPanel('header-login-panel');
                                });
                            }

                            // CAPTCHA loading logic
                            function loadHeaderCaptcha() {
                                const formData = new FormData();
                                formData.append('_csrf_token', '<?= Csrf::token() ?>');
                                fetch('<?= URL_ROOT ?>/nguoi-dung/refreshCaptcha', {
                                    method: 'POST',
                                    body: formData,
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        const display = document.getElementById('header-captcha-display');
                                        if (display) {
                                            display.textContent = data.captcha.toUpperCase();
                                        }
                                    }
                                });
                            }

                            const refreshBtn = document.getElementById('header-captcha-refresh');
                            if (refreshBtn) {
                                refreshBtn.addEventListener('click', loadHeaderCaptcha);
                            }

                            // Resend verification email
                            const resendBtn = document.getElementById('header-resend-verify-btn');
                            if (resendBtn && loginForm) {
                                resendBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const identifier = loginForm.querySelector('input[name="identifier"]').value;
                                    if (!identifier) {
                                        alert('Vui lòng nhập Email hoặc Số điện thoại trước.');
                                        return;
                                    }
                                    const formData = new FormData();
                                    formData.append('_csrf_token', '<?= Csrf::token() ?>');
                                    formData.append('identifier', identifier);
                                    
                                    resendBtn.textContent = 'Đang gửi...';
                                    fetch('<?= URL_ROOT ?>/nguoi-dung/resend-verify', {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        alert(data.message);
                                        resendBtn.textContent = 'Gửi lại email xác thực';
                                    })
                                    .catch(err => {
                                        alert('Có lỗi xảy ra khi gửi lại email xác thực.');
                                        resendBtn.textContent = 'Gửi lại email xác thực';
                                    });
                                });
                            }

                            // Form login submit.
                            // Native submit is the reliable default. AJAX can be explicitly
                            // enabled later with data-ajax="true" when needed.
                            if (loginForm && loginForm.dataset.ajax === 'true') {
                                loginForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const errorDiv = document.getElementById('header-login-error');
                                    const submitBtn = loginForm.querySelector('button[type="submit"]');
                                    const btnText = submitBtn.querySelector('.btn-text');
                                    const spinner = submitBtn.querySelector('.spinner-border');

                                    errorDiv.classList.add('d-none');
                                    btnText.classList.add('d-none');
                                    spinner.classList.remove('d-none');
                                    submitBtn.disabled = true;

                                    const formData = new FormData(loginForm);
                                    formData.append('ajax', '1');

                                    fetch(loginForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            window.location.href = data.redirect || '<?= URL_ROOT ?>/';
                                        } else {
                                            errorDiv.textContent = data.message || 'Đăng nhập thất bại. Vui lòng kiểm tra lại.';
                                            errorDiv.classList.remove('d-none');
                                            
                                            if (data.captcha_needed) {
                                                const container = document.getElementById('header-captcha-container');
                                                if (container) {
                                                    container.classList.remove('d-none');
                                                    const input = document.getElementById('header-captcha-input');
                                                    if (input) input.setAttribute('required', 'required');
                                                    loadHeaderCaptcha();
                                                }
                                            }
                                            
                                            if (data.unverified) {
                                                const unverifiedContainer = document.getElementById('header-unverified-container');
                                                if (unverifiedContainer) {
                                                    unverifiedContainer.classList.remove('d-none');
                                                }
                                            }
                                            
                                            btnText.classList.remove('d-none');
                                            spinner.classList.add('d-none');
                                            submitBtn.disabled = false;
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Error:', err);
                                        errorDiv.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
                                        errorDiv.classList.remove('d-none');
                                        
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    });
                                });
                            }

                            // Form register submit
                            if (registerForm) {
                                registerForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const errorDiv = document.getElementById('header-register-error');
                                    const submitBtn = registerForm.querySelector('button[type="submit"]');
                                    const btnText = submitBtn.querySelector('.btn-text');
                                    const spinner = submitBtn.querySelector('.spinner-border');

                                    // clear old errors
                                    registerForm.querySelectorAll('.text-danger').forEach(el => {
                                        el.classList.add('d-none');
                                        el.textContent = '';
                                    });
                                    errorDiv.classList.add('d-none');
                                    btnText.classList.add('d-none');
                                    spinner.classList.remove('d-none');
                                    submitBtn.disabled = true;

                                    const formData = new FormData(registerForm);
                                    formData.append('ajax', '1');

                                    fetch(registerForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert(data.message);
                                            showPanel('header-login-panel');
                                        } else {
                                            if (data.errors) {
                                                for (const key in data.errors) {
                                                    if (key === 'general') {
                                                        errorDiv.textContent = data.errors[key];
                                                        errorDiv.classList.remove('d-none');
                                                    } else {
                                                        const fieldError = document.getElementById('header-reg-error-' + key);
                                                        if (fieldError) {
                                                            fieldError.textContent = data.errors[key];
                                                            fieldError.classList.remove('d-none');
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    })
                                    .catch(err => {
                                        errorDiv.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
                                        errorDiv.classList.remove('d-none');
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    });
                                });
                            }

                            // Form forgot password submit
                            if (forgotForm) {
                                forgotForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const errorDiv = document.getElementById('header-forgot-error');
                                    const successDiv = document.getElementById('header-forgot-success');
                                    const submitBtn = forgotForm.querySelector('button[type="submit"]');
                                    const btnText = submitBtn.querySelector('.btn-text');
                                    const spinner = submitBtn.querySelector('.spinner-border');

                                    errorDiv.classList.add('d-none');
                                    successDiv.classList.add('d-none');
                                    btnText.classList.add('d-none');
                                    spinner.classList.remove('d-none');
                                    submitBtn.disabled = true;

                                    const formData = new FormData(forgotForm);
                                    formData.append('ajax', '1');

                                    fetch(forgotForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            successDiv.textContent = data.message;
                                            successDiv.classList.remove('d-none');
                                            
                                            if (data.method === 'otp') {
                                                setTimeout(() => {
                                                    showPanel('header-otp-panel');
                                                    successDiv.classList.add('d-none');
                                                }, 1500);
                                            }
                                        } else {
                                            if (data.errors && data.errors.general) {
                                                errorDiv.textContent = data.errors.general;
                                            } else if (data.errors && data.errors.identifier) {
                                                errorDiv.textContent = data.errors.identifier;
                                            } else {
                                                errorDiv.textContent = 'Không tìm thấy tài khoản tương ứng.';
                                            }
                                            errorDiv.classList.remove('d-none');
                                        }
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    })
                                    .catch(err => {
                                        errorDiv.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
                                        errorDiv.classList.remove('d-none');
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    });
                                });
                            }

                            // Form verify OTP submit
                            if (otpForm) {
                                otpForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const errorDiv = document.getElementById('header-otp-error');
                                    const submitBtn = otpForm.querySelector('button[type="submit"]');
                                    const btnText = submitBtn.querySelector('.btn-text');
                                    const spinner = submitBtn.querySelector('.spinner-border');

                                    errorDiv.classList.add('d-none');
                                    btnText.classList.add('d-none');
                                    spinner.classList.remove('d-none');
                                    submitBtn.disabled = true;

                                    const formData = new FormData(otpForm);
                                    formData.append('ajax', '1');

                                    fetch(otpForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            if (data.otp_verified) {
                                                showPanel('header-reset-panel');
                                            } else if (data.reset_url) {
                                                window.location.href = data.reset_url;
                                            } else {
                                                alert('Xác thực OTP thành công!');
                                                showPanel('header-login-panel');
                                            }
                                        } else {
                                            errorDiv.textContent = data.message || 'Mã OTP không hợp lệ.';
                                            errorDiv.classList.remove('d-none');
                                        }
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    })
                                    .catch(err => {
                                        errorDiv.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
                                        errorDiv.classList.remove('d-none');
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    });
                                });
                            }

                            // Form Reset Password submit
                            const resetForm = document.getElementById('header-reset-form');
                            if (resetForm) {
                                resetForm.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    const errorDiv = document.getElementById('header-reset-error');
                                    const submitBtn = resetForm.querySelector('button[type="submit"]');
                                    const btnText = submitBtn.querySelector('.btn-text');
                                    const spinner = submitBtn.querySelector('.spinner-border');

                                    // Clear field errors
                                    const errMatKhau = document.getElementById('header-reset-error-mat_khau');
                                    const errXacNhan = document.getElementById('header-reset-error-mat_khau_xac_nhan');
                                    if (errMatKhau) errMatKhau.classList.add('d-none');
                                    if (errXacNhan) errXacNhan.classList.add('d-none');
                                    if (errorDiv) errorDiv.classList.add('d-none');

                                    btnText.classList.add('d-none');
                                    spinner.classList.remove('d-none');
                                    submitBtn.disabled = true;

                                    const formData = new FormData(resetForm);
                                    formData.append('ajax', '1');

                                    fetch(resetForm.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert(data.message);
                                            showPanel('header-login-panel');
                                        } else {
                                            if (data.errors) {
                                                for (const key in data.errors) {
                                                    const fieldError = document.getElementById('header-reset-error-' + key);
                                                    if (fieldError) {
                                                        fieldError.textContent = data.errors[key];
                                                        fieldError.classList.remove('d-none');
                                                    }
                                                }
                                            } else if (data.message) {
                                                errorDiv.textContent = data.message;
                                                errorDiv.classList.remove('d-none');
                                            }
                                        }
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    })
                                    .catch(err => {
                                        errorDiv.textContent = 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
                                        errorDiv.classList.remove('d-none');
                                        btnText.classList.remove('d-none');
                                        spinner.classList.add('d-none');
                                        submitBtn.disabled = false;
                                    });
                                });
                            }

                            // Resend OTP button handler
                            const resendOtpBtn = document.getElementById('header-resend-otp-btn');
                            if (resendOtpBtn) {
                                resendOtpBtn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    const formData = new FormData();
                                    formData.append('_csrf_token', '<?= Csrf::token() ?>');
                                    
                                    resendOtpBtn.textContent = 'Đang gửi...';
                                    fetch('<?= URL_ROOT ?>/nguoi-dung/resend-otp', {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        alert(data.message || 'Đã gửi lại mã OTP thành công!');
                                        resendOtpBtn.textContent = 'Gửi lại mã';
                                    })
                                    .catch(err => {
                                        alert('Có lỗi xảy ra khi gửi lại mã OTP.');
                                        resendOtpBtn.textContent = 'Gửi lại mã';
                                    });
                                });
                            }
                        });
                        </script>
                        <?php endif; ?>
                        <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Success Flash Alert -->
    <?php if (Session::get('contact_success')): ?>
        <div class="container mt-3" style="position: relative; z-index: 9999;">
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 p-3" role="alert" style="border-radius: 12px; background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46;">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
                    <div>
                        <h6 class="mb-1 fw-bold">Đã gửi yêu cầu thành công!</h6>
                        <p class="mb-0 small opacity-90"><?= Session::get('contact_success') ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <?php Session::delete('contact_success'); ?>
    <?php endif; ?>

    <!-- Floating Action Buttons Panel (Social Contacts Fixed) -->
    <div class="floating-contact-panel">
        <!-- Đặt lịch tư vấn -->
        <button type="button" class="floating-btn floating-btn-booking" data-bs-toggle="modal" data-bs-target="#bookingConsultationModal" data-tooltip="Đặt lịch tư vấn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="30" height="30">
                <rect x="12" y="16" width="40" height="36" rx="5" fill="#59171b"/>
                <rect x="12" y="16" width="40" height="9" rx="2" fill="#59171b"/>
                <rect x="15" y="25" width="34" height="24" rx="2" fill="#f2cb6c"/>
                <rect x="18" y="10" width="4" height="8" rx="2" fill="#59171b"/>
                <rect x="30" y="10" width="4" height="8" rx="2" fill="#59171b"/>
                <rect x="42" y="10" width="4" height="8" rx="2" fill="#59171b"/>
                <circle cx="21" cy="31" r="2" fill="#59171b"/>
                <circle cx="29" cy="31" r="2" fill="#59171b"/>
                <circle cx="37" cy="31" r="2" fill="#59171b"/>
                <circle cx="45" cy="31" r="2" fill="#59171b"/>
                <circle cx="21" cy="39" r="2" fill="#59171b"/>
                <circle cx="29" cy="39" r="2" fill="#59171b"/>
                <circle cx="21" cy="46" r="2" fill="#59171b"/>
                <circle cx="44" cy="44" r="11" fill="#59171b"/>
                <circle cx="44" cy="44" r="8" fill="#f2cb6c"/>
                <path d="M44 39.5 v4.5 h4" stroke="#59171b" stroke-width="2" stroke-linecap="round" fill="none"/>
            </svg>
        </button>
    </div>

    <!-- Modal Đặt Lịch Hẹn Tư Vấn (Global) -->
    <div class="modal fade" id="bookingConsultationModal" tabindex="-1" aria-hidden="true" style="z-index: 10500;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Premium Header -->
                <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #1e293b, #0f172a); border: none; position: relative;">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning p-2 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; box-shadow: 0 4px 8px rgba(251, 191, 36, 0.3);">
                            <i class="fa-solid fa-calendar-check fs-4 text-dark"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Đặt Lịch Hẹn Tư Vấn</h5>
                            <p class="mb-0 text-white-50 small">Chọn thời gian và điền thông tin để chúng tôi phục vụ chu đáo nhất.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="globalBookingForm" action="<?= URL_ROOT ?>/contact" method="POST">
                    <?= Csrf::field() ?>
                    <!-- Keep page tracking -->
                    <input type="hidden" name="redirect_url" id="booking-redirect-url">
                    
                    <div class="modal-body p-4 bg-light text-start">
                        <div class="row g-3">
                            <!-- Họ tên -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Họ và tên <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-regular fa-user"></i></span>
                                    <input type="text" name="name" class="form-control border-start-0 ps-0 animate-fade-in" placeholder="Nhập họ và tên..." required value="<?= Session::get('user_name') ?: '' ?>" style="height: 42px;">
                                </div>
                            </div>
                            
                            <!-- Số điện thoại -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Số điện thoại <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-phone-flip"></i></span>
                                    <input type="tel" name="phone" class="form-control border-start-0 ps-0 animate-fade-in" placeholder="Nhập số điện thoại..." required value="<?= Session::get('user_phone') ?: '' ?>" style="height: 42px;">
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Địa chỉ Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0 ps-0 animate-fade-in" placeholder="Nhập email (tùy chọn)..." value="<?= Session::get('user_email') ?: '' ?>" style="height: 42px;">
                                </div>
                            </div>
                            
                            <!-- Lịch hẹn tư vấn -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Thời gian hẹn tư vấn <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-regular fa-clock"></i></span>
                                    <input type="datetime-local" name="preferred_time" id="booking-preferred-time" class="form-control border-start-0 ps-0" required style="height: 42px;">
                                </div>
                                <div class="form-text small text-muted">Vui lòng chọn ngày và giờ mong muốn cuộc gọi tư vấn.</div>
                            </div>
                            
                            <!-- Lời nhắn yêu cầu cụ thể -->
                            <div class="col-12">
                                <label class="form-label small fw-bold text-secondary mb-1">Yêu cầu tư vấn cụ thể</label>
                                <textarea name="message_raw" id="booking-message-raw" rows="3" class="form-control" placeholder="Mô tả cụ thể nhu cầu của bạn (Ví dụ: Cần tìm căn hộ 2 phòng ngủ dự án X, tư vấn vay vốn, gọi lại sau giờ hành chính...)" style="resize: none;"></textarea>
                            </div>
                            
                            <!-- Hidden message field to combine schedule information -->
                            <input type="hidden" name="message" id="booking-combined-message">
                        </div>
                    </div>
                    
                    <div class="modal-footer bg-white border-top-0 justify-content-center py-3">
                        <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Hủy bỏ</button>
                        <button type="submit" class="btn btn-warning px-4 rounded-pill fw-bold text-dark"><i class="fa-solid fa-calendar-check me-1"></i> Xác Nhận Đặt Lịch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Notification handler
            <?php if (Session::get('user_id')): ?>
            var notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                notificationDropdown.addEventListener('show.bs.dropdown', function () {
                    var badge = notificationDropdown.querySelector('.badge');
                    if (badge) {
                        badge.style.display = 'none';
                        fetch('<?= URL_ROOT ?>/nguoi-dung/markNotificationsRead', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: '_csrf_token=<?= urlencode(Csrf::token()) ?>'
                        }).then(response => response.json())
                          .then(data => {
                              if (data.status === 'success') {
                                  var listItems = document.querySelectorAll('#notificationDropdown + .dropdown-menu .bg-light');
                                  listItems.forEach(function(item) {
                                      item.classList.remove('bg-light');
                                      var text = item.querySelector('.text-primary');
                                      if (text) text.classList.remove('text-primary');
                                  });
                              }
                          }).catch(err => console.error(err));
                    }
                });
            }
            <?php endif; ?>

            // Booking Form Handler
            const bookingForm = document.getElementById('globalBookingForm');
            if (bookingForm) {
                // Set default redirect url to current location path so the user returns to the exact same page!
                document.getElementById('booking-redirect-url').value = window.location.href;
                
                // Auto set minimum datetime to today
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                const timeInput = document.getElementById('booking-preferred-time');
                if (timeInput) {
                    timeInput.min = now.toISOString().slice(0, 16);
                }
                
                bookingForm.addEventListener('submit', function(e) {
                    const preferredTime = document.getElementById('booking-preferred-time').value;
                    const messageRaw = document.getElementById('booking-message-raw').value;
                    
                    // Format dynamic combined message
                    let formattedTime = '';
                    if (preferredTime) {
                        const dateObj = new Date(preferredTime);
                        formattedTime = dateObj.toLocaleDateString('vi-VN') + ' lúc ' + dateObj.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
                    }
                    
                    const combinedMessage = `[ĐẶT LỊCH HẸN TƯ VẤN]
- Thời gian mong muốn gọi lại: ${formattedTime}
- Yêu cầu cụ thể: ${messageRaw || 'Không có yêu cầu thêm.'}`;
                    
                    document.getElementById('booking-combined-message').value = combinedMessage;
                });
            }
        });

        // =============================================
        // Page Loader: ẩn khi trang đã tải xong
        // =============================================
        const pageLoader = document.getElementById('page-loader');
        if (pageLoader) {
            // Hàm ẩn loader: thêm class fade-out để trigger CSS transition
            function hideLoader() {
                pageLoader.classList.add('fade-out');
                // Xoá hoàn toàn khỏi DOM sau khi animation kết thúc (0.5s)
                setTimeout(function() {
                    pageLoader.style.display = 'none';
                }, 500);
            }

            // Ẩn ngay khi DOMContentLoaded nếu trang đã sẵn sàng
            if (document.readyState === 'complete') {
                hideLoader();
            } else {
                // Ẩn khi tất cả tài nguyên (ảnh, CSS...) đã tải xong
                window.addEventListener('load', hideLoader);
                // Failsafe: ẩn bắt buộc sau 3 giây dù trang chưa load xong
                setTimeout(hideLoader, 3000);
            }
        }
    </script>
